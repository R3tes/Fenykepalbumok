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
        SELECT k.kepID, k.kepNev, k.ertekeles, f.fID, f.fNev AS felhasznaloNev, h.varos, h.megye, h.orszag
        FROM Kep k
        JOIN Felhasznalo f ON k.fID = f.fID
        LEFT JOIN Hely h ON k.helyID = h.helyID
        WHERE k.kepID = :kepID
";

$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ":kepID", $kepID);
oci_execute($stmt);

if ($row = oci_fetch_assoc($stmt)) {
    $kepNev = htmlspecialchars($row['KEPNEV'], ENT_QUOTES, 'UTF-8');

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

    $kepNev = htmlspecialchars($row['KEPNEV'], ENT_QUOTES, 'UTF-8');
    $feltolto = htmlspecialchars($row['FELHASZNALONEV'], ENT_QUOTES, 'UTF-8');
    $orszag = !empty($row['ORSZAG']) ? htmlspecialchars($row['ORSZAG'], ENT_QUOTES, 'UTF-8') : "Ismeretlen";
    $megye = !empty($row['MEGYE']) ? htmlspecialchars($row['MEGYE'], ENT_QUOTES, 'UTF-8') : "Ismeretlen";
    $varos = !empty($row['VAROS']) ? htmlspecialchars($row['VAROS'], ENT_QUOTES, 'UTF-8') : "Ismeretlen";
    $ertekeles = $row['ERTEKELES'];

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

// Datalist előkészítése
$countries = $counties = $cities = $categories = [];

$stid = oci_parse($conn, "SELECT DISTINCT orszag FROM Hely");
oci_execute($stid);
while ($row = oci_fetch_assoc($stid)) {
    $countries[] = $row['ORSZAG'];
}
$stid = oci_parse($conn, "SELECT DISTINCT megye FROM Hely");
oci_execute($stid);
while ($row = oci_fetch_assoc($stid)) {
    $counties[] = $row['MEGYE'];
}
$stid = oci_parse($conn, "SELECT DISTINCT varos FROM Hely");
oci_execute($stid);
while ($row = oci_fetch_assoc($stid)) {
    $cities[] = $row['VAROS'];
}
$stid = oci_parse($conn, "SELECT DISTINCT kategoriaNev FROM Kategoria");
oci_execute($stid);
while ($row = oci_fetch_assoc($stid)) {
    $categories[] = $row['KATEGORIANEV'];
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
        <h2>Kép címe: <?php echo $kepNev; ?></h2>
        <p><strong>Feltöltő:</strong> <?php echo $feltolto; ?></p>
        <p><strong>Helyszín:</strong> <?php
            echo "Ország: $orszag, Megye: $megye, Város: $varos.";
            ?></p>
        <p><strong>Kategória:</strong> <?php echo $kategoriakNev ?: 'Ismeretlen'; ?></p>
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

        <?php if ($canEdit): ?>
            <h3>Kép szerkesztése</h3>
            <form action="update_kep.php" method="post" class="edit-form">
                <input type="hidden" name="kepID" value="<?php echo $kepID; ?>">

                <div class="form-group">
                    <label for="orszagInput">Ország:</label>
                    <input list="countries" id="orszagInput" name="orszag" value="<?php echo $orszag; ?>" required>
                    <datalist id="countries">
                        <?php foreach ($countries as $country): ?>
                            <option value="<?= htmlspecialchars($country, ENT_QUOTES, 'UTF-8') ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="form-group">
                    <label for="megyeInput">Megye:</label>
                    <input list="counties" id="megyeInput" name="megye" value="<?php echo $megye; ?>" required>
                    <datalist id="counties">
                        <?php foreach ($counties as $county): ?>
                            <option value="<?= htmlspecialchars($county, ENT_QUOTES, 'UTF-8') ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="form-group">
                    <label for="varosInput">Város:</label>
                    <input list="cities" id="varosInput" name="varos" value="<?php echo $varos; ?>" required>
                    <datalist id="cities">
                        <?php foreach ($cities as $city): ?>
                            <option value="<?= htmlspecialchars($city, ENT_QUOTES, 'UTF-8') ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="form-group">
                    <label for="categoryInput">Kategória:</label>
                    <input list="categories" id="categoryInput" name="category" value="<?php echo $kategoriakNev; ?>" required>
                    <datalist id="categories">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <button type="submit" class="save-btn">Mentés</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="comments-container">
        <h3>Kommentek</h3>
        <div class="comments-scrollable">
            <div class="comments">
                <?php
                $query = "BEGIN :cursor := get_kommentek(:kepID); END;";
                $stmt = oci_parse($conn, $query);

                $refCursor = oci_new_cursor($conn);
                oci_bind_by_name($stmt, ":cursor", $refCursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stmt, ":kepID", $kepID, -1, SQLT_INT);

                oci_execute($stmt);
                oci_execute($refCursor); // execute the cursor itself

                $hasComment = false;

                while ($komment = oci_fetch_assoc($refCursor)) {
                    $hasComment = true;
                    $nev = htmlspecialchars($komment['FNEV'], ENT_QUOTES, 'UTF-8');
                    $tartalom = htmlspecialchars($komment['TARTALOM'], ENT_QUOTES, 'UTF-8');
                    echo "
                    <div class='comment'>
                        <div class='comment-user'>$nev</div>
                        <div class='comment-text'>$tartalom</div>
                    </div>
                ";
                }

                oci_free_statement($stmt);
                oci_free_statement($refCursor);

                if (!$hasComment) {
                    echo "
                    <div class='no-comments'>
                        Jelenleg még nincs egy komment sem!
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
