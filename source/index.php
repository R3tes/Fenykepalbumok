<?php
session_start();
require_once 'resources/SUPPORT_FUNCS/db_connection.php';
?>

<!DOCTYPE html>
<html lang="hu">
<head>

    <meta charset="UTF-8">
    <title>FotóPont</title>
    <link rel="stylesheet" href="resources/CSS/style.css">
    <link rel="stylesheet" href="resources/CSS/index.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<?php if (isset($_SESSION['success_message'])): ?>
    <script>
        alert("<?= addslashes($_SESSION['success_message']) ?>");
    </script>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<div class="main-layout">
    <aside class="sidebar">
        <h2>Ranglista</h2>
        <ol>
            <?php 
                $stmt = oci_parse($conn, "SELECT f.fID, f.fNev, SUM(k.ertekeles) AS points 
                                                FROM Felhasznalo f 
                                                INNER JOIN Kep k ON k.fID = f.fID 
                                                GROUP BY f.fNev, f.fID 
                                                ORDER BY points DESC");
                oci_execute($stmt);
                while ($row = oci_fetch_assoc($stmt)) {
                    echo '<li><a href="profile.php?id='.$row["FID"].'">'.$row["FNEV"].': '.$row["POINTS"].' 👍</a></li>';
                }
            ?>
        </ol>
        <a href="statistics.php"><Button>Statisztikák</Button></a>
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
                        $kategoriaNev = htmlspecialchars($row['KATEGORIANEV'], ENT_QUOTES, 'UTF-8');
                        $kepszam = $row['KEPSZAM'];
                        $kategoriaID = $row['KATID'];

                        $kepPath = 'resources/APP_IMGS/placeholder.png';
                        if (!empty($row['LEGJOBBKEP'])) {
                            $dir = 'resources/APP_IMGS';
                            $files = scandir($dir);
                            foreach ($files as $file) {
                                if (fnmatch($row['LEGJOBBKEP'] . ".*", $file)) {
                                    $kepPath = $dir . '/' . $file;
                                    break;
                                }
                            }
                        }

                        echo '<a href="kategoria.php?id=' . $kategoriaID . '" class="grid-item" style="text-decoration: none; color: inherit;">';
                        echo '<div>';
                        echo '<img src="' . $kepPath . '" alt="Kategória" style="width: 100%; height: 150px; object-fit: cover; border-radius: 10px;">';
                        echo "<div style='font-weight: bold;'>{$kategoriaNev} ({$kepszam})</div>";
                        echo '</div>';
                        echo '</a>';
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
                        SELECT h.helyID, h.varos, h.megye, h.orszag,
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
                        $varosNev = htmlspecialchars($row['VAROS'], ENT_QUOTES, 'UTF-8') ?? 'Ismeretlen';
                        $megyeNev = htmlspecialchars($row['MEGYE'], ENT_QUOTES, 'UTF-8') ?? 'Ismeretlen';
                        $orszagNev = htmlspecialchars($row['ORSZAG'], ENT_QUOTES, 'UTF-8') ?? 'Ismeretlen';
                        $helyID = $row['HELYID'];
                        $kepszam = $row['KEPSZAM'];

                        $kepPath = 'resources/APP_IMGS/placeholder.png';
                        if (!empty($row['LEGJOBBKEP'])) {
                            $dir = 'resources/APP_IMGS/';
                            $files = scandir($dir);
                            foreach ($files as $file) {
                                if (fnmatch($row['LEGJOBBKEP'] . '.*', $file)) {
                                    $kepPath = $dir . '/' . $file;
                                    break;
                                }
                            }
                        }

                        echo '<a href="varos.php?id=' . urlencode($helyID) . '" class="grid-item" style="text-decoration: none; color: inherit;">';
                        echo '<div>';
                        echo '<img src="' . $kepPath . '" alt="Város" style="width: 100%; height: 150px; object-fit: cover; border-radius: 10px;">';
                        echo '<div style="font-weight: bold;">';
                        echo '<div>' . htmlspecialchars($varosNev) . ' (' . $kepszam . ')</div>';
                        echo '<div>Ország: ' . htmlspecialchars($orszagNev ?: 'Ismeretlen') .
                            ', Megye: ' . htmlspecialchars($megyeNev ?: 'Ismeretlen') . '</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</a>';
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

<script src="resources/JS/index.js" defer></script>

</body>
</html>
