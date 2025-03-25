-- Felhasználó tábla létrehozás
CREATE TABLE Felhasznalo (
    fID INT PRIMARY KEY,
    fNev VARCHAR2(32) NOT NULL,
    email VARCHAR2(128) UNIQUE NOT NULL,
    jelszo VARCHAR2(128) NOT NULL,
    profilkep VARCHAR2(128) NOT NULL,
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