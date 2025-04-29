<?php
session_start();
include('resources/SUPPORT_FUNCS/db_connection.php');

if (!isset($_SESSION['fID'])) {
    echo "Hiba: csak bejelentkezett felhasználók érhetik el az albumokat.";
    exit;
}

$fID = $_SESSION['fID'];


if (!isset($_GET['album'])) {
    echo "Hiba: nincs megadva album azonosító.";
    exit;
}

$albumID = (int)$_GET['album'];

$checkQuery = "SELECT albumNev FROM Album WHERE aID = :albumID AND fID = :fID";
$checkStmt = oci_parse($conn, $checkQuery);
oci_bind_by_name($checkStmt, ":albumID", $albumID);
oci_bind_by_name($checkStmt, ":fID", $fID);
oci_execute($checkStmt);

$albumNev = null;
if ($row = oci_fetch_assoc($checkStmt)) {
    $albumNev = htmlspecialchars($row['ALBUMNEV']);
}
oci_free_statement($checkStmt);

if (!$albumNev) {
    echo "Hiba: nincs jogosultságod megtekinteni ezt az albumot.";
    exit;
}

if (isset($_POST['addPhotosToAlbum'])) {
    if (!empty($_POST['selectedPhotos'])) {
        $kepIDs = explode(',', $_POST['selectedPhotos']);
        foreach ($kepIDs as $kepID) {
            $insert = "INSERT INTO Tartalmaz (aID, kepID) VALUES (:aID, :kepID)";
            $insertStmt = oci_parse($conn, $insert);
            oci_bind_by_name($insertStmt, ":aID", $albumID);
            oci_bind_by_name($insertStmt, ":kepID", $kepID);
            oci_execute($insertStmt);
            oci_free_statement($insertStmt);
        }

        header("Location: album.php?album=" . $albumID);
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($albumNev); ?> képei</title>
    <link rel="stylesheet" href="resources/CSS/styles.css">
    <link rel="stylesheet" href="resources/CSS/index.css">
    <link rel="stylesheet" href="resources/CSS/upload.css">
</head>

<body>
<?php include 'navbar.php'; ?>
<script src="resources/JS/popup.js"></script>

<button class="add-images-button" onclick="openAddPhotosPopup()">Képek hozzáadása</button>
<div id="addPhotosPopup" class="popup" style="display: none;">
    <div class="popup-content">
        <span onclick="closeAddPhotosPopup()" class="close">&times;</span>
        <h2>Képek kiválasztása</h2>
        <form method="POST" onsubmit="updateSelectedPhotos()">
            <div class="photo-select-grid">
                <?php
                $vanKep = false;
                $query = "SELECT kepID, kepNev FROM Kep WHERE fID = :fID AND kepID NOT IN (
                    SELECT kepID FROM Tartalmaz WHERE aID = :aID
                )";
                $stmt = oci_parse($conn, $query);
                oci_bind_by_name($stmt, ":fID", $_SESSION['fID']);
                oci_bind_by_name($stmt, ":aID", $albumID);
                oci_execute($stmt);

                while ($row = oci_fetch_assoc($stmt)) {
                    $vanKep = true;

                    $kepPath = "resources/APP_IMGS";
                    $kepFile = "resources/APP_IMGS/placeholder.png";

                    $files = scandir($kepPath);
                    foreach ($files as $file) {
                        if (fnmatch($row['KEPNEV'] . ".*", $file)) {
                            $kepFile = $kepPath . "/" . $file;
                            break;
                        }
                    }
                    echo '<div class="photo-option" onclick="togglePhotoSelection(this)" data-kepid="'.$row['KEPID'].'">';
                    echo '<img src="'.$kepFile.'" alt="'.htmlspecialchars($row['KEPNEV']).'">';
                    echo '<div class="photo-name">'.htmlspecialchars($row['KEPNEV']).'</div>';
                    echo '</div>';
                }
                ?>
            </div>

            <input type="hidden" id="selectedPhotos" name="selectedPhotos">

            <?php if ($vanKep): ?>
                <button type="submit" name="addPhotosToAlbum">Hozzáadás</button>
            <?php else: ?>
                <div class='empty-gallery'>
                    Minden képe hozzáadásra került.
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="container-album">
    <h2 style="text-align: center;"><?php echo $albumNev; ?></h2>
    <div class="grid-container">
        <?php
        $query = "
                    SELECT k.kepID, k.kepNev, k.ERTEKELES
                    FROM Tartalmaz t
                    JOIN Kep k ON t.kepID = k.kepID
                    WHERE t.aID = :albumID
                    ORDER BY k.kepNev ASC
                ";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ":albumID", $albumID);
        oci_execute($stmt);

        $vanKep = false;

        while ($row = oci_fetch_assoc($stmt)) {
            $vanKep = true;
            $kepID = $row['KEPID'];
            $kepNev = htmlspecialchars($row['KEPNEV']);
            $kepPath = "resources/APP_IMGS";
            $likeok = $row['ERTEKELES'];

            $files = scandir($kepPath);
            $kepFile = "resources/APP_IMGS/placeholder.png";

            foreach ($files as $file) {
                if (fnmatch($kepNev . ".*", $file)) {
                    $kepFile = $kepPath . "/" . $file;
                    break;
                }
            }

            echo '<a href="picture.php?id=' . $kepID . '" class="grid-item" style="text-decoration: none; color: inherit;">';
            echo '<img src="' . $kepFile . '" alt="' . $kepNev . '" style="width: 100%; height: 150px; object-fit: cover; border-radius: 10px;">';
            echo '<div style="font-weight: bold">' . $kepNev . '<br>👍 ' . $likeok . '</div>';
            echo '</a>';
        }

        if (!$vanKep) {
            echo "<div class='empty-gallery'>
                        Jelenleg még nincs kép az albumban!
                    </div>";
        }
        oci_free_statement($stmt);
        oci_close($conn);
        ?>
    </div>
</div>
</body>
</html>
