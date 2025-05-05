<?php
session_start();
require_once 'resources/SUPPORT_FUNCS/db_connection.php';

if (!isset($_SESSION['fID']) || $_SESSION['jogosultsag'] != 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['torlendo_helyek'])) {
    $torlendoHelyek = $_POST['torlendo_helyek'];
    foreach ($torlendoHelyek as $helyID) {
        $stmt = oci_parse($conn, "DELETE FROM Hely WHERE helyID = :helyID");
        oci_bind_by_name($stmt, ":helyID", $helyID);
        oci_execute($stmt);
        oci_free_statement($stmt);
    }
    echo "<p style='color: green;'>Kiválasztott város(ok) sikeresen törölve.</p>";
}

$query = "SELECT helyID, varos, megye, orszag FROM Hely ORDER BY orszag, megye, varos";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Városok törlése</title>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="tables">
    <form method="post" action="hely_torles.php">
        <table class="statCategory">
            <thead>
            <tr>
                <th>Törlés</th>
                <th>Város</th>
                <th>Megye</th>
                <th>Ország</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = oci_fetch_assoc($stmt)): ?>
                <tr>
                    <td><input type="checkbox" name="torlendo_helyek[]" value="<?= htmlspecialchars($row['HELYID']) ?>"></td>
                    <td><?= htmlspecialchars($row['VAROS']) ?></td>
                    <td><?= htmlspecialchars($row['MEGYE']) ?></td>
                    <td><?= htmlspecialchars($row['ORSZAG']) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <br>
        <div style="text-align: center;">
            <input type="submit" value="Kiválasztott városok törlése">
        </div>
    </form>
</div>
</body>
</html>

<?php
oci_free_statement($stmt);
oci_close($conn);
?>
