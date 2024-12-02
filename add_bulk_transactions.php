<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $casino = trim($_POST["casino"]);
    $deposit_amounts = $_POST["deposit_amount"];
    $withdrawal_amounts = $_POST["withdrawal_amount"];
    $user_id = $_SESSION["id"];

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

        // Upravený SQL dotaz bez transaction_date
        $sql = "INSERT INTO transactions (user_id, type, amount, casino) 
                VALUES (:user_id, :type, :amount, :casino)";
        
        $stmt = $pdo->prepare($sql);

        // Procházíme všechny transakce
        foreach($deposit_amounts as $index => $deposit_amount) {
            if(!empty($deposit_amount)) {
                $stmt->execute([
                    ':user_id' => $user_id,
                    ':type' => 'vklad',
                    ':amount' => $deposit_amount,
                    ':casino' => $casino
                ]);
            }
            
            if(!empty($withdrawal_amounts[$index])) {
                $stmt->execute([
                    ':user_id' => $user_id,
                    ':type' => 'výběr',
                    ':amount' => $withdrawal_amounts[$index],
                    ':casino' => $casino
                ]);
            }
        }

        $pdo->commit();
        header("location: dashboard.php");
    } catch(Exception $e) {
        $pdo->rollBack();
        echo "Chyba: " . $e->getMessage();
    }
} 