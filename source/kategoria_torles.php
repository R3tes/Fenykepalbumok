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

    $_SESSION['success_message'] = "Kiválasztott kategóriák sikeresen törölve.";
    header("Location: kategoria_torles.php");
    exit();
}

$kategoriak = [];
$query = "SELECT katID, kategoriaNev FROM Kategoria ORDER BY kategoriaNev";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
while ($row = oci_fetch_assoc($stmt)) {
    $kategoriak[] = $row;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Kategóriák törlése</title>
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
    <h2>Kategória törlése</h2>
    <form method="POST">
        <table class="place-table">
            <thead>
            <tr>
                <th>Törlés</th>
                <th>Kategória neve</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($kategoriak as $kat): ?>
                <tr class="clickable-row" data-checkbox-id="kat<?= $kat['KATID'] ?>">
                    <td>
                        <input type="checkbox" id="kat<?= $kat['KATID'] ?>" name="torlendo_kategoriak[]" value="<?= $kat['KATID'] ?>">
                    </td>
                    <td>
                        <label for="kat<?= $kat['KATID'] ?>"><?= htmlspecialchars($kat['KATEGORIANEV']) ?></label>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
        <button type="submit" class="delete-btn">Kiválasztott kategóriák törlése</button>
    </form>
</div>
</body>
</html>

<script>
    document.querySelectorAll('.clickable-row').forEach(row => {
        row.addEventListener('click', function (e) {

            if (e.target.tagName.toLowerCase() !== 'input') {
                const checkboxId = this.dataset.checkboxId;
                const checkbox = document.getElementById(checkboxId);
                checkbox.checked = !checkbox.checked;
            }
        });
    });
</script>

<?php
oci_free_statement($stmt);
oci_close($conn);
?>
