<?php
// Nastavení session před jejím spuštěním
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 0);
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Spuštění session
session_start();

// Kontrola automatického přihlášení pouze pokud není aktivní session
if (!isset($_SESSION["loggedin"]) && isset($_COOKIE["remember_user"]) && isset($_COOKIE["remember_token"])) {
    require_once "config.php";
    
    $sql = "SELECT id, username, is_banned, banned_until FROM users 
            WHERE username = :username AND remember_token = :token";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":username" => $_COOKIE["remember_user"],
        ":token" => $_COOKIE["remember_token"]
    ]);
    
    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch();
        
        // Kontrola banu
        if($row["is_banned"]) {
            // Pokud je ban časový a vypršel
            if(!empty($row["banned_until"])) {
                $banned_until = new DateTime($row["banned_until"]);
                $now = new DateTime();
                
                if($banned_until <= $now) {
                    // Ban vypršel, odstraníme ho
                    $sql_unban = "UPDATE users SET is_banned = FALSE, ban_reason = NULL, banned_until = NULL WHERE id = :id";
                    $stmt_unban = $pdo->prepare($sql_unban);
                    $stmt_unban->execute([":id" => $row["id"]]);
                } else {
                    // Ban stále platí
                    setcookie("remember_user", "", time() - 3600, "/");
                    setcookie("remember_token", "", time() - 3600, "/");
                    return;
                }
            } else {
                // Trvalý ban
                setcookie("remember_user", "", time() - 3600, "/");
                setcookie("remember_token", "", time() - 3600, "/");
                return;
            }
        }
        
        $_SESSION["loggedin"] = true;
        $_SESSION["id"] = $row["id"];
        $_SESSION["username"] = $row["username"];
        $_SESSION["cookie_notice_accepted"] = true;
    } else {
        // Pokud token není platný, vymažeme cookies
        setcookie("remember_user", "", time() - 3600, "/");
        setcookie("remember_token", "", time() - 3600, "/");
    }
}
?> 