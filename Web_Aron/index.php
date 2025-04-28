<?php
session_start();
require_once '../web_lara/db_connection.php';
?>

<!DOCTYPE html>
<html lang="hu">
<head>

    <meta charset="UTF-8">
    <title>FotóPont</title>
    <link rel="stylesheet" href="../web_lara/style.css">
    <link rel="stylesheet" href="resources/index.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="main-layout">
    <aside class="sidebar">
        <h2>Ranglista</h2>
        <ol>
            <li>Felhasználó 1</li>
            <li>Felhasználó 2</li>
            <li>Felhasználó 3</li>
        </ol>
    </aside>

    <main class="content">
        <div class="container">
            <h2>Kategóriák</h2>
            <div class="pagination-container" id="kategoriak-container">
                <div class="grid-container" id="kategoriak-grid">
                    <?php
                    $query = "
                        SELECT k.katID, k.kategoriaNev,
                               (SELECT COUNT(*) 
                                FROM KategoriaResze kr
                                WHERE kr.katID = k.katID) AS kepszam,
                               (SELECT kep.kepNev 
                                FROM KategoriaResze kr
                                JOIN Kep kep ON kr.kepID = kep.kepID
                                WHERE kr.katID = k.katID
                                ORDER BY kep.ertekeles DESC
                                FETCH FIRST 1 ROWS ONLY) AS legjobbKep
                        FROM Kategoria k
                        ORDER BY k.kategoriaNev
                    ";
                    $stmt = oci_parse($conn, $query);
                    oci_execute($stmt);

                    while ($row = oci_fetch_assoc($stmt)) {
                        $kategoriaNev = htmlspecialchars($row['KATEGORIANEV']);
                        $kepszam = $row['KEPSZAM'];

                        $kepPath = 'resources/placeholder.png';
                        if (!empty($row['LEGJOBBKEP'])) {
                            $dir = '../Web/PICS';
                            $files = scandir($dir);
                            foreach ($files as $file) {
                                if (fnmatch($row['LEGJOBBKEP'] . ".*", $file)) {
                                    $kepPath = $dir . '/' . $file;
                                    break;
                                }
                            }
                        }

                        echo '<div class="grid-item">';
                        echo '<div><img src="' . $kepPath . '" alt="Kategória" style="width: 100%; height: 150px; object-fit: cover; border-radius: 10px;">';
                        echo "<div style='font-weight: bold;'>{$kategoriaNev} ({$kepszam})</div>";
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>

                <div class="pagination-buttons" style="margin-bottom: 1%">
                    <button onclick="lapoz('kategoriak', -1)">
                        <img src="resources/left-arrow.png" alt="Előző"
                             style="max-width: 4vh; margin: auto auto; max-height: 4vh;">
                    </button>
                    <span style="margin-left: 10%; margin-right: 10%;" id="kategoriak-counter">1 / 1</span>
                    <button onclick="lapoz('kategoriak', 1)">
                        <img src="resources/right-arrow.png" alt="Következő"
                             style="max-width: 4vh; margin: auto auto; max-height: 4vh;">
                    </button>
                </div>
            </div>

            <h2>Városok</h2>
            <div class="pagination-container" id="varosok-container">
                <div class="grid-container" id="varosok-grid">
                    <?php
                    $query = "
                        SELECT h.helyID, h.varos,
                               (SELECT COUNT(*) 
                                FROM Kep k
                                WHERE k.helyID = h.helyID) AS kepszam,
                               (SELECT k.kepNev
                                FROM Kep k
                                WHERE k.helyID = h.helyID
                                ORDER BY k.ertekeles DESC
                                FETCH FIRST 1 ROWS ONLY) AS legjobbKep
                        FROM Hely h
                        ORDER BY h.varos
                    ";
                    $stmt = oci_parse($conn, $query);
                    oci_execute($stmt);

                    while ($row = oci_fetch_assoc($stmt)) {
                        $varosNev = htmlspecialchars($row['VAROS']);
                        $kepszam = $row['KEPSZAM'];

                        // Kép keresés
                        $kepPath = 'resources/placeholder.png';
                        if (!empty($row['LEGJOBBKEP'])) {
                            $dir = '../Web/PICS';
                            $files = scandir($dir);
                            foreach ($files as $file) {
                                if (fnmatch($row['LEGJOBBKEP'] . '.*', $file)) {
                                    $kepPath = $dir . '/' . $file;
                                    break;
                                }
                            }
                        }

                        echo '<div class="grid-item">';
                        echo '<div>';
                        echo '<img src="' . $kepPath . '" alt="Város" style="width: 100%; height: 150px; object-fit: cover; border-radius: 10px;">';
                        echo "<div style='font-weight: bold'>{$varosNev} ({$kepszam})</div>";
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>

                <div class="pagination-buttons">
                    <button onclick="lapoz('varosok', -1)">
                        <img src="resources/left-arrow.png" alt="Előző"
                             style="max-width: 4vh; margin: auto auto; max-height: 4vh;">
                    </button>
                    <span style="margin-left: 10%; margin-right: 10%;" id="varosok-counter">1 / 1</span>
                    <button onclick="lapoz('varosok', 1)">
                        <img src="resources/right-arrow.png" alt="Következő"
                             style="max-width: 4vh; margin: auto auto; max-height: 4vh;">
                    </button>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="resources/index.js" defer></script>

</body>
</html>
