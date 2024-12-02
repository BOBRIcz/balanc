<?php
// Vymaže všechny cookies
if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time()-3600, '/');
    }
}

// Zrušení session
session_start();
session_destroy();

// Vymazání session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Přesměrování zpět na hlavní stránku
header("location: index.php");
exit;
?> 