<?php
session_start();
require_once "config.php";
require_once "includes/header.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];

// Získání top kasin podle bilance
$sql_top_casinos = "SELECT 
    casino,
    SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END) as deposits,
    SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) as withdrawals
    FROM transactions 
    WHERE user_id = :user_id 
    GROUP BY casino
    ORDER BY (SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) - 
             SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END)) DESC
    LIMIT 5";

// Získání top výběrů
$sql_top_withdrawals = "SELECT 
    casino, amount, transaction_date 
    FROM transactions 
    WHERE user_id = :user_id AND type = 'výběr'
    ORDER BY amount DESC 
    LIMIT 5";

// Získání největších vkladů
$sql_top_deposits = "SELECT 
    casino, amount, transaction_date 
    FROM transactions 
    WHERE user_id = :user_id AND type = 'vklad'
    ORDER BY amount DESC 
    LIMIT 5";

// Na začátku souboru
$ceske_mesice = [
    'January' => 'Leden',
    'February' => 'Únor',
    'March' => 'Březen',
    'April' => 'Duben',
    'May' => 'Květen',
    'June' => 'Červen',
    'July' => 'Červenec',
    'August' => 'Srpen',
    'September' => 'Září',
    'October' => 'Říjen',
    'November' => 'Listopad',
    'December' => 'Prosinec'
];

try {
    // SQL dotaz pro měsíční přehled
    $sql_monthly_overview = "SELECT 
        DATE_FORMAT(transaction_date, '%Y-%m') as month,
        SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END) as deposits,
        SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) as withdrawals
        FROM transactions 
        WHERE user_id = :user_id 
        GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
        ORDER BY month DESC";

    $stmt = $pdo->prepare($sql_monthly_overview);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $monthly_overview = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $monthly_overview = [];
    error_log("Chyba při načítání měsíčního přehledu: " . $e->getMessage());
}

try {
    // Top kasina
    $stmt = $pdo->prepare($sql_top_casinos);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $top_casinos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Top výběry
    $stmt = $pdo->prepare($sql_top_withdrawals);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $top_withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Největší vklady
    $stmt = $pdo->prepare($sql_top_deposits);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $top_deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $monthly_overview = [];
    // Můžete přidat logování chyby nebo zobrazit uživatelsky přívětivou zprávu
    echo "Omlouváme se, došlo k chybě při načítání dat.";
}

?>

<div class="container mt-4">
    <h2>Přehled</h2>

    <div class="row">
        <!-- Top kasina -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Top kasina podle bilance</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kasino</th>
                                    <th>Bilance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($top_casinos as $casino): ?>
                                    <?php $balance = $casino['withdrawals'] - $casino['deposits']; ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($casino['casino']); ?></td>
                                        <td class="<?php echo $balance >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo number_format($balance, 0, ',', ' '); ?> Kč
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top výběry -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Top výběry</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kasino</th>
                                    <th>Částka</th>
                                    <th>Datum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($top_withdrawals as $withdrawal): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($withdrawal['casino']); ?></td>
                                        <td><?php echo number_format($withdrawal['amount'], 0, ',', ' '); ?> Kč</td>
                                        <td>
                                            <?php 
                                            echo !empty($withdrawal['transaction_date']) 
                                                ? date('d.m.Y', strtotime($withdrawal['transaction_date'])) 
                                                : "-- -- ----"; 
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Největší vklady -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Největší vklady</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kasino</th>
                                    <th>Částka</th>
                                    <th>Datum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($top_deposits as $deposit): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($deposit['casino']); ?></td>
                                        <td><?php echo number_format($deposit['amount'], 0, ',', ' '); ?> Kč</td>
                                        <td>
                                            <?php 
                                            echo !empty($deposit['transaction_date']) 
                                                ? date('d.m.Y', strtotime($deposit['transaction_date'])) 
                                                : "-- -- ----"; 
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Měsíční přehled</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Měsíc</th>
                            <th>Vklady</th>
                            <th>Výběry</th>
                            <th>Bilance</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($monthly_overview)): ?>
                            <?php foreach($monthly_overview as $month): 
                                // Kontrola, zda existují všechny potřebné hodnoty
                                if (!isset($month['month']) || !isset($month['withdrawals']) || !isset($month['deposits'])) {
                                    continue;
                                }
                                $balance = $month['withdrawals'] - $month['deposits'];
                                $monthValue = trim($month['month']); // Zajistíme, že hodnota není prázdná
                                if (empty($monthValue)) {
                                    continue;
                                }
                            ?>
                                <tr class="month-row" data-month="<?php echo htmlspecialchars($monthValue); ?>">
                                    <td><?php 
                                        try {
                                            $date = new DateTime($monthValue . '-01');
                                            $mesic_en = $date->format('F');
                                            $rok = $date->format('Y');
                                            echo isset($ceske_mesice[$mesic_en]) ? $ceske_mesice[$mesic_en] . ' ' . $rok : '';
                                        } catch (Exception $e) {
                                            echo 'Neplatné datum';
                                        }
                                    ?></td>
                                    <td><?php echo number_format((float)$month['deposits'], 0, ',', ' '); ?> Kč</td>
                                    <td><?php echo number_format((float)$month['withdrawals'], 0, ',', ' '); ?> Kč</td>
                                    <td class="<?php echo ($month['withdrawals'] - $month['deposits']) >= 0 ? 'balance-positive' : 'balance-negative'; ?>">
                                        <?php echo number_format($month['withdrawals'] - $month['deposits'], 0, ',', ' '); ?> Kč
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary toggle-details">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="casino-details d-none" data-month="<?php echo htmlspecialchars($monthValue); ?>">
                                    <td colspan="5" class="p-0">
                                        <div class="loading text-center py-3 d-none">
                                            <i class="fas fa-spinner fa-spin"></i> Načítání...
                                        </div>
                                        <div class="details-content"></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Žádné transakce k zobrazení</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-details').forEach(button => {
        button.addEventListener('click', function() {
            const monthRow = this.closest('.month-row');
            const month = monthRow.dataset.month;
            const detailsRow = document.querySelector(`.casino-details[data-month="${month}"]`);
            const icon = this.querySelector('i');
            
            // Toggle ikony
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
            
            // Toggle řádku s detaily
            detailsRow.classList.toggle('d-none');
            
            // Pokud je obsah prázdný, načteme data
            if (!detailsRow.querySelector('.details-content').innerHTML.trim()) {
                const loading = detailsRow.querySelector('.loading');
                const content = detailsRow.querySelector('.details-content');
                
                loading.classList.remove('d-none');
                
                // AJAX požadavek pro získání detailů
                fetch(`get_casino_details.php?month=${month}`)
                    .then(response => response.json())
                    .then(data => {
                        let html = `
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Kasino</th>
                                        <th>Vklady</th>
                                        <th>Výběry</th>
                                        <th>Bilance</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                        
                        data.forEach(casino => {
                            const balance = casino.withdrawals - casino.deposits;
                            html += `
                                <tr>
                                    <td>${casino.casino}</td>
                                    <td>${new Intl.NumberFormat('cs-CZ').format(casino.deposits)} Kč</td>
                                    <td>${new Intl.NumberFormat('cs-CZ').format(casino.withdrawals)} Kč</td>
                                    <td class="${balance >= 0 ? 'balance-positive' : 'balance-negative'}">
                                        ${new Intl.NumberFormat('cs-CZ').format(balance)} Kč
                                    </td>
                                </tr>`;
                        });
                        
                        html += `</tbody></table>`;
                        content.innerHTML = html;
                        loading.classList.add('d-none');
                    });
            }
        });
    });
});
</script>
