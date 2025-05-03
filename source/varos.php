<?php
session_start();
require_once 'resources/SUPPORT_FUNCS/db_connection.php';

if (!isset($_GET['id'])) {
    echo "Nincs megadva város.";
    exit();
}

$helyID = $_GET['id'];

$query = "SELECT h.varos, COUNT(k.kepID) AS count, SUM(k.ertekeles) AS sum
FROM Hely h
LEFT JOIN Kep k ON k.helyID = h.helyID
WHERE h.helyID = :helyID
GROUP BY h.varos";

$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ":helyID", $helyID);

$varosNev = "";
$count = 0;
$sum = 0;
if(oci_execute($stmt)){
    if($row = oci_fetch_assoc($stmt)){
        $varosNev = $row["VAROS"];
        $count = $row["COUNT"];
        $sum = $row["SUM"];
    }
} else {
    $e = oci_error($stmt);
    die("Database Error: " . $e['message']);
}


$query = "SELECT k.kepID, k.kepNev, f.fNev, k.ertekeles
FROM Kep k
JOIN Felhasznalo f ON k.fID = f.fID
JOIN Hely h ON k.helyID = h.helyID
WHERE h.helyID = :helyID
ORDER BY k.ertekeles DESC";

$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ":helyID", $helyID);
oci_execute($stmt);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($varosNev); ?> képei</title>
    <link rel="stylesheet" href="resources/CSS/index.css">
</head>
<body style="">

<?php include 'navbar.php'; ?>

<div class="container-city-category">
    <h1><?php echo htmlspecialchars($varosNev); ?> képei</h1>
    <p><?php echo '(képek: '.$count.' db, összesített pontok: '.$sum.' )';?></p>
    <div class="grid-container">
        <?php
        while ($row = oci_fetch_assoc($stmt)) {
            $kepNev = htmlspecialchars($row['KEPNEV']);
            $felhasznaloNev = htmlspecialchars($row['FNEV']);
            $likeok = $row['ERTEKELES'];

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
