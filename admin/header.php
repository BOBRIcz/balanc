<?php
require_once __DIR__ . "/../includes/init_session.php";
require_once __DIR__ . "/../includes/admin_check.php";
define('BASE_PATH', '/balanc');

requireAdmin();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Casino Tracker</title>
    
    <!-- Styly -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    
    <!-- Dark mode detekce -->
    <script>
        let theme = localStorage.getItem('theme');
        if (theme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.addEventListener('DOMContentLoaded', function() {
                const themeToggle = document.getElementById('themeToggle');
                if (themeToggle) {
                    themeToggle.checked = true;
                }
            });
        }
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                Casino Tracker <span class="badge badge-warning">BETA</span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item mr-3">
                        <label class="theme-toggle">
                            <input type="checkbox" id="themeToggle" onclick="toggleTheme()">
                            <span class="theme-toggle-slider">
                                <i class="fas fa-sun"></i>
                                <i class="fas fa-moon"></i>
                            </span>
                        </label>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php">
                            <i class="fas fa-chart-bar mr-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="adminDropdown" role="button" 
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-shield-alt"></i> Admin
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="users.php">
                                <i class="fas fa-users"></i> Správa uživatelů
                            </a>
                            <a class="dropdown-item" href="security_logs.php">
                                <i class="fas fa-list"></i> Bezpečnostní logy
                            </a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($_SESSION["username"]); ?>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="../profile.php">
                                <i class="fas fa-user-cog mr-1"></i>Profil
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="../logout.php">
                                <i class="fas fa-sign-out-alt mr-1"></i>Odhlásit
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Skripty -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function toggleTheme() {
            let currentTheme = document.documentElement.getAttribute('data-theme');
            let newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }
    </script>
</body>
</html> 