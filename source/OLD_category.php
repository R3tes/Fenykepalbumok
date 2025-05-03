<?php
    include('resources/SUPPORT_FUNCS/db_connection.php');

    $katID = substr(explode('?',$_SERVER['REQUEST_URI'])[1],3);
    $query = "SELECT kategoriaNev
            FROM 
                Kategoria 
            WHERE 
                katID = :katID";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ":katID", $katID);
    oci_execute($stmt);
    $katNev = "";
    if (oci_execute($stmt)) {
        if($row = oci_fetch_assoc($stmt)){
            $katNev = $row['KATEGORIANEV'];
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
    <title><?php echo $katNev;?></title>
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
                <?php echo $katNev;?>
                </h2>
            </div>
            <div class="gallery">
            <?php 
                    $query = "SELECT k.kepID, k.kepNev, k.ertekeles
                            FROM 
                                KategoriaResze kr
                            INNER JOIN 
                                Kep k ON kr.kepID = k.kepID
                            WHERE 
                                kr.katID = :katID
                            GROUP BY 
                                k.kepID, k.kepNev, k.ertekeles";
                    $stmt = oci_parse($conn, $query);
                    oci_bind_by_name($stmt, ":katID", $katID);
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
