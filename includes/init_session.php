<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kontrola cookie notice při načtení každé stránky
if (isset($_COOKIE['cookies_accepted']) && !isset($_SESSION['cookie_notice_accepted'])) {
    $_SESSION['cookie_notice_accepted'] = true;
}
?> 