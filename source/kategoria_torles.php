<?php
session_start();
require_once 'resources/SUPPORT_FUNCS/db_connection.php';

if (!isset($_SESSION['fID']) || $_SESSION['jogosultsag'] != 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['torlendo_kategoriak'])) {
    $torlendoKategoriak = $_POST['torlendo_kategoriak'];
    foreach ($torlendoKategoriak as $katID) {
        $stmt = oci_parse($conn, "DELETE FROM Kategoria WHERE katID = :katID");
        oci_bind_by_name($stmt, ":katID", $katID);
        oci_execute($stmt);
        oci_free_statement($stmt);
    }
    echo "<p style='color: green;'>Kiválasztott kategória(k) sikeresen törölve.</p>";
}

$query = "SELECT katID, kategoriaNev FROM Kategoria ORDER BY kategoriaNev";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kategóriák törlése</title>
</head>
<body>
<?php include 'navbar.php'; ?>

<style>
    body{
        font-family: 'Arial', sans-serif;
    }
</style>

<div class="tables">
    <form method="post" action="kategoria_torles.php">
        <table class="statCategory">
            <thead>
            <tr>
                <th>Törlés</th>
                <th>Kategória név</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = oci_fetch_assoc($stmt)): ?>
                <tr>
                    <td><input type="checkbox" name="torlendo_kategoriak[]" value="<?= htmlspecialchars($row['KATID']) ?>"></td>
                    <td><?= htmlspecialchars($row['KATEGORIANEV']) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <br>
        <div style="text-align: center;">
            <input type="submit" value="Kiválasztott kategóriák törlése">
        </div>
    </form>
</div>
</body>
</html>

<?php
oci_free_statement($stmt);
oci_close($conn);
?>
