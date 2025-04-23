# Fenykepalbumok
Ez a repository Adatbázis alapú rendszerek gyakorlatai projektjének elkészítésére jött létre.

## Részletes feladatleírás
Célunk egy olyan platform létrehozása, ahol a fényképészek támogathatják egymás munkásságát és népszerűsíthetik saját alkotásaikat. A felhasználóknak lehetőségük van képek feltöltésére, albumok létrehozására, valamint kategóriákba sorolására, így kialakítva saját portfóliójukat. Inspirációkat gyűjthetnek mások fényképeinek böngészésével. Emellett lehet a fényképeket értékelni és megjegyzést fűzni hozzájuk, így a fényképészek visszajelzést és támogatást kaphatnak munkájukról. Ezzel növelhetik népszerűségüket, így feljebb kerülve a ranglistán. Fontos elem továbbá a fotópályázatok kiírása és azokon való részvétel biztosítása. Az oldal letisztult dizájnja és egyszerű kezelhetősége arra törekszik, hogy maximalizálja a felhasználói élményt, miközben a fókusz mindig a megosztott fényképeken marad.

Adatbázis tábla törlési sorrend:

DROP TABLE KategoriaResze; <br>
DROP TABLE Kategoria; <br>
DROP TABLE Nevezett; <br>
DROP TABLE Palyazat; <br>
DROP TABLE Hozzaszolas; <br>
DROP TABLE Tartalmaz; <br>
DROP TABLE Kep; <br>
DROP TABLE Hely; <br>
DROP TABLE Album; <br>
DROP TABLE Felhasznalo; <br>

DROP SEQUENCE hely_seq;<br>
DROP SEQUENCE kat_seq;<br>
DROP SEQUENCE kep_seq;<br>
DROP SEQUENCE felhasznalo_seq;<br>
DROP SEQUENCE palyazat_seq;<br>

## Fontos!!
- legelső lépéssként le kell futtatni a setup filet hogy a hashelt jelszavak rendbe legyenek az előre insertelt felhasználóknál
- mindenkinek 12345 a jelszava akik előre hozzá vannak adva

