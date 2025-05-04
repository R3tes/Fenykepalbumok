<?php
session_start();
include('resources/SUPPORT_FUNCS/db_connection.php');



?>
<!DOCTYPE html>
<html lang="hu">
<head>

    <meta charset="UTF-8">
    <title>FotóPont | Statisztika</title>
    <link rel="stylesheet" href="resources/CSS/style.css">
    <link rel="stylesheet" href="resources/CSS/statistics.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<main>
    <div class="tables">
        <table class="statCategory">
            <thead>
                <tr>
                    <th colspan="3">
                        Felhasználói:
                    </th>
                </tr>
                <?php
                    $stmt = oci_parse($conn, "SELECT COUNT(f.fID) AS count FROM Felhasznalo f ");
                    oci_execute($stmt);
                    if (oci_execute($stmt)) {
                        $row = oci_fetch_assoc($stmt);
                        echo '  <tr>
                                    <td > Felhasználók:</td><td>'.$row["COUNT"].'</td>
                                </tr>';
                        
                    } else {
                        $e = oci_error($stmt);
                        die("Database Error: " . $e['message']);
                    }
                ?>
                <tr>
                    <th class="subHeading">Név</th>
                    <th class="subHeading">Képek száma</th>
                    <th class="subHeading">Értékelés</th>
                </tr>
            </thead>
            <tbody >
                <?php
                    $stmt = oci_parse($conn, "SELECT f.fNev, f.fID, COUNT(k.kepID) AS count, SUM(k.ertekeles) AS points 
                                            FROM Felhasznalo f INNER JOIN Kep k ON k.fID = f.fID 
                                            GROUP BY f.fNev, f.fID
                                            ORDER BY COUNT(k.kepID) DESC");
                    if (oci_execute($stmt)) {
                        while($row = oci_fetch_assoc($stmt)) {
                            echo '<tr>
                                    <td><a href="profile.php?id='.$row["FID"].'">'.$row["FNEV"].'</a></td><td>'.$row["COUNT"].'</td><td>'.$row["POINTS"].'</td>
                                </tr>';
                        }
                    } else {
                        $e = oci_error($stmt);
                        die("Database Error: " . $e['message']);
                    }
                ?>
            </tbody>
        </table>
        
        <table class="statCategory">
            <thead>
                <tr>
                    <th>
                        Képek:
                    </th>
                </tr>
                <?php
                    $stmt = oci_parse($conn, "SELECT count(k.kepID) as count FROM Kep k");
                    oci_execute($stmt);
                    if (oci_execute($stmt)) {
                        $row = oci_fetch_assoc($stmt);
                        echo '<tr>
                                <td>'.$row["COUNT"].' db kép</td>
                            </tr>';
                    } else {
                        $e = oci_error($stmt);
                        die("Database Error: " . $e['message']);
                    }

                ?>
                <tr>
                    <th class="subHeading">Név</th>
                    <th class="subHeading">Értékelés</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $stmt = oci_parse($conn, "SELECT k.kepID, k.kepNev, k.ertekeles FROM Kep k ORDER BY k.ertekeles DESC");
                oci_execute($stmt);
                if (oci_execute($stmt)) {
                    while($row = oci_fetch_assoc($stmt)) {
                    echo '<tr>
                            <td><a href="picture.php?id='.$row["KEPID"].'">'.$row["KEPNEV"].'</a></td><td>'.$row["ERTEKELES"].'</td>
                        </tr>';
                    }
                } else {
                    $e = oci_error($stmt);
                    die("Database Error: " . $e['message']);
                }

            ?>
            </tbody>
            
        </table>
        <table class="statCategory">
            <thead>
                <tr>
                    <th>
                        Hely:
                    </th>
                </tr>
                <?php
                    $stmt = oci_parse($conn, "SELECT COUNT(h.helyID) AS result
                                                FROM Kep k
                                                INNER JOIN Hely h ON k.helyID = h.helyID
                                                GROUP BY h.helyID
                                                HAVING COUNT(k.kepID) > 0");
                    oci_execute($stmt);
                    if (oci_execute($stmt)) {
                        $row = oci_fetch_assoc($stmt);
                        echo '<tr>
                                <td>'.$row["RESULT"].' város</td>
                            </tr>';
                    } else {
                        $e = oci_error($stmt);
                        die("Database Error: " . $e['message']);
                    }

                ?>
                <tr>
                    <th class="subHeading">Város</th>
                    <th class="subHeading">Képek</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $stmt = oci_parse($conn, "SELECT h.helyID, h.orszag, h.megye, h.varos, COUNT(k.kepID) AS count
                                                FROM Kep k
                                                INNER JOIN Hely h ON k.helyID = h.helyID
                                                GROUP BY h.helyID, h.orszag, h.megye, h.varos
                                                HAVING COUNT(k.kepID) > 0
                                                ORDER BY COUNT(k.kepID) DESC");
                    oci_execute($stmt);
                    if (oci_execute($stmt)) {
                        while($row = oci_fetch_assoc($stmt)) {
                            echo '<tr>
                                    <td><a href="varos.php?id='.$row["HELYID"].'">'.$row["ORSZAG"].', '.$row["MEGYE"].', '.$row["VAROS"].'</a></td><td>'.$row["COUNT"].'</td>
                                </tr>';
                            }
                    } else {
                        $e = oci_error($stmt);
                        die("Database Error: " . $e['message']);
                    }

                ?>
            </tbody>
        </table>
        <table class="statCategory">
            <thead>
            <tr>
                <th colspan="2">
                    Legaktívabb felhasználók:
                </th>
            </tr>
            <tr>
                <th class="subHeading">Név</th>
                <th class="subHeading">Összes idő (perc)</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $stmt = oci_parse($conn, "

                    SELECT f.fNev, 
                            ROUND(SUM(
           (CAST(s.kilepes_ideje AS DATE) - CAST(s.belepes_ideje AS DATE)) * 1440
       ), 0) AS percek
                    FROM SessionNaplo s
                    JOIN Felhasznalo f ON s.felhasznalo_id = f.fID
                    GROUP BY f.fNev
                    ORDER BY percek DESC
                    FETCH FIRST 10 ROWS ONLY
                    
                ");
            if (oci_execute($stmt)) {
                while($row = oci_fetch_assoc($stmt)) {
                    echo '<tr>
                                <td>' . htmlspecialchars($row["FNEV"]) . '</td>
                                <td>' . htmlspecialchars($row["PERCEK"]) . ' perc</td>
                              </tr>';
                }
            } else {
                $e = oci_error($stmt);
                echo '<tr><td colspan="2">Hiba a lekérdezésben: ' . htmlspecialchars($e['message']) . '</td></tr>';
            }
            ?>
            </tbody>
        </table>
        <table class="statCategory">
            <thead>
            <tr>
                <th colspan="2">
                    Legtöbb pályázatot nyert felhasználók:
                </th>
            </tr>
            <tr>
                <th class="subHeading">Név</th>
                <th class="subHeading">Nyert Pályázatok</th>
            </tr>
            </thead>
            <tbody>
            <?php
            // Lekérdezzük a legtöbb pályázatot nyert felhasználókat
            $stmt = oci_parse($conn, "
    SELECT f.fNev AS FelhasznaloNev, COUNT(n.pID) AS NyertPalyazatok
    FROM Felhasznalo f
    JOIN Kep k ON f.fID = k.fID
    JOIN Nevezett n ON k.kepID = n.kepID
    JOIN Nyertesek ny ON n.pID = ny.pID AND n.kepID = ny.kepID
    GROUP BY f.fNev
    ORDER BY NyertPalyazatok DESC
");
            if (oci_execute($stmt)) {
                while ($row = oci_fetch_assoc($stmt)) {
                    echo '<tr>
            <td>' . htmlspecialchars($row["FELHASZNALONEV"]) . '</td> <!-- Helyes alias -->
            <td>' . htmlspecialchars($row["NYERTPALYAZATOK"]) . '</td> <!-- Helyes alias -->
        </tr>';
                }
            } else {
                $e = oci_error($stmt);
                echo '<tr><td colspan="2">Hiba a lekérdezésben: ' . htmlspecialchars($e['message']) . '</td></tr>';
            }

            ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
<?php
oci_free_statement($stmt);
oci_close($conn);
?>