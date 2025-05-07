<?php
session_start();
include('resources/SUPPORT_FUNCS/db_connection.php');

if (!isset($_SESSION['fID'])) {
    echo "Hiba: csak bejelentkezett felhasználók érhetik el az albumokat.";
    exit;
}

$albumID = isset($_GET['album']) ? intval($_GET['album']) : 0;

$query = "SELECT fID FROM Album WHERE aID = :albumID";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ":albumID", $albumID);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
$albumTulajdonosID = $row ? $row['FID'] : -1;
oci_free_statement($stmt);

if (!isset($_GET['album'])) {
    echo "Hiba: nincs megadva album azonosító.";
    exit;
}

if ($_SESSION['is_admin']) {
    $checkQuery = "SELECT SUM(k.ertekeles) AS points, COUNT(k.kepID) AS numberOfPics, a.albumNev
                        FROM Album a INNER JOIN Tartalmaz t ON a.aID = t.aID
                        INNER JOIN Kep k ON k.kepID = t.kepID 
                        WHERE a.aID = :albumID
                        GROUP BY a.albumNev";
    $checkStmt = oci_parse($conn, $checkQuery);
    oci_bind_by_name($checkStmt, ":albumID", $albumID);
} else {
    $checkQuery = "SELECT SUM(k.ertekeles) AS points, COUNT(k.kepID) AS numberOfPics, a.albumNev
                        FROM Album a INNER JOIN Tartalmaz t ON a.aID = t.aID
                        INNER JOIN Kep k ON k.kepID = t.kepID 
                        WHERE a.aID = :albumID AND a.fID = :fID
                        GROUP BY a.albumNev";
    $checkStmt = oci_parse($conn, $checkQuery);
    oci_bind_by_name($checkStmt, ":albumID", $albumID);
    oci_bind_by_name($checkStmt, ":fID", $albumTulajdonosID);
}
oci_execute($checkStmt);

$albumNev = null;
$numberOfPics = 0;
$albumPoints = 0;
if ($row = oci_fetch_assoc($checkStmt)) {
    $albumNev = htmlspecialchars($row['ALBUMNEV'], ENT_QUOTES, 'UTF-8');
    $numberOfPics = htmlspecialchars($row['NUMBEROFPICS'], ENT_QUOTES, 'UTF-8');
    $albumPoints = htmlspecialchars($row['POINTS'], ENT_QUOTES, 'UTF-8');
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['editAlbum'])) {
        if (!empty($_POST["newAlbumName"])) {
            $newName = trim($_POST["newAlbumName"]);
            $stmt = oci_parse($conn, "UPDATE Album SET albumNev = :newName WHERE aID = :albumID");
            oci_bind_by_name($stmt, ":newName", $newName);
            oci_bind_by_name($stmt, ":albumID", $albumID);
            oci_execute($stmt);
            oci_free_statement($stmt);
        }

        if (!empty($_POST["deleteFromAlbum"])) {
            $kepIDs = explode(",", $_POST["deleteFromAlbum"]);

            foreach ($kepIDs as $kepID) {
                $stmt = oci_parse($conn, "DELETE FROM Tartalmaz WHERE aID = :albumID AND kepID = :kepID");
                oci_bind_by_name($stmt, ":albumID", $albumID);
                oci_bind_by_name($stmt, ":kepID", $kepID);
                oci_execute($stmt);
                oci_free_statement($stmt);
            }
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
    <title><?php echo htmlspecialchars($albumNev, ENT_QUOTES, 'UTF-8'); ?> képei</title>
    <link rel="stylesheet" href="resources/CSS/styles.css">
    <link rel="stylesheet" href="resources/CSS/index.css">
    <link rel="stylesheet" href="resources/CSS/upload.css">
</head>

<body>
<?php include 'navbar.php'; ?>
<script src="resources/JS/popup.js"></script>

<?php if (isset($_SESSION["fID"]) && $_SESSION["fID"] == $albumTulajdonosID) {
    echo '<button class="add-images-button" onclick="openAddPhotosPopup()">Képek hozzáadása</button>';
}
?>
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
                    echo '<div class="photo-option" onclick="togglePhotoSelection(this)" data-kepid="' . $row['KEPID'] . '">';
                    echo '<img src="' . $kepFile . '" alt="' . htmlspecialchars($row['KEPNEV'], ENT_QUOTES, 'UTF-8') . '">';
                    echo '<div class="photo-name">' . htmlspecialchars($row['KEPNEV'], ENT_QUOTES, 'UTF-8') . '</div>';
                    echo '</div>';
                }
                ?>
            </div>

            <input type="hidden" id="selectedPhotos" name="selectedPhotos">

            <?php if ($vanKep): ?>
                <button type="submit" name="addPhotosToAlbum">Hozzáadás</button>
            <?php else: ?>
                <div class='empty-gallery'>
                    Nincs több kép amit hozzáadhatna az albumhoz!
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="container-album">

    <div style="display: flex; gap: 5%; align-items: center;">
        <h2>Album neve: <?php echo $albumNev; ?></h2>

        <button onclick="openAlbumEditPopup()">Album módosítása</button>
        <div id="editAlbumPopup" class="popup" style="display: none;">
            <div class="popup-content">
                <span class="close" onclick="closeAlbumEditPopup()">&times;</span>
                <h2>Album módosítása</h2>
                <form method="POST" onsubmit="return updateAlbumEditForm();">
                    <input type="hidden" name="albumID" value="<?php echo $albumID; ?>">

                    <label for="newAlbumName">Új albumnév:</label>
                    <input type="text" id="newAlbumName" name="newAlbumName" placeholder="Új név">

                    <h3>Képek törlése (válassza ki a képeket):</h3>
                    <div class="photo-album-del-select-grid">
                        <?php
                        $stmt = oci_parse($conn, "SELECT k.kepID, k.kepNev
                                                        FROM Tartalmaz t
                                                        JOIN Kep k ON t.kepID = k.kepID
                                                        WHERE t.aID = :albumID
                                                        ORDER BY k.kepNev ASC");
                        oci_bind_by_name($stmt, ":albumID", $albumID);
                        oci_execute($stmt);

                        $vanKep = false;

                        while ($row = oci_fetch_assoc($stmt)) {
                            $vanKep = true;
                            $kepPath = "resources/APP_IMGS";
                            $kepFile = "resources/APP_IMGS/placeholder.png";
                            foreach (scandir($kepPath) as $file) {
                                if (fnmatch($row['KEPNEV'] . ".*", $file)) {
                                    $kepFile = $kepPath . "/" . $file;
                                    break;
                                }
                            }
                            echo '<div class="photo-album-del-option" onclick="toggleAlbumEditPhotoSelection(this)" data-kepid="' . $row['KEPID'] . '">';
                            echo '<img src="' . $kepFile . '" alt="' . htmlspecialchars($row['KEPNEV'], ENT_QUOTES, 'UTF-8') . '">';
                            echo '<div class="photo-album-del-name">' . htmlspecialchars($row['KEPNEV'], ENT_QUOTES, 'UTF-8') . '</div>';
                            echo '</div>';
                        }

                        if (!$vanKep) {
                            echo "<div class='empty-gallery'>
                                    Jelenleg még nincs kép az albumban!
                                </div>";
                        }

                        oci_free_statement($stmt);
                        ?>
                    </div>
                    <input type="hidden" name="deleteFromAlbum" id="deleteFromAlbum">
                    <button type="submit" name="editAlbum">Módosítás</button>
                </form>
            </div>
        </div>
    </div>

    <p>
        <?php
        echo '(képek: ' . $numberOfPics . ' db, összesített pontok: ' . $albumPoints . ' )';
        ?>
    </p>

    <div class="grid-container">
        <?php
        $query = "SELECT k.kepID, k.kepNev, k.ERTEKELES
                    FROM Tartalmaz t
                    JOIN Kep k ON t.kepID = k.kepID
                    WHERE t.aID = :albumID
                    ORDER BY k.kepNev ASC";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ":albumID", $albumID);
        oci_execute($stmt);

        $vanKep = false;

        while ($row = oci_fetch_assoc($stmt)) {
            $vanKep = true;
            $kepID = $row['KEPID'];
            $kepNev = htmlspecialchars($row['KEPNEV'], ENT_QUOTES, 'UTF-8');
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
