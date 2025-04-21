<?php
    session_start();
    include('../../web_lara/db_connection.php');
    if(!isset($_SESSION["fID"])){
        $_SESSION["fID"] = -1;
    }

    $fID = substr(explode('?',$_SERVER['REQUEST_URI'])[1],3);
    $stmt = oci_parse($conn, "SELECT * FROM Kep WHERE fID = :fID");
    oci_bind_by_name($stmt, ":fID", $fID);
    if (oci_execute($stmt)) {
        if(!$row = oci_fetch_assoc($stmt)){
            //Ha nincs ilyen idjű felhasználó redirectel
            header("Location: index.php");
        }
    } else {
        $e = oci_error($stmt);
        die("Database Error: " . $e['message']);
    }


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fIDUpload = $_SESSION['fID'];
        $kepNev = $_POST['name'];
        $hely = NULL;
        if(!empty($_POST['country']) && !empty($_POST['county']) && !empty($_POST['city'])){
            //ékezetessel nem boldog, túl sok idő alatt nem tudtam megoldani
            $cleanCity= str_replace(['á','é','í','ó','ö','ő','ú','ü','ű','Á','É','Í','Ó','Ö','Ő','Ú','Ü','Ű'],
                                    ['a','e','i','o','o','o','u','u','u','A','E','I','O','O','O','U','U','U'],$_POST['city']);
            $cleanCounty= str_replace(['á','é','í','ó','ö','ő','ú','ü','ű','Á','É','Í','Ó','Ö','Ő','Ú','Ü','Ű'],
                                    ['a','e','i','o','o','o','u','u','u','A','E','I','O','O','O','U','U','U'],$_POST['county']);
            $cleanCountry = str_replace(['á','é','í','ó','ö','ő','ú','ü','ű','Á','É','Í','Ó','Ö','Ő','Ú','Ü','Ű'],
                                        ['a','e','i','o','o','o','u','u','u','A','E','I','O','O','O','U','U','U'],$_POST['country']);
            $stmt = oci_parse($conn, "SELECT helyID FROM Hely WHERE varos = :city AND megye = :county AND orszag = :country"); 
            oci_bind_by_name($stmt, ":city", $cleanCity); 
            oci_bind_by_name($stmt, ":county", $cleanCounty); 
            oci_bind_by_name($stmt, ":country", $cleanCountry); 
            if (oci_execute($stmt)) {
                if($row = oci_fetch_assoc($stmt)){
                    $hely =$row["HELYID"];
                }else{
                    oci_free_statement($stmt);
                    $stmt = oci_parse($conn, "INSERT INTO Hely (helyID, orszag, megye, varos)
                          VALUES (hely_seq.NEXTVAL, :country, :county, :city)
                          RETURNING helyID INTO :helyID"); 
                    oci_bind_by_name($stmt, ":city", $cleanCity); 
                    oci_bind_by_name($stmt, ":county", $cleanCounty); 
                    oci_bind_by_name($stmt, ":country", $cleanCountry); 
                    oci_bind_by_name($stmt, ":helyID", $hely, SQLT_INT);
                    oci_execute($stmt);
                }
                oci_free_statement($stmt);
            }
        }
        $kepID = 0;
        if (!empty($kepNev) && !empty($fIDUpload)) {
            $cleanNev= str_replace(['á','é','í','ó','ö','ő','ú','ü','ű','Á','É','Í','Ó','Ö','Ő','Ú','Ü','Ű'],
                                    ['a','e','i','o','o','o','u','u','u','A','E','I','O','O','O','U','U','U'],$kepNev);
            $stmt = oci_parse($conn, "INSERT INTO Kep (kepID, kepNev, fID, helyID)
                                      VALUES (kep_seq.NEXTVAL, :kepNev, :fID, :helyID)
                                      RETURNING kepID INTO :kepID");
            oci_bind_by_name($stmt, ":kepNev", $cleanNev);
            oci_bind_by_name($stmt, ":fID", $fIDUpload);
            oci_bind_by_name($stmt, ":helyID", $hely);
            oci_bind_by_name($stmt, ":kepID", $kepID, SQLT_INT);
            if (!oci_execute($stmt)) {
                $e = oci_error($stmt);
                die("Database Error: " . $e['message']);
            }
            
            if (isset($_FILES["uploadedFile"]) && $_FILES["uploadedFile"]["error"] == 0) {
                $uploadDir = "../PICS/";
                $fileExt = strtolower(pathinfo($_FILES["uploadedFile"]["name"], PATHINFO_EXTENSION));
                $uploadFile = $uploadDir . $cleanNev . "." . $fileExt;
                $check = getimagesize($_FILES["uploadedFile"]["tmp_name"]);
                if (!$check) {
                    die("File is not a valid image.");
                }
                if (!move_uploaded_file($_FILES["uploadedFile"]["tmp_name"], $uploadFile)) {
                    echo "The file ". htmlspecialchars( basename( $_FILES["uploadedFile"]["name"])). " failed to upload.";
                }
            }
        }
        
        
        if(!empty($_POST['categories'])){
            $kategoria = explode(' ',trim($_POST['categories']));
            $katRes = [];
            foreach ($kategoria as $kat) {
                $clean = str_replace(['á','é','í','ó','ö','ő','ú','ü','ű','Á','É','Í','Ó','Ö','Ő','Ú','Ü','Ű'],
                                        ['a','e','i','o','o','o','u','u','u','A','E','I','O','O','O','U','U','U'],$kat);
                $stmt = oci_parse($conn, "SELECT katID FROM Kategoria WHERE kategoriaNev = :nev"); 
                oci_bind_by_name($stmt, ":nev", $clean); 
                if (oci_execute($stmt)) {
                    if($row = oci_fetch_assoc($stmt)){
                        array_push($katRes,$row["KATID"]);
                    }else{
                        $id = 0;
                        oci_free_statement($stmt);
                        $stmt = oci_parse($conn, "INSERT INTO Kategoria (katID, kategorianev)
                              VALUES (kat_seq.NEXTVAL, :nev)
                              RETURNING katID INTO :katID"); 
                        oci_bind_by_name($stmt, ":nev", $clean); 
                        oci_bind_by_name($stmt, ":katID", $id);
                        oci_execute($stmt);
                        array_push($katRes,$id);
                    }
                }
                oci_free_statement($stmt);
            }
            foreach ($katRes as $kat) {
                $stmt = oci_parse($conn, "INSERT INTO KategoriaResze (katID, kepID)
                              VALUES (:katID, :kepID)"); 
                oci_bind_by_name($stmt, ":kepID", $kepID); 
                oci_bind_by_name($stmt, ":katID", $kat);
                oci_execute($stmt);
                oci_free_statement($stmt);
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link rel="stylesheet" href="../CSS/styles.css">
</head>
<body>
    <header>
        <div class="menu">
            <?php if(isset($_SESSION['fID'])): ?>
                <a href="profile.php?id=<?php echo $_SESSION['fID'];?>" id="profileButton"><button class="interact">Profil</button></a>
                <a href="logout.php" id="logoutButton"><button class="interact">Kijelentkezés</button></a>
            <?php else:?>    
                <a href="../../web_lara/login.php" id="loginButton"><button class="interact">Bejelentkezés</button></a>
            <?php endif; ?>
        </div>
    </header>
    <main>
        <h1 class="title">
            <?php
                $stmt = oci_parse($conn, "SELECT fNev FROM Felhasznalo WHERE fID = :fID");
                oci_bind_by_name($stmt, ":fID", $fID);
                if (oci_execute($stmt)) {
                    $row = oci_fetch_assoc($stmt);
                    echo $row["FNEV"];
                } else {
                    $e = oci_error($stmt);
                    die("Database Error: " . $e['message']);
                }
            ?>
        </h1>
        <?php if($_SESSION['fID'] == $fID): ?>
            <div class="topArea">
                <button onclick="openPopup()">Kép feltöltése</button>
                <div id="uploadPopup" class="popup">
                    <div class="popup-content">
                        <span onclick="closePopup()" class="close">&times;</span>
                        <form method="POST" enctype="multipart/form-data">
                        <link rel="stylesheet" href="../CSS/upload.css">
                            <div class="formHead">
                                <h2>Fénykép feltöltése</h2>
                            </div>

                            <div class="uploadForm">

                                <div class="drop-area">
                                    <label for="fileInput">Válassza ki a feltöltendő képet:</label>
                                    <input type="file" id="fileInput" name="uploadedFile" accept="image/*" require>
                                </div>
                                <div class="formElement">
                                    <label for="nameInput">Név:</label>
                                    <input id="nameInput" name="name" require>
                                    <label for="place">Hely:</label>
                                    <div id="place">
                                        <input id="country" name="country" placeholder="Ország">
                                        <input id="county" name="county" placeholder="Megye">
                                        <input id="city" name="city" placeholder="Város">
                                    </div>
                                    <label for="categoryInput">Kategória:</label>
                                    <input list="categories" id="categoryInput" name="categories" require>
                                    <button type="submit">Feltöltés</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <a href="albumCreate.php" id="newAlbum"><button class="interact">Új album</button></a>
            </div>
        <?php endif; ?>
        
        <div class="content">
            <div class="title">
                <h2>
                Albumok
                </h2>
            </div>
            <div class="gallery">
                <?php 
                    $query = "SELECT a.aID, a.albumNev, SUM(k.ertekeles) AS pont
                            FROM 
                                Album a
                            INNER JOIN 
                                Tartalmaz t ON a.aID = t.aID
                            INNER JOIN 
                                Kep k ON t.kepID = k.kepID
                            WHERE 
                                a.fID = :fID
                            GROUP BY 
                                a.aID, a.albumNev";
                    $stmt = oci_parse($conn, $query);
                    oci_bind_by_name($stmt, ":fID", $fID, SQLT_INT);
                    oci_execute($stmt);
                    while ($row = oci_fetch_assoc($stmt)): ?>
                        <a href="album.php?id=<?php echo $row['AID'];?>" class="image">
                            <img src="" alt="<?php echo $row['ALBUMNEV'];?>">
                            <div class="imageInfo">
                                <h3><?php echo $row['ALBUMNEV'];?></h3>
                                <!--TODO: fix-->
                                <!--Az összesített pontok valamiért 2x akkorák mint kéne-->
                                <p>pont: <?php echo $row['PONT']/2;?></p>
                            </div>
                        </a>
                <?php endwhile;?>
                
            </div>
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
                        <a href="picture.php?id=<?php echo $row['KEPID'];?>" class="image">
                            <img src="<?php
                                        $dir = "../PICS";
                                        $files = scandir($dir);
                                        foreach ($files as $file) {
                                            if (fnmatch($row['KEPNEV'].".*", $file)) {
                                                echo "../PICS/".$file;
                                            } else {
                                                echo "";
                                            }
                                        }
                                        ?>" 
                                        alt="<?php echo $row['KEPNEV'];?>">
                            <div class="imageInfo">
                                <h3><?php echo $row['KEPNEV'];?></h3>
                                <p>pont: <?php echo $row['ERTEKELES'];?></p>
                            </div>
                        </a>
                <?php endwhile;?>
            </div>
        </div>
        <?php if($_SESSION['fID'] == $fID): ?>
            <div class="accountControls">
                <a href="modifyProfile.php" id="profileButton"><button class="interact">Módosítás</button></a>
            </div>
        <?php endif; ?>
        
    </main>
    <script src="../JS/popup.js"></script>

</body>
</html>
<?php
oci_free_statement($stmt);
oci_close($conn);
?>