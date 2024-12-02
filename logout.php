<?php
// Přidáme output buffering na začátek souboru
ob_start();

// Inicializace session
session_start();

// Přidáme require pro security log
require_once "config.php";
require_once "includes/security_log.php";

// Uložíme ID uživatele před vymazáním session
$user_id = $_SESSION["id"] ?? null;

// Zrušení všech session proměnných
$_SESSION = array();

// Zničení session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Zničení session
session_destroy();

// Logování odhlášení, pouze pokud známe ID uživatele
if ($user_id !== null) {
    logSecurityEvent($pdo, $user_id, 'logout', 'success');
}

// Před přesměrováním vyčistíme buffer a zajistíme správné přesměrování
ob_end_clean();
header("location: index.php");
exit;
?> 