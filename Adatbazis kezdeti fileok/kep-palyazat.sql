-- Pályázat tábla létrehozása
CREATE TABLE Palyazat (
    pID INT PRIMARY KEY,
    palyazatNev VARCHAR2(255) NOT NULL
);

-- Kép tábla létrehozása
CREATE TABLE Kep (
    kepID INT PRIMARY KEY,
    kepNev VARCHAR2(255) NOT NULL,
    ertekeles INT DEFAULT 0,
    fID INT NOT NULL,
    helyID INT,
    CONSTRAINT fk_felhasznalo FOREIGN KEY (fID) 
        REFERENCES Felhasznalo(fID) ON DELETE CASCADE,
    CONSTRAINT fk_hely FOREIGN KEY (helyID) 
        REFERENCES Hely(helyID) ON DELETE SET NULL
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

-- Pályázatok tábla feltöltés
INSERT INTO Palyazat (pID, palyazatNev) VALUES (1, 'Természetfotó Pályázat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (2, 'Építészetifotó Pályázat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (3, 'Portréfotó Pályázat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (4, 'Éjszakaifotó Pályázat');
INSERT INTO Palyazat (pID, palyazatNev) VALUES (5, 'Streetfotó Pályázat');

-- Képek tábla feltöltés
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (101, 'Naplemente', 10, 1, 1);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (102, 'Reök', 5, 2, 2);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (103, 'Modell', 8, 3, NULL);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (104, 'Hold', 15, 4, 4);
INSERT INTO Kep (kepID, kepNev, ertekeles, fID, helyID) VALUES (105, 'Szeged', 20, 5, 5);

-- Nevezett tábla feltöltés
INSERT INTO Nevezett (kepID, pID, pont) VALUES (101, 1, 85);
INSERT INTO Nevezett (kepID, pID, pont) VALUES (102, 2, 70);
INSERT INTO Nevezett (kepID, pID, pont) VALUES (103, 3, 90);
INSERT INTO Nevezett (kepID, pID, pont) VALUES (104, 4, 60);
INSERT INTO Nevezett (kepID, pID, pont) VALUES (105, 5, 75);

COMMIT;