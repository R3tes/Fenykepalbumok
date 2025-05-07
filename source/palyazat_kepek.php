<?php
session_start();
require_once 'resources/SUPPORT_FUNCS/db_connection.php';

if (!isset($_GET['id'])) {
    header("Location: palyazatok.php");
    exit();
}

$pID = intval($_GET['id']);
$fID = $_SESSION['fID'] ?? null;

$nyertesKepID = null;
$nyertesQuery = "SELECT kepID FROM Nyertesek WHERE pID = :pID";
$nyertesStmt = oci_parse($conn, $nyertesQuery);
oci_bind_by_name($nyertesStmt, ":pID", $pID);
oci_execute($nyertesStmt);
if ($nyertesRow = oci_fetch_assoc($nyertesStmt)) {
    $nyertesKepID = $nyertesRow['KEPID'];
}
oci_free_statement($nyertesStmt);

$szavazottKepIDs = [];
if ($fID) {
    $voteQuery = "SELECT kepID FROM Szavazatok WHERE fID = :fID AND pID = :pID";
    $voteStmt = oci_parse($conn, $voteQuery);
    oci_bind_by_name($voteStmt, ":fID", $fID);
    oci_bind_by_name($voteStmt, ":pID", $pID);
    oci_execute($voteStmt);
    while ($voteRow = oci_fetch_assoc($voteStmt)) {
        $szavazottKepIDs[] = $voteRow['KEPID'];
    }
    oci_free_statement($voteStmt);
}

$query = "SELECT palyazatNev FROM Palyazat WHERE pID = :pID";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ":pID", $pID);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
$palyazatNev = $row['PALYAZATNEV'] ?? 'Ismeretlen pályázat';
oci_free_statement($stmt);

$query = "SELECT k.kepID, k.kepNev, n.pont,
                 f.fNev AS feltolto,
                 h.orszag, h.megye, h.varos
          FROM Kep k
          JOIN Nevezett n ON k.kepID = n.kepID
          JOIN Felhasznalo f ON k.fID = f.fID
          LEFT JOIN Hely h ON k.helyID = h.helyID
          WHERE n.pID = :pID
          ORDER BY n.pont DESC";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ":pID", $pID);
oci_execute($stmt);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($palyazatNev, ENT_QUOTES, 'UTF-8'); ?> - Képek</title>
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
    <h1><?php echo htmlspecialchars($palyazatNev, ENT_QUOTES, 'UTF-8'); ?> - Képek</h1>

    <?php if ($nyertesKepID): ?>
        <p style="color: red; font-weight: bold;">Ez a pályázat lezárult, a nyertes ki lett hirdetve.</p>
    <?php endif; ?>

    <div class="gallery">
        <?php while ($row = oci_fetch_assoc($stmt)): ?>
            <?php
            $orszag = $row['ORSZAG'] ?? 'Ismeretlen';
            $megye = $row['MEGYE'] ?? 'Ismeretlen';
            $varos = $row['VAROS'] ?? 'Ismeretlen';
            $feltolto = $row['FELTOLTO'] ?? 'Ismeretlen';

            $kepID = $row['KEPID'];

            $katQuery = "SELECT k.kategoriaNev 
             FROM KategoriaResze kr
             JOIN Kategoria k ON kr.katID = k.katID
             WHERE kr.kepID = :kepID";

            $katStmt = oci_parse($conn, $katQuery);
            oci_bind_by_name($katStmt, ":kepID", $kepID);
            oci_execute($katStmt);

            $kategoriak = [];
            while ($katRow = oci_fetch_assoc($katStmt)) {
                $kategoriak[] = $katRow['KATEGORIANEV'];
            }
            oci_free_statement($katStmt);
            ?>
            <div class="image-card" >
                <img style="max-width: 80%; height: auto; border-radius: 12px; margin-left: 10%" src="<?php
                $dir = "resources/APP_IMGS/";
                $files = scandir($dir);
                $found = false;
                foreach ($files as $file) {
                    if (fnmatch($row['KEPNEV'] . ".*", $file)) {
                        echo "resources/APP_IMGS/" . $file;
                        $found = true;
                        break;
                    }
                }
                if (!$found) echo "resources/APP_IMGS/placeholder.png";
                ?>" alt="<?php echo htmlspecialchars($row['KEPNEV'], ENT_QUOTES, 'UTF-8'); ?>">

                <div class="imageInfo">
                    <h3>Kép címe: <?php echo htmlspecialchars($row['KEPNEV'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <?php
                    echo "<p style='font-weight: bold'>Helyszín: Ország: $orszag, Megye: $megye, Város: $varos</p>";
                    echo "<p style='font-weight: bold'>Feltöltő: " . htmlspecialchars($feltolto, ENT_QUOTES, 'UTF-8') . "</p>";
                    ?>
                    <?php if (!empty($kategoriak)): ?>
                        <p style="font-weight: bold">Kategóriák: <?php echo implode(', ', array_map(function ($k) {
                                return htmlspecialchars($k, ENT_QUOTES, 'UTF-8');
                            }, $kategoriak)); ?></p>
                    <?php endif; ?>
                    <p>Szavazatok: <?php echo $row['PONT']; ?></p>

                    <?php if ($nyertesKepID && $nyertesKepID == $row['KEPID']): ?>
                        <p style="color: green; font-weight: bold;">🎉 Nyertes kép!</p>
                    <?php endif; ?>

                    <?php if (!$nyertesKepID): ?>
                        <?php if ($fID && !in_array($row['KEPID'], $szavazottKepIDs)): ?>
                            <form method="POST" action="szavaz.php">
                                <input type="hidden" name="kepID" value="<?php echo $row['KEPID']; ?>">
                                <input type="hidden" name="pID" value="<?php echo $pID; ?>">
                                <button type="submit" class="btn">Szavazás</button>
                            </form>
                        <?php elseif ($fID): ?>
                            <div class="disabled-btn">
                                <button class="btn" disabled>Szavazat leadva</button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>

<?php
oci_free_statement($stmt);
oci_close($conn);
?>
