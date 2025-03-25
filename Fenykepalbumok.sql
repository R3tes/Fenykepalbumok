-- Felhasználó tábla létrehozás
CREATE TABLE Felhasznalo (
    fID INT PRIMARY KEY,
    fNev VARCHAR2(32) NOT NULL,
    email VARCHAR2(128) UNIQUE NOT NULL,
    jelszo VARCHAR2(128) NOT NULL,
    profilkep VARCHAR2(128) NOT NULL,
    jogosultsag VARCHAR2(32) NOT NULL
);

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

-- Album tábla létrehozása
CREATE TABLE Album (
    aID INT PRIMARY KEY,
    albumNev VARCHAR2(128) NOT NULL,
    fID INT NOT NULL,
    CONSTRAINT fk_album_felhasznalo FOREIGN KEY (fID)
        REFERENCES Felhasznalo(fID) ON DELETE CASCADE
);

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


-- Hely tábla létrehozása
CREATE TABLE Hely (
    helyID INT PRIMARY KEY,
    varos VARCHAR2(64) NOT NULL,
    megye VARCHAR2(64) NOT NULL,
    orszag VARCHAR2(64) NOT NULL
);

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

-- Képek tábla feltöltés
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (1, 'Naplemente', 10, 1, 1);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (2, 'Reök', 5, 2, 2);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (3, 'Modell', 8, 3, NULL);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (4, 'Hold', 15, 4, 4);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (5, 'Szeged', 20, 5, 5);

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

-- Pályázat tábla létrehozása
CREATE TABLE Palyazat (
    pID INT PRIMARY KEY,
    palyazatNev VARCHAR2(128) NOT NULL
);

--Pályázat tábla feltöltése
INSERT INTO Palyazat (pID, palyazatNev) VALUES (1, 'Természetfotó Pályázat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (2, 'Építészetifotó Pályázat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (3, 'Portréfotó Pályázat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (4, 'Éjszakaifotó Pályázat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (5, 'Streetfotó Pályázat');

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

-- Nevezett tábla feltöltés
INSERT INTO Nevezett (kepID, pID, pont) VALUES (1, 1, 85);
INSERT INTO Nevezett (kepID, pID, pont) VALUES (2, 2, 70);
INSERT INTO Nevezett (kepID, pID, pont) VALUES (3, 3, 90);
INSERT INTO Nevezett (kepID, pID, pont) VALUES (4, 4, 60);
INSERT INTO Nevezett (kepID, pID, pont) VALUES (5, 5, 75);

CREATE TABLE Kategoria (
    katID INT PRIMARY KEY,
    kategoriaNev VARCHAR(128) UNIQUE NOT NULL
);
INSERT INTO Kategoria (katID,kategoriaNev) VALUES(
1,"termeszet",
2,"tajkep",
3,"portre",
4,"epiteszet",
5,"cgi",
6,"etel",
7,"utazas",
8,"varosi_elet",
9,"sport",
10,"allatok",
11,"kutya",
12,"macska",
13,"makrofotozas",
14,"divat",
15,"fesztivalok",
16,"tudomany",
17,"technológia",
18,"festmeny",
19,"ejszakai",
20,"csillagaszat"
);

CREATE TABLE KategoriaResze (
    katID INT,
    kepID INT,
    FOREIGN KEY (katID) REFERENCES Kategoria(katID) ON DELETE CASCADE,
    FOREIGN KEY (kepID) REFERENCES Kep(kepID) ON DELETE CASCADE
);
INSERT INTO KategoriaResze (katID,kepID)VALUES (
1,1,
1,2,
2,3,
3,4,
4,5,
5,6
);

CREATE TABLE Tartalmaz (
    aID INT,
    kepID INT,
    FOREIGN KEY (aID) REFERENCES Album(aID) ON DELETE CASCADE,
    FOREIGN KEY (kepID) REFERENCES Kep(kepID) ON DELETE CASCADE
);
INSERT INTO Tartalmaz (aID,kepID)VALUES (
1,1,
1,2,
2,3,
3,4,
4,5,
5,6
);
