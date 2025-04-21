<?php
    session_start();
    include('../../web_lara/db_connection.php');
    
    if(!isset($_SESSION["fID"])){
        header("Location: ../../web_lara/login.php");
    }

    $stmt = oci_parse($conn, "SELECT fNev, email, jelszo FROM Felhasznalo WHERE fID = :fID"); 
    oci_bind_by_name($stmt, ":fID", $_SESSION["fID"]); 
    $row = [];
    if (oci_execute($stmt)) {
        $row = oci_fetch_assoc($stmt);
    }else {
        $e = oci_error($stmt);
        header("Location: ../../web_lara/login.php?id=".$_SESSION["fID"]);
    }
    $hiba = "";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST["email"];
        $uname = $_POST["name"];
        $pwd = $_POST["password"];
        $pwd2 = $_POST["password2"];
        if(strlen($pwd)<6){
            $hiba = "A jelszó túl rövid.";
            
        } else{
            if($pwd === $pwd2){
                $pwdHash = password_hash($pwd, PASSWORD_DEFAULT);
                if($email !== $row["EMAIL"] || $uname !== $row["FNEV"] || $pwdHash !== $row["JELSZO"]){
                    $stmt = oci_parse($conn, "UPDATE Felhasznalo SET fNev = :fNev, email = :email, jelszo = :pwd WHERE fID = :fID"); 
                    oci_bind_by_name($stmt, ":fID", $_SESSION["fID"]); 
                    oci_bind_by_name($stmt, ":fNev", $uname); 
                    oci_bind_by_name($stmt, ":email", $email); 
                    oci_bind_by_name($stmt, ":pwd", $pwdHash); 
                    if (!oci_execute($stmt)) {
                        $e = oci_error($stmt);
                        echo "Query failed: " . $e['message'];
                        die();
                    }
                }
            } else{
                $hiba = "A jelszavak nem egyeznek";
            }
            if(isset($_POST["deleteConf"]) && isset($_POST["delete"])){
                
                $stmt = oci_parse($conn, "SELECT kepNev FROM Kep WHERE fID = :fID"); 
                    oci_bind_by_name($stmt, ":fID", $_SESSION["fID"]); 
                    if (oci_execute($stmt)) {
                        $files = scandir("../PICS/");
                        $rows = [];
                        while ($row = oci_fetch_assoc($stmt)) {
                            $rows[] = $row;
                        }
                        foreach ($rows as $row) {
                            
                            foreach ($files as $file) {
                                if (fnmatch($row['KEPNEV'].".*", $file)) {
                                    if (!unlink("../PICS/".$file)) {
                                        echo "Error deleting: $file<br>";
                                    }
                                }
                            }
                        }
                    }

                
                
                $stmt = oci_parse($conn, "DELETE FROM Felhasznalo WHERE fID = :fID"); 
                    oci_bind_by_name($stmt, ":fID", $_SESSION["fID"]); 
                    if (!oci_execute($stmt)) {
                        $e = oci_error($stmt);
                        echo "Query failed: " . $e['message'];
                        die();
                    }
                    header("Location: ../../web_lara/login.php");
                
            }
        }
        

    }
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil módosítása</title>
    <link rel="icon" href="../images/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../CSS/styles.css">
    <link rel="stylesheet" href="../CSS/upload.css">
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
        <form method="POST">
            <div class="formHead">
                <h2>Felhasználói aadatok módosítása</h2>
            </div>
            
            <div class="uploadForm">
                <div class="formElement">
                    <label for="name">Felhasználónév:</label>
                    <input id="name" name="name" value="<?php echo $row["FNEV"]?>" placeholder="Felhasználónév" require>
                    <label for="email">Email cím:</label>
                    <input id="email" name="email" type="email" value="<?php echo $row["EMAIL"]?>" placeholder="Email cím" require>
                    <label for="password">Jelszó:</label>
                    <input id="password" name="password" type="password" placeholder="Jelszó" require>
                    <label for="password2">Jelszó mégegyszer:</label>
                    <input id="password2" name="password2" type="password" placeholder="Jelszó mégegyszer" require>
                    <?php if(!empty($hiba)) echo "<p style='color:red;'>$hiba</p>";?>
                    <button type="submit">Módosítás</button>
                    <div>
                        <input id="deleteConf" name="deleteConf" type="checkbox">
                        <label for="deleteConf">Véglegesen törölni szeretném a fiókom.</label>
                    </div>
                    <button type="submit" name="delete" id="delete">Törlés</button>
                </div>
            </div>
        </form>
    </main>
</body>
</html>
<?php
oci_free_statement($stmt);
oci_close($conn);
?>