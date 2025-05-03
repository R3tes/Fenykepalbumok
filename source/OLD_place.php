<?php
    include('resources/SUPPORT_FUNCS/db_connection.php');

    $helyID = substr(explode('?',$_SERVER['REQUEST_URI'])[1],3);
    $query = "SELECT orszag,varos,megye
            FROM 
                Hely 
            WHERE 
                helyID = :helyID";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ":helyID", $helyID);
    oci_execute($stmt);
    $helyNev = "";
    if (oci_execute($stmt)) {
        if($row = oci_fetch_assoc($stmt)){
            $helyNev = $row['ORSZAG'].", ".$row['MEGYE'].", ".$row['VAROS'];
        }
        
    } else {
        $e = oci_error($stmt);
        echo "Query failed: " . $e['message'];
    }
    oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $helyNev;?></title>
    <link rel="stylesheet" href="resources/CSS/styles.css">
</head>
<body>
    <header>
        <div class="menu">
            <a href="login.php" id="loginButton"><button class="Button">Bejelentkezés</button></a>
            <a href="profile.php" id="profileButton"><button class="Button">Profil</button></a>
        </div>
    </header>
    <main>
        <div>
            <div class="title">
                <h2>
                <?php echo $helyNev;?>
                </h2>
                <p style="text-align: center;">
                    <?php echo '(képek: '.$numberOfPics.' db, összesített pontok: '.$points.' )';?>
                </p>
            </div>
            <div class="gallery">
            <?php 
                    $query = "SELECT k.kepID, k.kepNev, k.ertekeles
                            FROM 
                                Hely h
                            INNER JOIN 
                                Kep k ON h.helyID = k.helyID
                            WHERE 
                                h.helyID = :helyID";
                    $stmt = oci_parse($conn, $query);
                    oci_bind_by_name($stmt, ":helyID", $helyID);
                    oci_execute($stmt);
                    while ($row = oci_fetch_assoc($stmt)): ?>
                        <a href="picture.php?id=<?php echo $row['KEPID'];?>" class="image">
                            <img src="<?php
                                        $dir = "resources/APP_IMGS/PICS";
                                        $files = scandir($dir);
                                        foreach ($files as $file) {
                                            if (fnmatch($row['KEPNEV'].".*", $file)) {
                                                echo "resources/APP_IMGS/".$file;
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
    </main>
</body>
</html>
