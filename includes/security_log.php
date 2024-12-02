<?php
/**
 * Funkce pro logování bezpečnostních událostí
 * 
 * @param PDO $pdo PDO instance pro připojení k databázi
 * @param int|null $user_id ID uživatele nebo null pro neautentizované akce
 * @param string $action Typ akce (např. 'login', 'logout', 'password_change')
 * @param string $status Status akce ('success' nebo 'failed')
 * @param string|null $details Volitelné dodatečné informace
 * @return bool True pokud byl log úspěšně vytvořen
 */
function logSecurityEvent($pdo, $user_id, $action, $status, $details = null) {
    try {
        $sql = "INSERT INTO security_log (user_id, action, status, ip_address, details) 
                VALUES (:user_id, :action, :status, :ip_address, :details)";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':action' => $action,
            ':status' => $status,
            ':ip_address' => $_SERVER['REMOTE_ADDR'],
            ':details' => $details
        ]);
    } catch (PDOException $e) {
        error_log("Security log error: " . $e->getMessage());
        return false;
    }
} 