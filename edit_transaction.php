<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header('HTTP/1.1 403 Forbidden');
    exit('Přístup odepřen');
}

// Získání dat transakce pro editaci
if($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $transaction_id = (int)$_GET['id'];
    $user_id = $_SESSION["id"];

    try {
        $sql = "SELECT * FROM transactions 
                WHERE id = :id AND user_id = :user_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $transaction_id,
            ':user_id' => $user_id
        ]);

        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Transakce nebyla nalezena']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Chyba při načítání transakce']);
    }
}

// Uložení upravené transakce
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $transaction_id = (int)$_POST['id'];
    $user_id = $_SESSION["id"];
    $amount = trim($_POST["amount"]);
    $casino = trim($_POST["casino"]);
    $type = trim($_POST["type"]);
    $note = trim($_POST["note"] ?? '');
    $transaction_date = trim($_POST["transaction_date"]);

    try {
        $sql = "UPDATE transactions 
                SET amount = :amount,
                    casino = :casino,
                    type = :type,
                    description = :note,
                    transaction_date = :transaction_date
                WHERE id = :id AND user_id = :user_id";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':id' => $transaction_id,
            ':user_id' => $user_id,
            ':amount' => $amount,
            ':casino' => $casino,
            ':type' => $type,
            ':note' => $note,
            ':transaction_date' => $transaction_date
        ]);

        if($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Chyba při ukládání změn']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Chyba při ukládání transakce']);
    }
}
?> 