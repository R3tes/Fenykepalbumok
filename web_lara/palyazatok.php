<?php
include('db_connection.php');

session_start();
$is_admin = $_SESSION['is_admin'];

if (isset($_SESSION['login_success'])) {
    echo "<p style='color:green; text-align:center;'>" . $_SESSION['login_success'] . "</p>";
    unset($_SESSION['login_success']);
}

$query = "SELECT p.pID, p.palyazatNev
          FROM Palyazat p
          ORDER BY p.pID DESC";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pályázatok</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Pályázatok</h1>

    <?php if ($is_admin): ?>
        <div class="admin-actions">
            <a href="uj_palyazat.php" class="btn">Új pályázat létrehozása</a>
        </div>
    <?php endif; ?>

    <table class="palyazatok-lista">
        <thead>
        <tr>
            <th>Pályázat neve</th>
            <th>Jelentkezés</th>
            <?php if ($is_admin): ?>
                <th>Admin műveletek</th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = oci_fetch_assoc($stmt)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['PALYAZATNEV']); ?></td>
                <td>
                    <?php if (!$is_admin): ?>
                        <!--TODO: itt is lehet hasznalnia kep feltoltes oldalt ha kesz-->
                        <a href="kep_feltoltes.php?id=<?php echo $row['PID']; ?>" class="btn">Jelentkezés</a>
                    <?php endif; ?>
                </td>
                <?php if ($is_admin): ?>
                    <td>
                        <a href="admin_palyazat_szerkeszt.php?id=<?php echo $row['PID']; ?>" class="btn">Szerkesztés</a>
                        <a href="admin_palyazat_torles.php?id=<?php echo $row['PID']; ?>" class="btn">Törlés</a>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>

<?php
oci_free_statement($stmt);
oci_close($conn);
?>
