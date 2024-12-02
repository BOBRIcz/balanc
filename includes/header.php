<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . "/init_session.php";

// Jednoduchá podmínka pro zobrazení cookie notice
$show_cookie_notice = isset($_SESSION["loggedin"]) && !isset($_SESSION["cookie_notice_accepted"]);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casino Tracker</title>

    <!-- Zbytek stylů -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Preload hlavního CSS -->
    <link rel="preload" href="assets/css/style.css" as="style" onload="this.rel='stylesheet'">
</head>
<body>
    <?php if ($show_cookie_notice): ?>
    <div id="cookie-modal" class="cookie-modal">
        <div class="cookie-modal-overlay"></div>
        <div class="cookie-modal-content">
            <div class="cookie-modal-body">
                <h4>Používání cookies</h4>
                <p>
                    Tento web používá cookies pro zlepšení vašeho zážitku z prohlížení. 
                    Používáním našeho webu souhlasíte s našimi zásadami používání cookies.
                </p>
                <div class="text-center mt-4">
                    <button id="accept-cookies" class="btn btn-primary">
                        Souhlasím a chci pokračovat
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                Casino Tracker <span class="badge">BETA</span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item mr-3">
                        <label class="theme-toggle">
                            <input type="checkbox" id="darkModeToggle">
                            <span class="theme-toggle-slider">
                                <i class="fas fa-sun"></i>
                                <i class="fas fa-moon"></i>
                            </span>
                        </label>
                    </li>
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-chart-bar mr-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="overview.php">
                                <i class="fas fa-chart-pie mr-1"></i>Přehled
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="transactions.php">
                                <i class="fas fa-exchange-alt mr-1"></i>Transakce
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="statistics.php">Statistiky</a>
                        </li>
                        <?php if(isset($_SESSION["is_admin"]) && $_SESSION["is_admin"]): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" 
                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-shield-alt"></i> Admin
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="<?php echo BASE_PATH; ?>/admin/users.php">
                                        <i class="fas fa-users"></i> Správa uživatelů
                                    </a>
                                    <a class="dropdown-item" href="<?php echo BASE_PATH; ?>/admin/security_logs.php">
                                        <i class="fas fa-list"></i> Bezpečnostní logy
                                    </a>
                                </div>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                                <i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($_SESSION["username"]); ?>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user-cog mr-1"></i>Profil
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt mr-1"></i>Odhlásit
                                </a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt mr-1"></i>Přihlášení
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus mr-1"></i>Registrace
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <script src="assets/js/theme.js"></script>
</body>
</html> 