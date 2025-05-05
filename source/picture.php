<?php
session_start();
require_once 'resources/SUPPORT_FUNCS/db_connection.php';

$fID = $_SESSION['fID'] ?? null;
$isAdmin = $_SESSION['isAdmin'] ?? false;
$dir = 'resources/APP_IMGS/';

if (!isset($_GET['id'])) {
    echo "Nincs megadva kép azonosító.";
    exit();
}

$kepID = intval($_GET['id']);

$query = "
    SELECT k.kepID, k.kepNev, k.ertekeles, f.fID, f.fNev AS felhasznaloNev, h.varos
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
    $feltolto = htmlspecialchars($row['FELHASZNALONEV']);
    $feltoltoID = $row['FID'];
    $varos = htmlspecialchars($row['VAROS']);
    $ertekeles = $row['ERTEKELES'];
    $canEdit = $isAdmin || ($fID !== null && $fID == $feltoltoID);

    $kepPath = 'resources/APP_IMGS/placeholder.png';
    $files = scandir($dir);
    foreach ($files as $file) {
        if (fnmatch($kepNev . ".*", $file)) {
            $kepPath = 'resources/APP_IMGS/' . $file;
            break;
        }
    }
} else {
    die('Nem található ilyen kép.');
}

$helyek = [];
$helyQuery = "SELECT helyID, varos FROM Hely";
$helyStmt = oci_parse($conn, $helyQuery);
oci_execute($helyStmt);
while ($row = oci_fetch_assoc($helyStmt)) {
    $helyek[] = $row;
}

$kategoriak = [];
$katQuery = "SELECT katID, kategoriaNev FROM Kategoria";
$katStmt = oci_parse($conn, $katQuery);
oci_execute($katStmt);
while ($row = oci_fetch_assoc($katStmt)) {
    $kategoriak[] = $row;
}

$kategoriakNev = 'Ismeretlen';
$katQuery = "
    SELECT k.kategoriaNev
    FROM KategoriaResze kr
    JOIN Kategoria k ON kr.katID = k.katID
    WHERE kr.kepID = :kepID
";
$katStmt = oci_parse($conn, $katQuery);
oci_bind_by_name($katStmt, ":kepID", $kepID);
oci_execute($katStmt);
if ($katRow = oci_fetch_assoc($katStmt)) {
    $kategoriakNev = htmlspecialchars($katRow['KATEGORIANEV']);
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
    <link rel="stylesheet" href="resources/CSS/style.css">
    <link rel="stylesheet" href="resources/CSS/picture.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="picture-page">
    <div class="picture-container">
        <img src="<?php echo $kepPath; ?>" alt="<?php echo $kepNev; ?>">
    </div>
    <div class="info-container">
        <h2><?php echo $kepNev; ?></h2>
        <p><strong>Feltöltő:</strong> <?php echo $feltolto; ?></p>
        <p><strong>Helyszín:</strong> <?php echo $varos ?: 'Ismeretlen'; ?></p>
        <p><strong>Kategória:</strong> <?php echo $kategoriakNev ?: 'Ismeretlen'; ?></p>
        <p><strong>Likeok száma:</strong> <?php echo $ertekeles; ?></p>

        <?php if (isset($_SESSION['fID'])): ?>
            <form action="like.php" method="post">
                <input type="hidden" name="kepID" value="<?php echo $kepID; ?>">
                <button type="submit" class="like-button <?php echo $isLiked ? 'liked' : ''; ?>"
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

        <?php if ($canEdit): ?>
            <h3>Kép szerkesztése</h3>
            <form action="update_kep.php" method="post">
                <input type="hidden" name="kepID" value="<?php echo $kepID; ?>">

                <label for="helyID">Helyszín:</label>
                <select name="helyID" id="helyID" required>
                    <?php foreach ($helyek as $hely): ?>
                        <option value="<?php echo $hely['HELYID']; ?>" <?php if ($hely['VAROS'] === $varos) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($hely['VAROS']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="kategoriaID">Kategória:</label>
                <select name="kategoriaID" id="kategoriaID" required>
                    <?php foreach ($kategoriak as $kat): ?>
                        <option value="<?php echo $kat['KATID']; ?>"
                            <?php if ($kat['KATEGORIANEV'] === $kategoriakNev) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($kat['KATEGORIANEV']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Mentés</button>
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

                $hasComment = false;

                while ($komment = oci_fetch_assoc($kommentStmt)) {
                    $hasComment = true;
                    $nev = htmlspecialchars($komment['FNEV']);
                    $tartalom = htmlspecialchars($komment['TARTALOM']);
                    echo "
                    <div class='comment'>
                        <div class='comment-user'>$nev</div>
                        <div class='comment-text'>$tartalom</div>
                    </div>
                    ";
                }

                if (!$hasComment) {
                    echo "<div class='no-comments'>Jelenleg még nincs egy komment sem!</div>";
                }
                ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
