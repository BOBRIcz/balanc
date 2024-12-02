<?php
require_once "includes/init_session.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'accept_cookies') {
    $_SESSION["cookie_notice_accepted"] = true;
    setcookie('cookies_accepted', '1', time() + (365 * 24 * 60 * 60), '/', '', true, true);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false]);
?> 