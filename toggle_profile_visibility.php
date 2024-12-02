<?php
session_start();
require_once "config.php";

// Kontrola přihlášení
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

// Přidáme CSRF ochranu
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'Invalid token']));
}

// Jednoduchý rate limiting
$timeWindow = 60; // 60 sekund
$maxRequests = 10; // maximální počet požadavků za časové okno

if (!isset($_SESSION['rate_limit'])) {
    $_SESSION['rate_limit'] = [
        'count' => 0,
        'first_request' => time()
    ];
}

if (time() - $_SESSION['rate_limit']['first_request'] > $timeWindow) {
    // Reset počítadla po uplynutí časového okna
    $_SESSION['rate_limit'] = [
        'count' => 1,
        'first_request' => time()
    ];
} else {
    $_SESSION['rate_limit']['count']++;
    if ($_SESSION['rate_limit']['count'] > $maxRequests) {
        http_response_code(429);
        exit(json_encode(['success' => false, 'message' => 'Too many requests']));
    }
}

function logSecurityEvent($user_id, $action, $status) {
    global $pdo;
    $sql = "INSERT INTO security_log (user_id, action, status, ip_address) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $action, $status, $_SERVER['REMOTE_ADDR']]);
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $user_id = $_SESSION["id"];
    $new_status = filter_var(
        isset($_POST['public']) ? $_POST['public'] : '0',
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 0, 'max_range' => 1]]
    );

    if ($new_status === false) {
        http_response_code(400);
        exit(json_encode(['success' => false, 'message' => 'Invalid input']));
    }

    try {
        // Pro debugování
        error_log("Updating profile visibility for user $user_id to $new_status");
        
        $sql = "UPDATE users SET public_profile = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':status' => $new_status,
            ':id' => $user_id
        ]);

        if($result) {
            logSecurityEvent($user_id, 'profile_visibility_change', $new_status);
            echo json_encode(['success' => true, 'public' => $new_status]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Update failed']);
        }
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 