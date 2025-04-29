<?php
    // régi
    session_start();
    include('resources/SUPPORT_FUNCS/db_connection.php');
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fIDUpload = $_SESSION['fID'];
        $kepNev = $_POST['name'];
        $kategoria = explode(' ',$_POST['categories']);
        $hely = $_POST['country'] .'--'.$_POST['county'].'--'.$_POST['city'];
        var_dump($kepNev,$fIDUpload,$kategoria);
        if (!empty($kepNev) && !empty($fIDUpload) && !empty($kategoria)) {
            $stmt = oci_parse($conn, "INSERT INTO Kep (kepID, kepNev, fID)
                                      VALUES (21, :kepNev , 1");
            oci_bind_by_name($stmt, ":kepNev", $kepNev);
            //oci_bind_by_name($stmt, ":fID", $fIDUpload);
            if (oci_execute($stmt)) {
                $_SESSION['success_message'] = "Upload successful!";
            } else {
                $e = oci_error($stmt);
                die("Database Error: " . $e['message']);
            }
            oci_free_statement($stmt);
        }
        oci_close($conn);
    }
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload</title>
    <link rel="icon" href="resources/images/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="resources/CSS/styles.css">
    <link rel="stylesheet" href="resources/CSS/upload.css">
</head>
<body>
    <header>
        <div class="menu">
        <?php if(isset($_SESSION['fID'])): ?>
                <a href="profile.php?id=<?php echo $_SESSION['fID'];?>" id="profileButton"><button class="interact">Profil</button></a>
                <a href="logout.php" id="logoutButton"><button class="interact">Kijelentkezés</button></a>
            <?php else:?>    
                <a href="login.php" id="loginButton"><button class="interact">Bejelentkezés</button></a>
            <?php endif; ?>
        </div>
    </header>
    <main>
        <form method="POST">
            <div class="formHead">
                <h2>Fénykép feltöltése</h2>
            </div>
            
            <div class="uploadForm">

                <div class="formElement">
                    <label for="fileInput">Válassza ki a feltöltendő képet:</label>
                    <input type="file" id="fileInput" name="uploadedFile" accept="image/*">
                </div>
                <div class="formElement">
                    <label for="nameInput">Név:</label>
                    <input id="nameInput" name="name">
                    <label for="place">Hely:</label>
                    <div>

                        <input id="country" name="country">
                        <input id="county" name="county">
                        <input id="city" name="city">
                    </div>
                    <label for="categoryInput">Kategória:</label>
                    <input list="categories" id="categoryInput" name="categories">
                    <datalist id="categories">
                        <option value="kat1">
                        <option value="kat2">
                        <option value="kat3">
                    </datalist>
                    <button type="submit">Feltöltés</button>
                </div>
            </div>
        </form>
    </main>
</body>
</html>