<?php
session_start();
include('resources/SUPPORT_FUNCS/db_connection.php');
if (!isset($_SESSION["fID"])) {
    $_SESSION["fID"] = -1;
}
if (!isset($_SESSION["is_admin"])) {
    $_SESSION["is_admin"] = false;
}

$fID = $_GET["id"];

if (isset($_POST['createAlbum'])) {
    if (isset($_SESSION['fID'])) {
        $albumName = trim($_POST['albumName']);
        $fID = $_SESSION['fID'];

        if (!empty($albumName)) {
            $insertAlbum = "INSERT INTO Album (aID, albumNev, fID) VALUES (album_seq.NEXTVAL, :albumNev, :fID)";
            $stmt = oci_parse($conn, $insertAlbum);
            oci_bind_by_name($stmt, ":albumNev", $albumName);
            oci_bind_by_name($stmt, ":fID", $fID);

            if (oci_execute($stmt)) {
                oci_free_statement($stmt);

                $_SESSION['success_message'] = "Album létrehozás sikeresen megtörtént.";
                header("Location: profile.php?id=" . (int)$_SESSION["fID"]);
                exit();
            } else {
                $error = oci_error($stmt);
                echo "Hiba történt: " . $error['message'];
            }
        } else {
            echo "Adj meg egy albumnevet!";
        }
    } else {
        echo "Csak bejelentkezett felhasználók hozhatnak létre albumot.";
    }
}

$albumCountQuery = "SELECT COUNT(*) AS NUMBEROFALBUMS FROM Album WHERE fID = :fID";
$albumCountStmt = oci_parse($conn, $albumCountQuery);
oci_bind_by_name($albumCountStmt, ":fID", $_SESSION['fID']);
oci_execute($albumCountStmt);
$albumCountRow = oci_fetch_assoc($albumCountStmt);
$numberOfAlbums = $albumCountRow["NUMBEROFALBUMS"];
oci_free_statement($albumCountStmt);

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fIDUpload = $_SESSION['fID'];
    $kepNev = $_POST['name'] ?? '';
    $orszag = $_POST['countries'] ?? '';
    $megye = $_POST['counties'] ?? '';
    $varos = $_POST['cities'] ?? '';
    $kategoriakInput = $_POST['categories'] ?? '';
    $kategoriak = array_filter(explode(' ', trim($kategoriakInput)));

    // Feltöltés előtt ellenőrzés
    $checkStmt = oci_parse($conn, "SELECT COUNT(*) AS CNT FROM Kep WHERE kepNev = :nev");
    oci_bind_by_name($checkStmt, ":nev", $kepNev);
    oci_execute($checkStmt);
    $checkRow = oci_fetch_assoc($checkStmt);

    if ($checkRow && $checkRow['CNT'] > 0) {
        $_SESSION['error_message'] = "Ez a képnév már létezik. Kérlek válassz másikat.";
        header("Location: profile.php?id=" . $_GET["id"]);
        exit();
    }

    $hely = NULL;
    if (!empty($_POST['countries']) && !empty($_POST['counties']) && !empty($_POST['cities'])) {
        $query = "BEGIN get_or_create_hely(:city, :county, :country, :helyID); END;";
        $stmt = oci_parse($conn, $query);

        oci_bind_by_name($stmt, ":city", $varos);
        oci_bind_by_name($stmt, ":county", $megye);
        oci_bind_by_name($stmt, ":country", $orszag);
        oci_bind_by_name($stmt, ":helyID", $hely, -1, SQLT_INT);

        if (!oci_execute($stmt)) {
            $e = oci_error($stmt);
            die("Database Error: " . $e['message']);
        }
    }
    $kepID = 0;
    if (!empty($kepNev) && !empty($fIDUpload)) {
        $stmt = oci_parse($conn, "INSERT INTO Kep (kepID, kepNev, fID, helyID)
                                      VALUES (kep_seq.NEXTVAL, :kepNev, :fID, :helyID)
                                      RETURNING kepID INTO :kepID");
        oci_bind_by_name($stmt, ":kepNev", $kepNev);
        oci_bind_by_name($stmt, ":fID", $fIDUpload);
        oci_bind_by_name($stmt, ":helyID", $hely);
        oci_bind_by_name($stmt, ":kepID", $kepID, SQLT_INT);
        if (!oci_execute($stmt)) {
            $e = oci_error($stmt);
            die("Database Error: " . $e['message']);
        }

        if (isset($_FILES["uploadedFile"]) && $_FILES["uploadedFile"]["error"] == 0) {
            $uploadDir = "resources/APP_IMGS/";
            $fileExt = strtolower(pathinfo($_FILES["uploadedFile"]["name"], PATHINFO_EXTENSION));
            $uploadFile = $uploadDir . $kepNev . "." . $fileExt;
            $check = getimagesize($_FILES["uploadedFile"]["tmp_name"]);
            if (!$check) {
                die("File is not a valid image.");
            }
            if (!move_uploaded_file($_FILES["uploadedFile"]["tmp_name"], $uploadFile)) {
                echo "The file " . htmlspecialchars(basename($_FILES["uploadedFile"]["name"]), ENT_QUOTES, 'UTF-8') . " failed to upload.";
            }
        }
    }

    if (!empty($_POST['categories'])) {
        $katRes = [];

        foreach ($kategoriak as $katNev) {
            $katNev = trim($katNev);
            if (empty($katNev)) continue;

            $stmt = oci_parse($conn, "BEGIN add_category_link(:katNev, :kepID); END;");

            oci_bind_by_name($stmt, ":katNev", $katNev);
            oci_bind_by_name($stmt, ":kepID", $kepID, -1, SQLT_INT); // assuming $kepID is an integer;

            oci_execute($stmt);
            oci_free_statement($stmt);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['deletePhotos'])) {
        if (!empty($_POST['selectedPhotos'])) {
            $kepIDs = explode(',', $_POST['selectedPhotos']);
            foreach ($kepIDs as $kepID) {

                $stmt = oci_parse($conn, "SELECT kepNev FROM Kep WHERE kepID = :kepID AND fID = :fID");
                oci_bind_by_name($stmt, ":kepID", $kepID);
                oci_bind_by_name($stmt, ":fID", $_SESSION['fID']);
                oci_execute($stmt);
                $row = oci_fetch_assoc($stmt);
                oci_free_statement($stmt);

                if ($row && isset($row['KEPNEV'])) {
                    $filenameBase = $row['KEPNEV'];
                    $filePathPattern = "resources/APP_IMGS/" . $filenameBase . ".*";

                    foreach (glob($filePathPattern) as $fileToDelete) {
                        unlink($fileToDelete);
                    }

                    $stmt = oci_parse($conn, "DELETE FROM Kep WHERE kepID = :kepID AND fID = :fID");
                    oci_bind_by_name($stmt, ":kepID", $kepID);
                    oci_bind_by_name($stmt, ":fID", $_SESSION['fID']);
                    oci_execute($stmt);
                    oci_free_statement($stmt);
                }
            }

            $_SESSION['success_message'] = "Kép(ek) törlése sikeresen megtörtént.";
            header("Location: profile.php?id=" . $_SESSION['fID']);
            exit();
        }
    }

    if (isset($_POST['deleteAlbums']) && !empty($_POST['selectedAlbums'])) {
        $albumIDs = explode(',', $_POST['selectedAlbums']);

        foreach ($albumIDs as $albumID) {
            $stmt = oci_parse($conn, "DELETE FROM Album WHERE aID = :aID AND fID = :fID");
            oci_bind_by_name($stmt, ":aID", $albumID);
            oci_bind_by_name($stmt, ":fID", $_SESSION['fID']);
            oci_execute($stmt);
            oci_free_statement($stmt);
        }

        $_SESSION['success_message'] = "Album(ok) törlése sikeresen megtörtént.";
        header("Location: profile.php?id=" . $_SESSION['fID']);
        exit();
    }

    $_SESSION['success_message'] = "Kép feltöltése sikeresen megtörtént.";
    header("Location: profile.php?id=" . $_SESSION["fID"]);
    exit();
}
?>
    <!DOCTYPE html>
    <html lang="hu">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Profil</title>
        <link rel="stylesheet" href="resources/CSS/styles.css">
        <link rel="stylesheet" href="resources/CSS/upload.css">
        <script src="resources/JS/popup.js"></script>
    </head>
    <body>
    <?php include 'navbar.php'; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <script>
            alert("<?php echo addslashes($_SESSION['error_message']); ?>");
        </script>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <script>
            alert("<?= addslashes($_SESSION['success_message']) ?>");
        </script>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <main>
        <div class="title">
            <h1>
                <?php
                $stmt = oci_parse($conn, "SELECT f.fNev FROM Felhasznalo f WHERE f.fID = :fID");
                oci_bind_by_name($stmt, ":fID", $fID);
                if (oci_execute($stmt)) {
                    $row = oci_fetch_assoc($stmt);
                    echo $row["FNEV"];
                } else {
                    $e = oci_error($stmt);
                    die("Database Error: " . $e['message']);
                }
                $stmt = oci_parse($conn, "SELECT TO_CHAR(f.created_at, 'YYYY-MM-DD') AS regDatum FROM Felhasznalo f WHERE f.fID = :fID");
                oci_bind_by_name($stmt, ":fID", $fID);
                if (oci_execute($stmt)) {
                    $row = oci_fetch_assoc($stmt);
                    if ($row) { // Check if the result is valid
                        $regDatum = $row["REGDATUM"];
                        echo "<p>Regisztráció dátuma: $regDatum</p>";
                    } else {
                        echo "<p>No registration date found.</p>";
                    }
                } else {
                    $e = oci_error($stmt);
                    die("Database Error: " . $e['message']);
                }
                ?>
            </h1>
            <p>
                <?php
                $query = "
                     BEGIN
                         get_user_stat(:fID, :points, :numPics);
                     END;";


                $stmt = oci_parse($conn, $query);

                oci_bind_by_name($stmt, ":fID", $fID, -1, SQLT_INT);
                oci_bind_by_name($stmt, ":points", $points, -1, SQLT_INT);
                oci_bind_by_name($stmt, ":numPics", $numPics, -1, SQLT_INT);

                if (oci_execute($stmt)) {
                    echo '(képek: ' . $numPics . ' db, összesített pontok: ' . $points . ' )';
                } else {
                    $e = oci_error($stmt);
                    die("Database Error: " . $e['message']);
                }

                ?>
            </p>
        </div>
        <?php if ($_SESSION['fID'] == $fID || $_SESSION['is_admin']): ?>
    <?php if ($_SESSION['fID'] == $fID): ?>
        <div class="topArea">
            <button onclick="openPopup()">Kép feltöltése</button>
            <div id="uploadPopup" class="popup" style="display: none;">
                <div class="popup-content">
                    <span onclick="closePopup()" class="close">&times;</span>
                    <form method="POST" enctype="multipart/form-data">
                        <link rel="stylesheet" href="resources/CSS/upload.css">
                        <div class="formHead">
                            <h2>Fénykép feltöltése</h2>
                        </div>

                        <div class="uploadForm">

                            <div class="drop-area">
                                <label for="fileInput">Válassza ki a feltöltendő képet:</label>
                                <input type="file" id="fileInput" name="uploadedFile" accept="image/*" required>
                            </div>
                            <div class="formElement">
                                <label for="nameInput">Név:</label>
                                <input id="nameInput" name="name" required>
                                <label for="place">Hely:</label>
                                <div id="place">
                                    <input list="countries" id="countriesInput" name="countries" placeholder="Ország">
                                    <datalist id="countries">
                                        <?php foreach ($countries as $country): ?>
                                            <option value="<?= htmlspecialchars($country, ENT_QUOTES, 'UTF-8') ?>"></option>
                                        <?php endforeach; ?>
                                    </datalist>

                                    <input list="counties" id="countiesInput" name="counties" placeholder="Megye">
                                    <datalist id="counties">
                                        <?php foreach ($counties as $county): ?>
                                            <option value="<?= htmlspecialchars($county, ENT_QUOTES, 'UTF-8') ?>"></option>
                                        <?php endforeach; ?>
                                    </datalist>

                                    <input list="cities" id="citiesInput" name="cities" placeholder="Város">
                                    <datalist id="cities">
                                        <?php foreach ($cities as $city): ?>
                                            <option value="<?= htmlspecialchars($city, ENT_QUOTES, 'UTF-8') ?>"></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>

                                <label for="categoryInput">Kategória:</label>
                                <input list="categories" type="text" id="categoryInput" name="categories"
                                       placeholder="életkép" required>
                                <datalist id="categories">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>"></option>
                                    <?php endforeach; ?>
                                </datalist>

                                <button type="submit">Feltöltés</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <button onclick="openAlbumPopup()">Új Album létrehozása</button>
            <div id="albumPopup" class="popup" style="display: none;">
                <div class="popup-content">
                    <span onclick="closeAlbumPopup()" class="close">&times;</span>
                    <form method="POST">
                        <link rel="stylesheet" href="resources/CSS/upload.css">
                        <div class="formHead">
                            <h2>Új Album Létrehozása</h2>
                        </div>

                        <div class="uploadForm">
                            <div class="formElement">
                                <label for="albumNameInput">Album neve:</label>
                                <input id="albumNameInput" name="albumName" required>
                                <button type="submit" name="createAlbum">Létrehozás</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <a href="modifyProfile.php" id="profileButton">
                <button class="interact">Felhasználói adatok szerkesztése</button>
            </a>

            <button onclick="openErtesitesPopup()" class="ertesites-gomb">🔔 Értesítések</button>
            <div id="ertesitesPopup" class="popup" style="display: none;">
                <div class="popup-content">
                    <span onclick="closeErtesitesPopup()" class="close">&times;</span>
                    <h2>Értesítések</h2>
                    <div class="ertesites-lista">
                        <?php
                        $ertesitesStmt = oci_parse($conn, "
                                SELECT e.ertesites_id, e.uzenet, e.letrehozas_datuma, e.olvasott
                                FROM Ertesites e
                                WHERE e.felhasznalo_id = :fID
                                ORDER BY e.letrehozas_datuma DESC
                    ");
                        oci_bind_by_name($ertesitesStmt, ":fID", $_SESSION['fID']);
                        oci_execute($ertesitesStmt);

                        $ures = true;
                        while ($ertesitesSor = oci_fetch_assoc($ertesitesStmt)) {
                            if ($ertesitesSor['OLVASOTT'] == 1) {
                                continue;
                            }

                            $ures = false;
                            $ertesites_id = $ertesitesSor['ERTESITES_ID'];
                            $uzenet = htmlspecialchars($ertesitesSor['UZENET'], ENT_QUOTES, 'UTF-8');
                            $datum = date("Y.m.d", strtotime($ertesitesSor['LETREHOZAS_DATUMA']));

                            echo "
                                <div class='ertesites-elem' id='ertesites-$ertesites_id'>
                                    <div class='ertesites-sor'>
                                        <div class='ertesites-szoveg'>
                                            <strong>$uzenet</strong><br>
                                            <small>$datum</small>
                                        </div>
                                        <button class='message-read' onclick=\"markAsRead($ertesites_id)\">Olvasottként megjelölés</button>
                                    </div>
                                </div>";
                        }
                        if ($ures) {
                            echo "<div class='empty-message'>Nincs megjeleníthető értesítés.</div>";
                        }
                        oci_free_statement($ertesitesStmt);
                        ?>
                    </div>
                </div>
            </div>

            <?php if ($numPics > 0) {
                echo '<button onclick="openDeletePhotoPopup()">Képek törlése</button>';
            } ?>
            <div id="deletePhotoPopup" class="popup" style="display: none;">
                <div class="popup-content">
                    <span onclick="closeDeletePhotoPopup()" class="close">&times;</span>
                    <h2>Képek törlése</h2>
                    <form method="POST" onsubmit="return updateSelectedPhotosForDeletion();">
                        <div class="photo-del-select-grid">
                            <?php
                            $query = "SELECT kepID, kepNev FROM Kep WHERE fID = :fID";
                            $stmt = oci_parse($conn, $query);
                            oci_bind_by_name($stmt, ":fID", $_SESSION['fID']);
                            oci_execute($stmt);

                            while ($row = oci_fetch_assoc($stmt)) {
                                $kepPath = "resources/APP_IMGS";
                                $kepFile = "resources/APP_IMGS/placeholder.png";

                                $files = scandir($kepPath);
                                foreach ($files as $file) {
                                    if (fnmatch($row['KEPNEV'] . ".*", $file)) {
                                        $kepFile = $kepPath . "/" . $file;
                                        break;
                                    }
                                }
                                echo '<div class="photo-del-option" onclick="togglePhotoSelection(this)" data-kepid="' . $row['KEPID'] . '">';
                                echo '<img src="' . $kepFile . '" alt="' . htmlspecialchars($row['KEPNEV'], ENT_QUOTES, 'UTF-8') . '">';
                                echo '<div class="photo-del-name">' . htmlspecialchars($row['KEPNEV'], ENT_QUOTES, 'UTF-8') . '</div>';
                                echo '</div>';
                            }
                            ?>
                        </div>

                        <input type="hidden" id="selectedPhotos" name="selectedPhotos">
                        <button type="submit" name="deletePhotos">Törlés</button>
                    </form>
                </div>
            </div>

            <?php if ($numberOfAlbums > 0) {
                echo '<button onclick="openDeleteAlbumPopup()">Albumok törlése</button>';
            } ?>
            <div id="deleteAlbumPopup" class="popup" style="display: none;">
                <div class="popup-content">
                    <span onclick="closeDeleteAlbumPopup()" class="close">&times;</span>
                    <h2>Albumok törlése</h2>
                    <form method="POST" onsubmit="return updateSelectedAlbumsForDeletion();">
                        <div class="album-del-select-grid">
                            <?php
                            $query = "SELECT aID, albumNev FROM Album WHERE fID = :fID ORDER BY albumNev";
                            $stmt = oci_parse($conn, $query);
                            oci_bind_by_name($stmt, ":fID", $_SESSION['fID']);
                            oci_execute($stmt);

                            while ($row = oci_fetch_assoc($stmt)) {
                                $albumID = $row['AID'];
                                $albumNev = htmlspecialchars($row['ALBUMNEV'], ENT_QUOTES, 'UTF-8');

                                $kepQuery = "SELECT k.kepNev 
                                                 FROM Tartalmaz t 
                                                 INNER JOIN Kep k ON t.kepID = k.kepID 
                                                 WHERE t.aID = :albumID 
                                                 FETCH FIRST 1 ROWS ONLY";
                                $kepStmt = oci_parse($conn, $kepQuery);
                                oci_bind_by_name($kepStmt, ":albumID", $albumID);
                                oci_execute($kepStmt);

                                $kepFile = "resources/APP_IMGS/placeholder.png";
                                if ($kepRow = oci_fetch_assoc($kepStmt)) {
                                    $dir = "resources/APP_IMGS";
                                    $files = scandir($dir);
                                    foreach ($files as $file) {
                                        if (fnmatch($kepRow['KEPNEV'] . ".*", $file)) {
                                            $kepFile = $dir . "/" . $file;
                                            break;
                                        }
                                    }
                                }

                                echo '<div class="album-del-option" onclick="toggleAlbumSelection(this)" data-albumid="' . $row['AID'] . '">';
                                echo '<img src="' . $kepFile . '" alt="' . $albumNev . '">';
                                echo '<div class="album-del-name">' . htmlspecialchars($row['ALBUMNEV'], ENT_QUOTES, 'UTF-8') . '</div>';
                                echo '</div>';
                            }
                            ?>
                        </div>

                        <input type="hidden" id="selectedAlbums" name="selectedAlbums">
                        <button type="submit" name="deleteAlbums">Törlés</button>
                    </form>
                </div>
            </div>

        </div>
    <?php endif; ?>

        <div class="content">
            <div class="title">
                <h2>Albumok</h2>
            </div>
            <div class="gallery">
                <?php
                $query = "SELECT a.aID, a.albumNev
              FROM Album a
              WHERE a.fID = :fID
              ORDER BY a.albumNev";
                $stmt = oci_parse($conn, $query);
                oci_bind_by_name($stmt, ":fID", $fID, SQLT_INT);
                oci_execute($stmt);

                while ($row = oci_fetch_assoc($stmt)):
                    $albumID = $row['AID'];
                    $albumNev = htmlspecialchars($row['ALBUMNEV'], ENT_QUOTES, 'UTF-8');

                    $kepQuery = "SELECT k.kepNev 
                     FROM Tartalmaz t 
                     INNER JOIN Kep k ON t.kepID = k.kepID 
                     WHERE t.aID = :albumID 
                     FETCH FIRST 1 ROWS ONLY";
                    $kepStmt = oci_parse($conn, $kepQuery);
                    oci_bind_by_name($kepStmt, ":albumID", $albumID);
                    oci_execute($kepStmt);

                    $kepFile = "resources/APP_IMGS/placeholder.png";
                    if ($kepRow = oci_fetch_assoc($kepStmt)) {
                        $dir = "resources/APP_IMGS";
                        $files = scandir($dir);
                        foreach ($files as $file) {
                            if (fnmatch($kepRow['KEPNEV'] . ".*", $file)) {
                                $kepFile = $dir . "/" . $file;
                                break;
                            }
                        }
                    }
                    oci_free_statement($kepStmt);
                    ?>

                    <a href="album.php?album=<?php echo $albumID; ?>" class="image">
                        <img src="<?php echo $kepFile; ?>" alt="<?php echo $albumNev; ?>">
                        <div class="imageInfo">
                            <h3><?php echo $albumNev; ?></h3>
                        </div>
                    </a>

                <?php endwhile; ?>
            </div>

            <?php endif; ?>
            <div class="title">
                <h2>
                    Képek
                </h2>
            </div>
            <div class="gallery">
                <?php
                $query = "SELECT 
                                kepID, kepNev, ertekeles
                            FROM 
                                Kep
                            WHERE 
                                fID = :fID";
                $stmt = oci_parse($conn, $query);
                oci_bind_by_name($stmt, ":fID", $fID);
                oci_execute($stmt);
                while ($row = oci_fetch_assoc($stmt)): ?>
                    <?php
                    $kepPath = "resources/APP_IMGS";
                    $kepFile = "resources/APP_IMGS/placeholder.png";

                    $files = scandir($kepPath);
                    foreach ($files as $file) {
                        if (fnmatch($row['KEPNEV'] . ".*", $file)) {
                            $kepFile = $kepPath . "/" . $file;
                            break;
                        }
                    }
                    ?>
                    <a href="picture.php?id=<?php echo $row['KEPID']; ?>" class="image">
                        <img src="<?php echo $kepFile; ?>"
                             alt="<?php echo htmlspecialchars($row['KEPNEV'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="imageInfo">
                            <h3><?php echo htmlspecialchars($row['KEPNEV'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p>👍 <?php echo $row['ERTEKELES']; ?></p>
                        </div>
                    </a>
                <?php endwhile; ?>

            </div>
        </div>
        <?php if ($_SESSION['fID'] == $fID): ?>

        <?php endif; ?>

    </main>

    </body>
    </html>
<?php
oci_free_statement($stmt);
oci_close($conn);
?>