<?php
ob_start();
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $casino = trim($_POST["casino"]);
    $deposit_amount = !empty($_POST["deposit_amount"]) ? trim($_POST["deposit_amount"]) : 0;
    $withdrawal_amount = !empty($_POST["withdrawal_amount"]) ? trim($_POST["withdrawal_amount"]) : 0;
    $note = trim($_POST["note"]);
    $user_id = $_SESSION["id"];
    $transaction_date = !empty($_POST["transaction_date"]) ? $_POST["transaction_date"] : date('Y-m-d H:i:s');

    try {
        $pdo->beginTransaction();

        // Ověření, že kasino existuje
        $sql_check_casino = "SELECT id FROM casinos WHERE name = :casino_name";
        $stmt = $pdo->prepare($sql_check_casino);
        $stmt->bindParam(":casino_name", $casino, PDO::PARAM_STR);
        $stmt->execute();
        
        if($stmt->rowCount() == 0) {
            throw new Exception("Neplatné kasino");
        }

        // Vložení transakce
        $sql = "INSERT INTO transactions (user_id, type, amount, casino, description, transaction_date) 
                VALUES (:user_id, :type, :amount, :casino, :note, :transaction_date)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":amount", $amount, PDO::PARAM_STR);
        $stmt->bindParam(":casino", $casino, PDO::PARAM_STR);
        $stmt->bindParam(":note", $note, PDO::PARAM_STR);
        $stmt->bindParam(":transaction_date", $transaction_date, PDO::PARAM_STR);

        if($deposit_amount > 0) {
            $amount = $deposit_amount;
            $type = 'vklad';
            $stmt->bindParam(":type", $type, PDO::PARAM_STR);
            $stmt->execute();
        }

        if($withdrawal_amount > 0) {
            $amount = $withdrawal_amount;
            $type = 'výběr';
            $stmt->bindParam(":type", $type, PDO::PARAM_STR);
            $stmt->execute();
        }

        $pdo->commit();

        header("location: dashboard.php");
    } catch(PDOException $e) {
        $pdo->rollBack();
        echo "Chyba: " . $e->getMessage();
    }
}
?> 