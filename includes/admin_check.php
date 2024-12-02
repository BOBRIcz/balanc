<?php
function isAdmin() {
    return isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true;
}

function requireAdmin() {
    if (!isAdmin()) {
        header("location: index.php");
        exit;
    }
}
?> 