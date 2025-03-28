-- Felhasználó tábla létrehozás
CREATE TABLE Felhasznalo (
    fID INT PRIMARY KEY,
    fNev VARCHAR2(32) NOT NULL,
    email VARCHAR2(128) UNIQUE NOT NULL,
    jelszo VARCHAR2(128) NOT NULL,
    profilkep VARCHAR2(128),
    jogosultsag VARCHAR2(32) NOT NULL
);

-- Album tábla létrehozása
CREATE TABLE Album (
    aID INT PRIMARY KEY,
    albumNev VARCHAR2(128) NOT NULL,
    fID INT NOT NULL,
    CONSTRAINT fk_album_felhasznalo FOREIGN KEY (fID)
        REFERENCES Felhasznalo(fID) ON DELETE CASCADE
);

-- Hely tábla létrehozása
CREATE TABLE Hely (
    helyID INT PRIMARY KEY,
    varos VARCHAR2(64) NOT NULL,
    megye VARCHAR2(64) NOT NULL,
    orszag VARCHAR2(64) NOT NULL
);

-- Kép tábla létrehozása
CREATE TABLE Kep (
    kepID INT PRIMARY KEY,
    kepNev VARCHAR2(128) NOT NULL,
    ertekeles INT DEFAULT 0,
    fID INT NOT NULL,
    helyID INT,
    CONSTRAINT fk_felhasznalo FOREIGN KEY (fID) 
        REFERENCES Felhasznalo(fID) ON DELETE CASCADE,
    CONSTRAINT fk_hely FOREIGN KEY (helyID) 
        REFERENCES Hely(helyID) ON DELETE SET NULL
);

-- Hozzászólás tábla létrehozása
CREATE TABLE Hozzaszolas (
    hozzaszolasID INT PRIMARY KEY,
    tartalom VARCHAR2(512) NOT NULL,
    fID INT NOT NULL,
    kepID INT NOT NULL,
    CONSTRAINT fk_hozzaszolo_felhasznalo FOREIGN KEY (fID)
        REFERENCES Felhasznalo(fID) ON DELETE CASCADE,
    CONSTRAINT fk_hozzaszolo_kep FOREIGN KEY (kepID)
        REFERENCES Kep(kepID) ON DELETE CASCADE
);

-- Pályázat tábla létrehozása
CREATE TABLE Palyazat (
    pID INT PRIMARY KEY,
    palyazatNev VARCHAR2(128) NOT NULL
);

-- Nevezett tábla létrehozása (kapcsolótábla a pályázat és a kép között)
CREATE TABLE Nevezett (
    kepID INT,
    pID INT,
    pont INT DEFAULT 0,
    PRIMARY KEY (kepID, pID),
    CONSTRAINT fk_kep FOREIGN KEY (kepID) 
        REFERENCES Kep(kepID) ON DELETE CASCADE,
    CONSTRAINT fk_palyazat FOREIGN KEY (pID) 
        REFERENCES Palyazat(pID) ON DELETE CASCADE
);

-- Kategória tábla létrehozása
CREATE TABLE Kategoria (
    katID INT PRIMARY KEY,
    kategoriaNev VARCHAR(128) UNIQUE NOT NULL
);

-- KategóriaRésze tábla létrehozása (kapcsolótábla a kategória és a kép között)
CREATE TABLE KategoriaResze (
    katID INT NOT NULL,
    kepID INT NOT NULL,
    CONSTRAINT fk_katid FOREIGN KEY (katID) REFERENCES Kategoria(katID) ON DELETE CASCADE,
    CONSTRAINT fk_kepid FOREIGN KEY (kepID) REFERENCES Kep(kepID) ON DELETE CASCADE
);

-- Tartalmaz tábla létrehozása (kapcsolótábla a album és a kép között)
CREATE TABLE Tartalmaz (
    aID INT NOT NULL,
    kepID INT NOT NULL,
    CONSTRAINT fk_aid2 FOREIGN KEY (aID) REFERENCES Album(aID) ON DELETE CASCADE,
    CONSTRAINT fk_kepid2 FOREIGN KEY (kepID) REFERENCES Kep(kepID) ON DELETE CASCADE
);

-- Felhasználó tábla feltöltése
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (1, 'Kiss Anna', 'anna.kiss@gmail.com', 'Anna2024!', 'felhasznalo');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (2, 'Nagy Péter', 'pnagy84@yahoo.com', 'nP_1984pass', 'admin');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (3, 'Tóth Eszter', 'eszter.t@hotmail.com', 'Eszti*321', 'felhasznalo');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (4, 'Farkas Levente', 'levente.f@freemail.hu', 'Levi!pass2023', 'felhasznalo');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (5, 'Kovács Dóra', 'dora.kovacs92@outlook.com', 'Dorka_92!', 'admin');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (6, 'Szabó Bence', 'bence.sz@gmail.com', 'SzB!pass456', 'felhasznalo');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (7, 'Molnár Réka', 'reka.molnar@inf.u-szeged.hu', 'Reka1234@', 'felhasznalo');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (8, 'Varga Gábor', 'vargag@gmail.com', 'GaborPass!', 'felhasznalo');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (9, 'Balogh Enikő', 'eniko.balogh@citromail.hu', 'Eni*567', 'felhasznalo');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (10, 'Papp Tamás', 'tamaspapp99@gmail.com', 'tamasP99$', 'admin');

-- Hely tábla feltöltése
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (1, 'Szeged', 'Csongrád-Csanád', 'Magyarország');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (2, 'Budapest', 'Pest', 'Magyarország');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (3, 'Debrecen', 'Hajdú-Bihar', 'Magyarország');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (4, 'Győr', 'Győr-Moson-Sopron', 'Magyarország');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (5, 'Pécs', 'Baranya', 'Magyarország');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (6, 'Miskolc', 'Borsod-Abaúj-Zemplén', 'Magyarország');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (7, 'Nyíregyháza', 'Szabolcs-Szatmár-Bereg', 'Magyarország');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (8, 'Kecskemét', 'Bács-Kiskun', 'Magyarország');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (9, 'Szombathely', 'Vas', 'Magyarország');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (10, 'Eger', 'Heves', 'Magyarország');

-- Képek tábla feltöltése
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (1, 'Naplemente', 10, 1, 1);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (2, 'Reök', 5, 2, 2);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (3, 'Modell', 8, 3, NULL);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (4, 'Hold', 15, 4, 4);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (5, 'Szeged', 20, 5, 5);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (6, 'Kiskutya', 10, 6, 6);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (7, 'Kiscica', 5, 7, 7);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (8, 'Alföld', 8, 8, 8);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (9, 'Szitakötő', 15, 9, 9);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (10, 'Budapest', 20, 10, 10);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (11, 'Naplemente2', 10, 10, 1);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (12, 'Reök2', 5, 9, 2);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (13, 'Modell2', 8, 8, NULL);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (14, 'Hold2', 15, 7, 4);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (15, 'Szeged2', 20, 6, 5);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (16, 'Kiskutya2', 10, 5, 6);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (17, 'Kiscica2', 5, 4, 7);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (18, 'Alföld2', 8, 3, 8);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (19, 'Szitakötő2', 15, 2, 9);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (20, 'Budapest2', 20, 1, 10);

-- Album tábla feltöltése
INSERT INTO Album (aID, albumNev, fID) VALUES (1, 'Természet Csodái', 2);
INSERT INTO Album (aID, albumNev, fID) VALUES (2, 'Építészeti Részletek', 2);
INSERT INTO Album (aID, albumNev, fID) VALUES (3, 'Portrék', 3);
INSERT INTO Album (aID, albumNev, fID) VALUES (4, 'Naplementék', 4);
INSERT INTO Album (aID, albumNev, fID) VALUES (5, 'Utazásaim', 5);
INSERT INTO Album (aID, albumNev, fID) VALUES (6, 'Városi Élet', 7);
INSERT INTO Album (aID, albumNev, fID) VALUES (7, 'Éjszakai Fotók', 6);
INSERT INTO Album (aID, albumNev, fID) VALUES (8, 'Tengerpartok', 1);
INSERT INTO Album (aID, albumNev, fID) VALUES (9, 'Makró Világ', 9);
INSERT INTO Album (aID, albumNev, fID) VALUES (10, 'Fekete-fehér Hangulatok', 10);

-- Hozzászólás tábla feltöltése
INSERT INTO Hozzaszolas (hozzaszolasID, tartalom, fID, kepID) VALUES (1, 'Nagyon tetszik ez a kép!', 1, 1);
INSERT INTO Hozzaszolas (hozzaszolasID, tartalom, fID, kepID) VALUES (2, 'Gyönyörű színek!', 2, 2);
INSERT INTO Hozzaszolas (hozzaszolasID, tartalom, fID, kepID) VALUES (3, 'Ez a kedvencem!', 3, 5);
INSERT INTO Hozzaszolas (hozzaszolasID, tartalom, fID, kepID) VALUES (4, 'Szuper kompozíció!', 4, 3);
INSERT INTO Hozzaszolas (hozzaszolasID, tartalom, fID, kepID) VALUES (5, 'Gratulálok!', 5, 5);
INSERT INTO Hozzaszolas (hozzaszolasID, tartalom, fID, kepID) VALUES (6, 'Szép munka!', 6, 1);
INSERT INTO Hozzaszolas (hozzaszolasID, tartalom, fID, kepID) VALUES (7, 'Tetszik a fénykezelés.', 7, 2);
INSERT INTO Hozzaszolas (hozzaszolasID, tartalom, fID, kepID) VALUES (8, 'Imádom ezt a hangulatot.', 8, 3);
INSERT INTO Hozzaszolas (hozzaszolasID, tartalom, fID, kepID) VALUES (9, 'Nagyon kreatív!', 9, 4);
INSERT INTO Hozzaszolas (hozzaszolasID, tartalom, fID, kepID) VALUES (10, 'Lenyűgöző látvány.', 10, 4);

--Pályázat tábla feltöltése
INSERT INTO Palyazat (pID, palyazatNev) VALUES (1, 'Természetfotó Pályázat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (2, 'Építészetifotó Pályázat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (3, 'Portréfotó Pályázat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (4, 'Éjszakaifotó Pályázat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (5, 'Streetfotó Pályázat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (6, 'Kisállatfotó Pályázat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (7, 'Utazásfotó Pályázat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (8, 'Holdfotó Pályázat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (9, 'Vadvilágfotó Pályázat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (10, 'Stúdiófotó Pályázat');

-- Nevezett tábla feltöltés
INSERT INTO Nevezett (kepID, pID, pont) VALUES (1, 1, 85);
INSERT INTO Nevezett (kepID, pID, pont) VALUES (2, 2, 70);
INSERT INTO Nevezett (kepID, pID, pont) VALUES (3, 3, 90);
INSERT INTO Nevezett (kepID, pID, pont) VALUES (4, 4, 60);
INSERT INTO Nevezett (kepID, pID, pont) VALUES (5, 5, 75);
INSERT INTO Nevezett (kepID, pID, pont) VALUES (6, 6, 85);
INSERT INTO Nevezett (kepID, pID, pont) VALUES (7, 7, 70);
INSERT INTO Nevezett (kepID, pID, pont) VALUES (8, 8, 90);
INSERT INTO Nevezett (kepID, pID, pont) VALUES (9, 9, 60);
INSERT INTO Nevezett (kepID, pID, pont) VALUES (10, 10, 75);

-- Kategória tábla feltöltése
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (1,'termeszet');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (2,'tajkep');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (3,'portre');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (4,'epiteszet');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (5,'cgi');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (6,'etel');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (7,'utazas');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (8,'varosi_elet');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (9,'sport');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (10,'allatok');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (11,'kutya');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (12,'macska');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (13,'makrofotozas');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (14,'divat');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (15,'fesztivalok');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (16,'tudomany');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (17,'technológia');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (18,'festmeny');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (19,'ejszakai');
INSERT INTO Kategoria (katID,kategoriaNev) VALUES (20,'csillagaszat');

-- KategóriaRésze tábla feltöltése
INSERT INTO KategoriaResze (katID,kepID) VALUES (1,1);
INSERT INTO KategoriaResze (katID,kepID) VALUES (1,2);
INSERT INTO KategoriaResze (katID,kepID) VALUES (2,3);
INSERT INTO KategoriaResze (katID,kepID) VALUES (3,4);
INSERT INTO KategoriaResze (katID,kepID) VALUES (4,5);
INSERT INTO KategoriaResze (katID,kepID) VALUES (5,6);
INSERT INTO KategoriaResze (katID,kepID) VALUES (7,3);
INSERT INTO KategoriaResze (katID,kepID) VALUES (19,8);
INSERT INTO KategoriaResze (katID,kepID) VALUES (1,10);
INSERT INTO KategoriaResze (katID,kepID) VALUES (12,6);
INSERT INTO KategoriaResze (katID,kepID) VALUES (4,2);
INSERT INTO KategoriaResze (katID,kepID) VALUES (15,7);
INSERT INTO KategoriaResze (katID,kepID) VALUES (20,1);
INSERT INTO KategoriaResze (katID,kepID) VALUES (3,9);
INSERT INTO KategoriaResze (katID,kepID) VALUES (11,5);
INSERT INTO KategoriaResze (katID,kepID) VALUES (8,4);

-- Tartalmaz tábla feltöltése
INSERT INTO Tartalmaz (aID,kepID) VALUES (1,1);
INSERT INTO Tartalmaz (aID,kepID) VALUES (1,2);
INSERT INTO Tartalmaz (aID,kepID) VALUES (2,3);
INSERT INTO Tartalmaz (aID,kepID) VALUES (3,4);
INSERT INTO Tartalmaz (aID,kepID) VALUES (4,5);
INSERT INTO Tartalmaz (aID,kepID) VALUES (5,6);
INSERT INTO Tartalmaz (aID,kepID) VALUES (10,2);
INSERT INTO Tartalmaz (aID,kepID) VALUES (5,9);
INSERT INTO Tartalmaz (aID,kepID) VALUES (1,7);
INSERT INTO Tartalmaz (aID,kepID) VALUES (6,4);
INSERT INTO Tartalmaz (aID,kepID) VALUES (2,10);
INSERT INTO Tartalmaz (aID,kepID) VALUES (8,3);
INSERT INTO Tartalmaz (aID,kepID) VALUES (4,8);
INSERT INTO Tartalmaz (aID,kepID) VALUES (9,6);
INSERT INTO Tartalmaz (aID,kepID) VALUES (2,5);
