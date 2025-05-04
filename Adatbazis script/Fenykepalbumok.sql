-- Felhasználó tábla létrehozás
CREATE TABLE Felhasznalo (
    fID INT PRIMARY KEY,
    fNev VARCHAR2(32) NOT NULL,
    email VARCHAR2(128) UNIQUE NOT NULL,
    jelszo VARCHAR2(128) NOT NULL,
    profilkep VARCHAR2(128),
    jogosultsag VARCHAR2(32) NOT NULL,
    created_at DATE
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

-- Likeok tábla létrehozása
CREATE TABLE Likeok (
    fID INT,
    kepID INT,
    PRIMARY KEY (fID, kepID),
    FOREIGN KEY (fID) REFERENCES Felhasznalo(fID) ON DELETE CASCADE,
    FOREIGN KEY (kepID) REFERENCES Kep(kepID) ON DELETE CASCADE
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
    PRIMARY KEY (kepID, katID),
    CONSTRAINT fk_katid FOREIGN KEY (katID) REFERENCES Kategoria(katID) ON DELETE CASCADE,
    CONSTRAINT fk_kepid FOREIGN KEY (kepID) REFERENCES Kep(kepID) ON DELETE CASCADE
);

-- Tartalmaz tábla létrehozása (kapcsolótábla a album és a kép között)
CREATE TABLE Tartalmaz (
    aID INT NOT NULL,
    kepID INT NOT NULL,
    PRIMARY KEY (kepID, aID),
    CONSTRAINT fk_aid2 FOREIGN KEY (aID) REFERENCES Album(aID) ON DELETE CASCADE,
    CONSTRAINT fk_kepid2 FOREIGN KEY (kepID) REFERENCES Kep(kepID) ON DELETE CASCADE
);

CREATE TABLE Szavazatok (
    fID INT,
    kepID INT,
    pID INT,
    PRIMARY KEY (fID, kepID, pID),
    FOREIGN KEY (fID) REFERENCES Felhasznalo(fID),
    FOREIGN KEY (kepID) REFERENCES Kep(kepID),
    FOREIGN KEY (pID) REFERENCES Palyazat(pID)
);

CREATE TABLE Nyertesek (
     pID INT PRIMARY KEY,
     kepID INT,
     FOREIGN KEY (pID) REFERENCES Palyazat(pID),
     FOREIGN KEY (kepID) REFERENCES Kep(kepID)
);

CREATE TABLE SessionNaplo (
        id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
        felhasznalo_id NUMBER NOT NULL,
        belepes_ideje DATE DEFAULT SYSDATE,
        kilepes_ideje DATE,
        FOREIGN KEY (felhasznalo_id) REFERENCES Felhasznalo(fID)
);

-- Sequencek amik száomntartják a következő indexet
CREATE SEQUENCE hely_seq
    START WITH 32
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;

CREATE SEQUENCE kat_seq
    START WITH 42
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;

CREATE SEQUENCE kep_seq
    START WITH 24
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;

CREATE SEQUENCE felhasznalo_seq
    START WITH 13
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;

CREATE SEQUENCE palyazat_seq
    START WITH 31
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;

CREATE SEQUENCE hozzaszolas_seq
    START WITH 11
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;

CREATE SEQUENCE album_seq
    START WITH 11
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;

CREATE OR REPLACE TRIGGER trg_insert_felhasznalo
BEFORE INSERT ON Felhasznalo
FOR EACH ROW
BEGIN
    :NEW.created_at := SYSDATE;
END;
/

-- Felhasználó tábla feltöltése
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (1, 'Kiss Anna', 'anna.kiss@gmail.com', '12345', 'felhasznalo');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (2, 'Nagy Péter', 'pnagy84@yahoo.com', '12345', 'admin');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (3, 'Tóth Eszter', 'eszter.t@hotmail.com', '12345', 'felhasznalo');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (4, 'Farkas Levente', 'levente.f@freemail.hu', '12345', 'felhasznalo');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (5, 'Kovács Dóra', 'dora.kovacs92@outlook.com', '12345', 'admin');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (6, 'Szabó Bence', 'bence.sz@gmail.com', '12345', 'felhasznalo');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (7, 'Molnár Réka', 'reka.molnar@inf.u-szeged.hu', '12345', 'felhasznalo');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (8, 'Varga Gábor', 'vargag@gmail.com', '12345', 'felhasznalo');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (9, 'Balogh Enikő', 'eniko.balogh@citromail.hu', '12345', 'felhasznalo');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (10, 'Papp Tamás', 'tamaspapp99@gmail.com', '12345', 'admin');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (11, 'Test Admin', 'testadmin@gmail.com', '12345', 'admin');
INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag) VALUES (12, 'Test Felhasznalo', 'testfelhasznalo@gmail.com', '12345', 'felhasznalo');

-- Hely tábla feltöltése
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (1, 'Szeged', 'Csongrad-Csanad', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (2, 'Budapest', 'Pest', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (3, 'Debrecen', 'Hajdu-Bihar', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (4, 'Gyor', 'Gyor-Moson-Sopron', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (5, 'Pecs', 'Baranya', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (6, 'Miskolc', 'Borsod-Abauj-Zemplen', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (7, 'Nyiregyhaza', 'Szabolcs-Szatmar-Bereg', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (8, 'Kecskemet', 'Bacs-Kiskun', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (9, 'Szombathely', 'Vas', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (10, 'Eger', 'Heves', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (11, 'Sopron', 'Gyor-Moson-Sopron', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (12, 'Kaposvar', 'Somogy', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (13, 'Veszprem', 'Veszprem', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (14, 'Zalaegerszeg', 'Zala', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (15, 'Tatabanya', 'Komarom-Esztergom', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (16, 'Szentendre', 'Pest', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (17, 'Ajka', 'Veszprem', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (18, 'Cegled', 'Pest', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (19, 'Vaci', 'Pest', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (20, 'Szekszard', 'Tolna', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (21, 'Baja', 'Bacs-Kiskun', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (22, 'Szarvas', 'Békés', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (23, 'Kiskunfelegyhaza', 'Bacs-Kiskun', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (24, 'Dunaújváros', 'Fejér', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (25, 'Mórahalom', 'Csongrad-Csanad', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (26, 'Nagykáta', 'Pest', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (27, 'Tata', 'Komarom-Esztergom', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (28, 'Szentgotthard', 'Vas', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (29, 'Paks', 'Tolna', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (30, 'Szombathely', 'Vas', 'Magyarorszag');
INSERT INTO Hely (helyID, varos, megye, orszag) VALUES (31, 'ValamiVaros', 'ValamiMegye', 'Magyarorszag');

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

INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (21, 'Naplemente3', 10, 1, 1);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (22, 'Naplemente4', 15, 1, 1);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (23, 'Naplemente5', 120, 1, 1);

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
INSERT INTO Palyazat (pID, palyazatNev) VALUES (1, 'Termeszetfoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (2, 'Epiteszetifoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (3, 'Portrefoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (4, 'Ejszakaifoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (5, 'Streetfoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (6, 'Kisallatfoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (7, 'Utazasfoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (8, 'Holdfoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (9, 'Vadvilagfoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (10, 'Studiovideo Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (11, 'Tajkepfoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (12, 'Cgi Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (13, 'Avarfoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (14, 'Nyarfoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (15, 'Hegyfoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (16, 'Kutya Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (17, 'Macska Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (18, 'Divatfoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (19, 'Festivalfoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (20, 'Tudomany Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (21, 'Mikroszkopfoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (22, 'Rendorfoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (23, 'Ujtechnologia Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (24, 'Zenevideo Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (25, 'Belfotó Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (26, 'Vezetesfoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (27, 'Termeszetfoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (28, 'Szulofoto Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (29, 'Haromdimenzios Palyazat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (30, 'Szabadido Palyazat');

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
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (1, 'termeszet');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (2, 'tajkep');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (3, 'portre');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (4, 'epiteszet');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (5, 'cgi');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (6, 'etel');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (7, 'utazas');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (8, 'varosi_elet');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (9, 'sport');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (10, 'allatok');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (11, 'kutya');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (12, 'macska');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (13, 'makrofotozas');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (14, 'divat');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (15, 'fesztivalok');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (16, 'tudomany');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (17, 'technologia');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (18, 'festmeny');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (19, 'ejszakai');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (20, 'csillagaszat');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (21, 'reklam');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (22, 'horror');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (23, 'kaland');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (24, 'museum');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (25, 'uj_technologia');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (26, 'tenger');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (27, 'hegyek');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (28, 'gokart');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (29, 'nyaralas');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (30, 'hideg_teli_taj');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (31, 'videoklip');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (32, 'szeged');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (33, 'mesek');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (34, 'rendorseg');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (35, 'iskola');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (36, 'szamuraj');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (37, 'gyermekek');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (38, 'tomegkozlekedes');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (39, 'multikulturalizmus');
INSERT INTO Kategoria (katID, kategoriaNev) VALUES (40, 'varosfejlesztes');

INSERT INTO Kategoria (katID, kategoriaNev) VALUES (41, 'valami');

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