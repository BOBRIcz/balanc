<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header('HTTP/1.1 403 Forbidden');
    exit;
}

if(!isset($_GET['month'])) {
    header('HTTP/1.1 400 Bad Request');
    exit;
}

$user_id = $_SESSION["id"];
$month = $_GET['month'];

$sql = "SELECT 
    casino,
    SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END) as deposits,
    SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) as withdrawals
    FROM transactions 
    WHERE user_id = :user_id 
    AND DATE_FORMAT(transaction_date, '%Y-%m') = :month
    GROUP BY casino
    ORDER BY (SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) - 
             SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END)) DESC";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
$stmt->bindParam(":month", $month, PDO::PARAM_STR);
$stmt->execute();
$casino_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($casino_details); 