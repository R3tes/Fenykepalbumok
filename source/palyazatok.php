<?php
include('resources/SUPPORT_FUNCS/db_connection.php');

session_start();
$is_admin = $_SESSION['is_admin'];

$query = "SELECT p.pID, p.palyazatNev FROM Palyazat p ORDER BY p.pID DESC";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pályázatok</title>
    <link rel="stylesheet" href="resources/CSS/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<?php if (isset($_SESSION['success_message'])): ?>
    <script>
        alert("<?= addslashes($_SESSION['success_message']) ?>");
    </script>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

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
                <td>
                    <a href="palyazat_kepek.php?id=<?php echo $row['PID']; ?>">
                        <?php echo htmlspecialchars($row['PALYAZATNEV'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </td>
                <td>
                    <?php
                        $nyertesQuery = "SELECT COUNT(*) AS CNT FROM Nyertesek WHERE pID = :pID";
                        $nyertesStmt = oci_parse($conn, $nyertesQuery);
                        oci_bind_by_name($nyertesStmt, ":pID", $row['PID']);
                        oci_execute($nyertesStmt);
                        $nyertesRow = oci_fetch_assoc($nyertesStmt);
                        $vanNyertes = $nyertesRow['CNT'] > 0;
                        oci_free_statement($nyertesStmt);

                        if (!$vanNyertes): ?>
                            <a href="palyazatra_jelentkezes.php?id=<?php echo $row['PID']; ?>" class="btn">Jelentkezés</a>
                        <?php else: ?>
                            <span style="color:gray;">Lezárt</span>
                        <?php endif;
                    ?>
                </td>

                <?php if ($is_admin): ?>
                    <td>
                        <a href="admin_palyazat_szerkeszt.php?id=<?php echo $row['PID']; ?>" class="btn">Szerkesztés</a>
                        <a href="admin_palyazat_torles.php?id=<?php echo $row['PID']; ?>" class="btn">Törlés</a>

                        <?php
                        $nyertesQuery = "SELECT COUNT(*) AS CNT FROM Nyertesek WHERE pID = :pID";
                        $nyStmt = oci_parse($conn, $nyertesQuery);
                        oci_bind_by_name($nyStmt, ":pID", $row['PID']);
                        oci_execute($nyStmt);
                        $nyertesRow = oci_fetch_assoc($nyStmt);
                        $vanNyertes = $nyertesRow['CNT'] > 0;
                        oci_free_statement($nyStmt);
                        ?>

                        <?php if (!$vanNyertes): ?>
                            <?php
                            $pID = $row['PID'];

                            $stmtNev = oci_parse($conn, "SELECT COUNT(*) AS db FROM Nevezett WHERE pID = :pID");
                            oci_bind_by_name($stmtNev, ":pID", $pID);
                            oci_execute($stmtNev);
                            $nevCount = oci_fetch_assoc($stmtNev)['DB'];
                            oci_free_statement($stmtNev);

                            $stmtSzav = oci_parse($conn, "SELECT COUNT(*) AS db FROM Szavazatok WHERE pID = :pID");
                            oci_bind_by_name($stmtSzav, ":pID", $pID);
                            oci_execute($stmtSzav);
                            $szavCount = oci_fetch_assoc($stmtSzav)['DB'];
                            oci_free_statement($stmtSzav);

                            $showHirdetButton = ($nevCount > 0 && $szavCount > 0);

                            if ($showHirdetButton) {
                            ?>

                            <form method="POST" action="hirdet_nyertest.php" style="display:inline;">
                            <input type="hidden" name="pID" value="<?php echo $row['PID']; ?>">
                            <button type="submit" class="btn" style="width:30%;">Nyertes kihirdetése</button>
                            </form>
                            <?php } ?>

                        <?php else: ?>
                            <span style="color:green; display:inline-block; margin-top: 5px;">Nyertes kihirdetve</span>
                        <?php endif; ?>
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
