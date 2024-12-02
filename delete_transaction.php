<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header('HTTP/1.1 403 Forbidden');
    exit('Přístup odepřen');
}

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $transaction_id = (int)$_POST['id'];
    $user_id = $_SESSION["id"];

    try {
        // Ověření, že transakce patří přihlášenému uživateli
        $sql = "DELETE FROM transactions 
                WHERE id = :id AND user_id = :user_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $transaction_id,
            ':user_id' => $user_id
        ]);

        if($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Transakce nebyla nalezena']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Chyba při mazání transakce']);
    }
}
?> 