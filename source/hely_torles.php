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

    $_SESSION['success_message'] = "Kiválasztott város(ok) sikeresen törölve.";
    header("Location: hely_torles.php");
    exit();
}

$helyek = [];
$query = "SELECT helyID, varos, megye, orszag FROM Hely ORDER BY orszag, megye, varos";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
while ($row = oci_fetch_assoc($stmt)) {
    $helyek[] = $row;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Városok törlése</title>

    <link rel="stylesheet" href="resources/CSS/hely_es_kategoria_torles.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<?php if (isset($_SESSION['success_message'])): ?>
    <script>
        alert("<?= addslashes($_SESSION['success_message']) ?>");
    </script>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<div class="place-delete-container">
    <form method="POST">
        <table class="place-table">
            <thead>
            <tr>
                <th>Törlés</th>
                <th>Város</th>
                <th>Megye</th>
                <th>Ország</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($helyek as $hely): ?>
                <tr class="clickable-row" data-checkbox-id="hely<?= $hely['HELYID'] ?>">
                    <td><input type="checkbox" id="hely<?= $hely['HELYID'] ?>" name="torlendo_helyek[]" value="<?= $hely['HELYID'] ?>"></td>
                    <td><?= htmlspecialchars($hely['VAROS']) ?></td>
                    <td><?= htmlspecialchars($hely['MEGYE']) ?></td>
                    <td><?= htmlspecialchars($hely['ORSZAG']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="delete-btn">Kiválasztott városok törlése</button>
    </form>
</div>

<script>
    document.querySelectorAll('.clickable-row').forEach(row => {
        row.addEventListener('click', function(e) {
            // Ha nem input elemre kattintottunk, toggle-oljuk a checkboxot
            if (e.target.tagName.toLowerCase() !== 'input') {
                const checkboxId = this.dataset.checkboxId;
                const checkbox = document.getElementById(checkboxId);
                checkbox.checked = !checkbox.checked;
            }
        });
    });
</script>

</body>
</html>

<?php
oci_free_statement($stmt);
oci_close($conn);
?>
