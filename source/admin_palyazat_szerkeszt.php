<?php
include('resources/SUPPORT_FUNCS/db_connection.php');
session_start();
if (!$_SESSION['is_admin']) {
    header("Location: palyazatok.php");
    exit();
}

$pID = $_GET['id'] ?? null;

if (!$pID) {
    header("Location: palyazatok.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $palyazatNev = $_POST['palyazatNev'];
    $query = "UPDATE Palyazat SET palyazatNev = :pnev WHERE pID = :pid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':pnev', $palyazatNev);
    oci_bind_by_name($stmt, ':pid', $pID);

    if (oci_execute($stmt)) {
        header("Location: palyazatok.php");
        exit();
    } else {
        $error = "Hiba történt a frissítés során.";
    }
} else {
    $query = "SELECT palyazatNev FROM Palyazat WHERE pID = :pid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':pid', $pID);
    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);
    $palyazatNev = $row['PALYAZATNEV'];
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Pályázat szerkesztése</title>
    <link rel="stylesheet" href="resources/CSS/style.css">
</head>
<body>
<form method="POST">
    <h2>Pályázat szerkesztése</h2>

    <label for="palyazatNev">Pályázat neve:</label>
    <input type="text" name="palyazatNev" value="<?php echo htmlspecialchars($palyazatNev); ?>" required>

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
