<?php
// Kontrola, zda session již není aktivní
if (session_status() === PHP_SESSION_NONE) {
    // Nastavení session parametrů
    ini_set('session.cookie_lifetime', '0');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => true,  // Pouze pokud používáte HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}
?> 