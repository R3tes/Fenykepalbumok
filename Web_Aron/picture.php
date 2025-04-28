<?php
session_start();
require_once '../web_lara/db_connection.php';

$fID = $_SESSION['fID'];
$dir = $_SERVER['DOCUMENT_ROOT'] . '/fotosprojekt/Web/PICS';

if (!isset($_GET['id'])) {
    echo "Nincs megadva kép azonosító.";
    exit();
}

$kepID = intval($_GET['id']);

$query = "
        SELECT k.kepID, k.kepNev, k.ertekeles, f.fNev AS felhasznaloNev, h.varos
        FROM Kep k
        JOIN Felhasznalo f ON k.fID = f.fID
        LEFT JOIN Hely h ON k.helyID = h.helyID
        WHERE k.kepID = :kepID
";

$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ":kepID", $kepID);
oci_execute($stmt);

if ($row = oci_fetch_assoc($stmt)) {
    $kepNev = htmlspecialchars($row['KEPNEV']);
    $kepPath = "";

    $files = scandir($dir);
    foreach ($files as $file) {
        if (fnmatch($kepNev . ".*", $file)) {
            $kepPath = '/fotosprojekt/Web/PICS/' . $file;
            break;
        }
    }

    $kepNev = htmlspecialchars($row['KEPNEV']);
    $feltolto = htmlspecialchars($row['FELHASZNALONEV']);
    $varos = htmlspecialchars($row['VAROS']);
    $ertekeles = $row['ERTEKELES'];
} else {
    die('Nem található ilyen kép.');
}

$isLiked = false;
if (isset($_SESSION['fID'])) {
    $query = "SELECT COUNT(*) AS count FROM Likeok WHERE fID = :user_id AND kepID = :kep_id";
    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":user_id", $_SESSION['fID']);
    oci_bind_by_name($stid, ":kep_id", $kepID);
    oci_execute($stid);
    $row = oci_fetch_assoc($stid);
    if ($row && $row['COUNT'] > 0) {
        $isLiked = true;
    }
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title><?php echo $kepNev; ?></title>
    <link rel="stylesheet" href="../web_lara/style.css">
    <link rel="stylesheet" href="resources/picture.css">
</head>
<body>

<?php include '../Web_Aron/navbar.php'; ?>

<div class="picture-page">
    <div class="picture-container">
        <img src="<?php echo $kepPath; ?>" alt="<?php echo $kepNev; ?>" style="max-width: 100%; height: auto;">
    </div>
    <div class="info-container">
        <h2><?php echo $kepNev; ?></h2>
        <p><strong>Feltöltő:</strong> <?php echo $feltolto; ?></p>
        <p><strong>Helyszín:</strong> <?php echo $varos ?: 'Ismeretlen'; ?></p>
        <p><strong>Likeok száma:</strong> <?php echo $ertekeles; ?></p>

        <?php if (isset($_SESSION['fID'])): ?>
            <form action="like.php" method="post">
                <input type="hidden" name="kepID" value="<?php echo $kepID; ?>">
                <button type="submit"
                        class="like-button <?php echo $isLiked ? 'liked' : ''; ?>"
                    <?php echo $isLiked ? 'disabled' : ''; ?>>
                    👍 Like
                </button>
            </form>

            <form action="comment.php" method="post">
                <input type="hidden" name="kepID" value="<?php echo $kepID; ?>">
                <textarea name="comment" placeholder="Írd ide a hozzászólásod..." required></textarea>
                <button type="submit">Komment beküldése</button>
            </form>
        <?php endif; ?>

    </div>

        <div class="comments-container">
            <h3>Kommentek</h3>
            <div class="comments-scrollable">
                <div class="comments">
                    <?php
                    $kommentQuery = "SELECT h.tartalom, f.fNev
                            FROM Hozzaszolas h
                            JOIN Felhasznalo f ON h.fID = f.fID
                            WHERE h.kepID = :kepID
                            ORDER BY h.hozzaszolasID DESC";
                    $kommentStmt = oci_parse($conn, $kommentQuery);
                    oci_bind_by_name($kommentStmt, ":kepID", $kepID);
                    oci_execute($kommentStmt);

                    while ($komment = oci_fetch_assoc($kommentStmt)) {
                        $nev = htmlspecialchars($komment['FNEV']);
                        $tartalom = htmlspecialchars($komment['TARTALOM']);
                        echo "
                            <div class='comment'>
                                <div class='comment-user'>$nev</div>
                                <div class='comment-text'>$tartalom</div>
                            </div>
                        ";
                    }
                    ?>
                </div>
            </div>
        </div>
</div>


</body>
</html>
