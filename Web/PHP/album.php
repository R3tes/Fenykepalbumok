<?php
    include('../../web_lara/db_connection.php');

    $aID = substr(explode('?',$_SERVER['REQUEST_URI'])[1],3);
    $query = "SELECT a.albumNev
            FROM 
                Album a
            WHERE 
                a.aID = :aID
            GROUP BY 
                a.albumNev";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ":aID", $aID);
    oci_execute($stmt);
    $aNev = "";
    if (oci_execute($stmt)) {
        if($row = oci_fetch_assoc($stmt)){
            $aNev = $row['ALBUMNEV'];
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
    <title><?php echo $aNev;?></title>
    <link rel="stylesheet" href="../CSS/styles.css">
</head>
<body>
    <header>
        <div class="menu">
            <a href="../../web_lara/login.php" id="loginButton"><button class="Button">Bejelentkezés</button></a>
            <a href="profile.php" id="profileButton"><button class="Button">Profil</button></a>
        </div>
    </header>
    <main>
        <div>
            <div class="title">
                <h2>
                <?php echo $aNev;?>
                </h2>
            </div>
            
            <div class="gallery">
                <?php 
                    $query = "SELECT k.kepID, k.kepNev, k.ertekeles
                            FROM 
                                Tartalmaz t
                            INNER JOIN 
                                Kep k ON t.kepID = k.kepID
                            WHERE 
                                t.aID = :aID
                            GROUP BY 
                                k.kepID, k.kepNev, k.ertekeles";
                    $stmt = oci_parse($conn, $query);
                    oci_bind_by_name($stmt, ":aID", $aID);
                    oci_execute($stmt);
                    while ($row = oci_fetch_assoc($stmt)) {
                        echo '
                            <a href="picture.php?id='.$row['KEPID'].'" class="image">
                                <img src="" alt='.$row['KEPNEV'].'>
                                <div class="imageInfo">
                                    <h3>'.$row['KEPNEV'].'</h3>
                                    <p>pont: '.$row['ERTEKELES'].'</p>
                                </div>
                            </a>
                        ';
                    }
                ?>
            </div>
        </div>
    </main>
</body>
</html>
<?php
oci_free_statement($stmt);
oci_close($conn);
?>