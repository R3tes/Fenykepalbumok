DROP TABLE Kategoria;
DROP TABLE KategoriaResze;
DROP TABLE Tartalmaz;

CREATE TABLE Kategoria (
    katID INT AUTO_INCREMENT PRIMARY KEY,
    kategoriaNev VARCHAR(255) UNIQUE NOT NULL
);

INSERT INTO Kategoria (kategoriaNev) VALUES(
"termeszet",
"tajkep",
"portre",
"epiteszet",
"cgi",
"etel",
"utazas",
"varosi_elet",
"sport",
"allatok",
"kutya",
"macska",
"makrofotozas",
"divat",
"fesztivalok",
"tudomany",
"technológia",
"festmeny",
"ejszakai",
"csillagaszat"
);


CREATE TABLE KategoriaResze (
    katID INT,
    kepID INT
    FOREIGN KEY (katID) REFERENCES Kategoria(katID) ON DELETE CASCADE,
    FOREIGN KEY (kepID) REFERENCES Kep(kepID) ON DELETE CASCADE
);

INSERT INTO KategoriaResze (katID,kepID)VALUES (
101,1,
101,2,
102,3,
103,4,
104,5,
105,6
);

CREATE TABLE Tartalmaz (
    aID INT,
    kepID INT,
    FOREIGN KEY (aID) REFERENCES Album(aID) ON DELETE CASCADE,
    FOREIGN KEY (kepID) REFERENCES Kep(kepID) ON DELETE CASCADE
);
INSERT INTO Tartalmaz (aID,kepID)VALUES (
101,1,
101,2,
102,3,
103,4,
104,5,
105,6
);