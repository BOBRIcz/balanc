<?php
require_once dirname(__DIR__) . '/includes/init_session.php';

// Kontrola admin práv
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("HTTP/1.1 403 Forbidden");
    header("Location: ../index.php");
    exit("Přístup odepřen");
}
?> 