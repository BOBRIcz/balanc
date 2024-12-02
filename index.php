<?php
require_once "init.php";
require_once "config.php";
require_once "includes/header.php";

// Získání globálních statistik pro veřejné profily
$sql_global_stats = "
    SELECT 
        -- Dnešní statistiky
        SUM(CASE 
            WHEN DATE(transaction_date) = CURDATE() AND type = 'vklad' THEN amount 
            ELSE 0 
        END) as today_deposits,
        SUM(CASE 
            WHEN DATE(transaction_date) = CURDATE() AND type = 'výběr' THEN amount 
            ELSE 0 
        END) as today_withdrawals,
        
        -- Měsíční statistiky
        SUM(CASE 
            WHEN DATE_FORMAT(transaction_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') 
            AND type = 'vklad' THEN amount 
            ELSE 0 
        END) as month_deposits,
        SUM(CASE 
            WHEN DATE_FORMAT(transaction_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') 
            AND type = 'výběr' THEN amount 
            ELSE 0 
        END) as month_withdrawals,
        
        -- Roční statistiky
        SUM(CASE 
            WHEN YEAR(transaction_date) = YEAR(CURDATE()) AND type = 'vklad' THEN amount 
            ELSE 0 
        END) as year_deposits,
        SUM(CASE 
            WHEN YEAR(transaction_date) = YEAR(CURDATE()) AND type = 'výběr' THEN amount 
            ELSE 0 
        END) as year_withdrawals
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    WHERE u.public_profile = 1";

$stmt = $pdo->prepare($sql_global_stats);
$stmt->execute();
$global_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Získání TOP 5 největších výběrů
$sql_top_withdrawals = "
    SELECT 
        t.amount,
        t.casino,
        t.transaction_date,
        COALESCE(u.display_name, u.username) as username
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    WHERE u.public_profile = 1 AND t.type = 'výběr'
    ORDER BY t.amount DESC
    LIMIT 5";

$stmt = $pdo->prepare($sql_top_withdrawals);
$stmt->execute();
$top_withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pokud uživatel není přihlášen, zobrazíme uvítací stránku
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Původní uvítací obsah
    require_once "welcome.php";
} else {
    // Zobrazení globálních statistik pro přihlášené uživatele
    ?>
    <div class="container mt-4">
        <h2 class="mb-4">Globální statistiky <small class="text-muted">(veřejné profily)</small></h2>
        
        <!-- Statistiky pro dnešek, měsíc a rok -->
        <div class="row">
            <!-- Dnešní statistiky -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Dnes</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Vklady:</span>
                            <span class="text-danger"><?php echo number_format($global_stats['today_deposits'] ?? 0, 0, ',', ' '); ?> Kč</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Výběry:</span>
                            <span class="text-success"><?php echo number_format($global_stats['today_withdrawals'] ?? 0, 0, ',', ' '); ?> Kč</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Měsíční statistiky -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Tento měsíc</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Vklady:</span>
                            <span class="text-danger"><?php echo number_format($global_stats['month_deposits'] ?? 0, 0, ',', ' '); ?> Kč</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Výběry:</span>
                            <span class="text-success"><?php echo number_format($global_stats['month_withdrawals'] ?? 0, 0, ',', ' '); ?> Kč</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Roční statistiky -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Tento rok</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Vklady:</span>
                            <span class="text-danger"><?php echo number_format($global_stats['year_deposits'] ?? 0, 0, ',', ' '); ?> Kč</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Výběry:</span>
                            <span class="text-success"><?php echo number_format($global_stats['year_withdrawals'] ?? 0, 0, ',', ' '); ?> Kč</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TOP 5 největších výběrů -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">TOP 5 největších výběrů</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Uživatel</th>
                                <th>Kasino</th>
                                <th>Částka</th>
                                <th>Datum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_withdrawals as $withdrawal): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($withdrawal['username']); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['casino']); ?></td>
                                <td class="text-success"><?php echo number_format($withdrawal['amount'], 0, ',', ' '); ?> Kč</td>
                                <td><?php echo $withdrawal['transaction_date'] ? date('d.m.Y', strtotime($withdrawal['transaction_date'])) : '-'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
}

require_once "includes/footer.php";
?>
