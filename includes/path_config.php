<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}

// Definujeme URL cesty
define('BASE_URL', '/'); // Upravte podle vašeho nastavení
define('ADMIN_URL', BASE_URL . 'admin/');

// Definujeme fyzické cesty
define('INCLUDES_PATH', ROOT_PATH . 'includes' . DIRECTORY_SEPARATOR);
define('ADMIN_PATH', ROOT_PATH . 'admin' . DIRECTORY_SEPARATOR);
?> 