<?php
include('resources/SUPPORT_FUNCS/db_connection.php');
session_start();
if (!$_SESSION['is_admin']) {
    header("Location: palyazatok.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $palyazatNev = $_POST['palyazatNev'];

    $query = "INSERT INTO Palyazat (pID, palyazatNev) VALUES (palyazat_seq.NEXTVAL, :pnev)";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':pnev', $palyazatNev);

    if (oci_execute($stmt)) {

        $_SESSION['success_message'] = "Új pályázat sikeresen létrehozva.";
        header("Location: palyazatok.php");
        exit();
    } else {
        $error = "Hiba történt a mentés során.";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Új pályázat</title>
    <link rel="stylesheet" href="resources/CSS/style.css">
</head>
<body>
<form method="POST">
    <h2>Új pályázat létrehozása</h2>

    <label for="palyazatNev">Pályázat neve:</label>
    <input type="text" name="palyazatNev" required>

    <input type="submit" value="Mentés">

    <?php if (isset($error)) echo "<p>$error</p>"; ?>

    <div class="menu">
        <a href="palyazatok.php">Vissza</a>
    </div>
</form>
</body>
</html>

<?php
oci_close($conn);
?>
