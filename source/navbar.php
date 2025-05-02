<?php
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin']; // Új változó az admin ellenőrzéshez
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="resources/CSS/navbar.css">

<nav class="navbar">
    <div class="navbar-brand">
        <a href="index.php">FotóPont</a>
    </div>
    <div class="navbar-links">
        <?php if ($is_logged_in): ?>
            <a style="font-weight: bold;" href="profile.php?id=<?php echo $_SESSION['fID']; ?>">Profilom</a>
            <a style="font-weight: bold;" href="palyazatok.php">Pályázatok</a>
            <?php if ($is_admin): ?>
                <a style="font-weight: bold;" href="uj_admin.php">Új admin</a>
            <?php endif; ?>
            <a style="font-weight: bold;" href="logout.php">Kijelentkezés</a>
        <?php else: ?>
            <?php if ($current_page === 'login.php'): ?>
                <a style="font-weight: bold;" href="register.php">Regisztráció</a>
            <?php elseif ($current_page === 'register.php'): ?>
                <a style="font-weight: bold;" href="login.php">Bejelentkezés</a>
            <?php else: ?>
                <a style="font-weight: bold;" href="login.php">Bejelentkezés</a>
                <a style="font-weight: bold;" href="register.php">Regisztráció</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</nav>
