<?php
// Na začátek souboru config.php přidejte:
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '/balanc');  // Upravte podle vaší skutečné cesty
}

// Databázová konfigurace
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'casino_tracker');

define('ROOT_PATH', dirname(__DIR__));

try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8mb4");
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
?> 