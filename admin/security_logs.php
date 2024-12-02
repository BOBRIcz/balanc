<?php
require_once "header.php";
require_once "../config.php";

// Kontrola admin práv
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true){
    header("location: ../index.php");
    exit;
}

$page_title = "Bezpečnostní logy";
$is_admin_page = true;

// Stránkování
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

try {
    // Kontrola existence tabulky
    $stmt = $pdo->query("SHOW TABLES LIKE 'security_log'");
    if($stmt->rowCount() == 0) {
        // Tabulka neexistuje, vytvoříme ji
        $sql = "CREATE TABLE security_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(255) NOT NULL,
            status VARCHAR(50) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )";
        $pdo->exec($sql);
    }

    // Celkový počet záznamů
    $stmt = $pdo->query("SELECT COUNT(*) FROM security_log");
    $total_records = $stmt->fetchColumn();
    $total_pages = ceil($total_records / $per_page);

    // Získání logů
    $sql = "SELECT sl.*, u.username, u.email 
            FROM security_log sl 
            LEFT JOIN users u ON sl.user_id = u.id 
            ORDER BY sl.created_at DESC 
            LIMIT :offset, :per_page";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Chyba databáze: " . $e->getMessage());
}
?>

<div class="container-fluid mt-4 admin-panel">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bezpečnostní logy</h3>
                </div>
                <div class="card-body">
                    <?php if(empty($logs)): ?>
                        <div class="alert alert-info">Zatím nejsou k dispozici žádné bezpečnostní logy.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Datum a čas</th>
                                        <th>Uživatel</th>
                                        <th>Akce</th>
                                        <th>Status</th>
                                        <th>IP adresa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($logs as $log): ?>
                                        <tr>
                                            <td><?php echo date('d.m.Y H:i:s', strtotime($log['created_at'])); ?></td>
                                            <td>
                                                <?php if($log['username']): ?>
                                                    <?php echo htmlspecialchars($log['username']); ?>
                                                    <small class="text-muted d-block">
                                                        <?php echo htmlspecialchars($log['email']); ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted">Smazaný uživatel</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                                            <td><?php echo htmlspecialchars($log['status']); ?></td>
                                            <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?> 