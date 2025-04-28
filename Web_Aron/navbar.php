<?php
$is_logged_in = isset($_SESSION['user_id']);
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="resources/navbar.css">

<nav class="navbar">
    <div class="navbar-brand">
        <a href="index.php">FotóPont</a>
    </div>
    <div class="navbar-links">
        <?php if ($is_logged_in): ?>
            <a href="../Web/PHP/profile.php?id=<?php echo $_SESSION['fID']; ?>">Profilom</a>
            <a href="../web_lara/palyazatok.php">Pályázatok</a>
            <a href="../Web/PHP/logout.php">Kijelentkezés</a>
        <?php else: ?>
            <?php if ($current_page === 'login.php'): ?>
                <a href="../web_lara/register.php">Regisztráció</a>
            <?php elseif ($current_page === 'register.php'): ?>
                <a href="../web_lara/login.php">Bejelentkezés</a>
            <?php else: ?>
                <a href="../web_lara/login.php">Bejelentkezés</a>
                <a href="../web_lara/register.php">Regisztráció</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</nav>
