<?php
session_start();
require_once 'resources/SUPPORT_FUNCS/db_connection.php';

if (!isset($_GET['id'])) {
    echo "Nincs megadva kategória.";
    exit();
}

$katID = intval($_GET['id']);

$katQuery = "SELECT kat.kategoriaNev, SUM(k.ertekeles) AS points, COUNT(k.kepID) AS numberOfPics 
            FROM Kategoria kat INNER JOIN KategoriaResze kr ON kat.katID = kr.katID
            INNER JOIN Kep k ON k.kepID = kr.kepID  
            WHERE kat.katID = :katID GROUP BY kat.kategoriaNev";
$katStmt = oci_parse($conn, $katQuery);
oci_bind_by_name($katStmt, ":katID", $katID);
oci_execute($katStmt);

if (!($katRow = oci_fetch_assoc($katStmt))) {
    echo "Nem található ilyen kategória.";
    exit();
}
$kategoriaNev = htmlspecialchars($katRow['KATEGORIANEV']);
$points = $katRow["POINTS"];
$numberOfPics = $katRow["NUMBEROFPICS"];
$query = "
    SELECT k.kepID, k.kepNev, f.fNev,
           NVL((SELECT COUNT(*) FROM Likeok l WHERE l.kepID = k.kepID), 0) AS likeok
    FROM Kep k
    JOIN KategoriaResze kr ON k.kepID = kr.kepID
    JOIN Felhasznalo f ON k.fID = f.fID
    WHERE kr.katID = :katID
    ORDER BY likeok DESC, k.kepNev ASC
";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ":katID", $katID);
oci_execute($stmt);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title><?php echo $kategoriaNev; ?> képek</title>
    <link rel="stylesheet" href="resources/CSS/index.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container-city-category">
    <h1 ><?php echo $kategoriaNev; ?> képek</h1>
    <p >
        <?php echo '(képek: '.$numberOfPics.' db, összesített pontok: '.$points.' )';?>
    </p>
    <div class="grid-container">
        <?php
        while ($row = oci_fetch_assoc($stmt)) {
            $kepNev = htmlspecialchars($row['KEPNEV']);
            $felhasznaloNev = htmlspecialchars($row['FNEV']);
            $likeok = $row['LIKEOK'];

            $dir = 'resources/APP_IMGS';
            $kepPath = 'resources/APP_IMGS/placeholder.png';
            $files = scandir($dir);
            foreach ($files as $file) {
                if (fnmatch($kepNev . '.*', $file)) {
                    $kepPath = $dir . '/' . $file;
                    break;
                }
            }

            echo '<a href="picture.php?id=' . $row['KEPID'] . '" class="grid-item" style="text-decoration: none; color: inherit;">';
            echo '<img src="' . $kepPath . '" alt="' . $kepNev . '" style="width: 100%; height: 150px; object-fit: cover; border-radius: 10px;">';
            echo '<div><strong>' . $felhasznaloNev . ':</strong><br>' . $kepNev . '<br>👍 ' . $likeok . '</div>';
            echo '</a>';
        }
        ?>
    </div>
</div>

</body>
</html>
