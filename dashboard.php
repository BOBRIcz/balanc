<?php
require_once "init.php";
require_once "config.php";
require_once "includes/header.php";

// Kontrola přihlášení až po zobrazení cookie notice
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo '<div class="container mt-5 text-center">
            <h1>Vítejte na Casino Tracker</h1>
            <p class="lead">Sledujte své transakce a získejte přehled o svých financích v online kasinech.</p>
            <div class="mt-4">
                <a href="register.php" class="btn btn-success btn-lg mr-2">Zaregistrujte se</a>
                <a href="login.php" class="btn btn-primary btn-lg">Přihlásit se</a>
            </div>
          </div>';
    require_once "includes/footer.php";
    exit;
}

$user_id = $_SESSION["id"];

// Získání celkových statistik
$sql_totals = "SELECT 
    SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END) as total_deposits,
    SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) as total_withdrawals,
    COUNT(DISTINCT casino) as total_casinos,
    COUNT(*) as total_transactions
    FROM transactions 
    WHERE user_id = :user_id";

// Získání posledních transakcí
$sql_recent = "SELECT * FROM transactions 
    WHERE user_id = :user_id 
    ORDER BY transaction_date DESC 
    LIMIT 5";

// Získání statistik podle kasina
$sql_by_casino = "SELECT 
    casino,
    SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END) as deposits,
    SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) as withdrawals
    FROM transactions 
    WHERE user_id = :user_id 
    GROUP BY casino
    ORDER BY (SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) - 
             SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END)) DESC
    LIMIT 5";

// SQL dotaz pro denní data
$sql_daily = "SELECT 
    DATE(transaction_date) as date,
    SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END) as deposits,
    SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) as withdrawals
    FROM transactions 
    WHERE user_id = :user_id 
    AND transaction_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
    GROUP BY DATE(transaction_date)
    ORDER BY date ASC";

// SQL dotaz pro týdenní data
$sql_weekly = "SELECT 
    DATE(DATE_SUB(transaction_date, INTERVAL WEEKDAY(transaction_date) DAY)) as week_start,
    SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END) as deposits,
    SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) as withdrawals
    FROM transactions 
    WHERE user_id = :user_id 
    AND transaction_date >= DATE_SUB(CURRENT_DATE, INTERVAL 12 WEEK)
    GROUP BY week_start
    ORDER BY week_start ASC";

$sql_monthly = "SELECT 
    DATE_FORMAT(transaction_date, '%Y-%m-01') as month_start,
    SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END) as deposits,
    SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) as withdrawals
    FROM transactions 
    WHERE user_id = :user_id 
    AND transaction_date >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(transaction_date, '%Y-%m-01')
    ORDER BY month_start";

// SQL dotaz pro celková data
$sql_all_time = "SELECT 
    DATE_FORMAT(transaction_date, '%Y-%m-01') as month_start,
    SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END) as deposits,
    SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) as withdrawals
    FROM transactions 
    WHERE user_id = :user_id 
    GROUP BY month_start
    ORDER BY month_start";

try {
    // Celkové statistiky
    $stmt = $pdo->prepare($sql_totals);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $totals = $stmt->fetch(PDO::FETCH_ASSOC);

    // Poslední transakce
    $stmt = $pdo->prepare($sql_recent);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Statistiky podle kasina
    $stmt = $pdo->prepare($sql_by_casino);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $casino_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Denní data
    $stmt = $pdo->prepare($sql_daily);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $daily_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Týdenní data
    $stmt = $pdo->prepare($sql_weekly);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $weekly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Měsíční data
    $stmt = $pdo->prepare($sql_monthly);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $monthly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Získání dat pro celková data
    $stmt = $pdo->prepare($sql_all_time);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $all_time_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $daily_data = [];
    $weekly_data = [];
}

$total_balance = ($totals['total_withdrawals'] ?? 0) - ($totals['total_deposits'] ?? 0);

require_once "includes/header.php";
?>
<style>
    #transactionsChart {
        max-height: 400px; /* Nastavte maximální výšku grafu */
    }
</style>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h2>Dashboard</h2>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#newTransactionModal">
                <i class="fas fa-plus mr-2"></i>Nová transakce
            </button>
        </div>
    </div>

    <!-- Statistické karty -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Celková bilance</h5>
                    <h3 class="<?php echo $total_balance >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo number_format($total_balance, 0, ',', ' '); ?> Kč
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Celkové vklady</h5>
                    <h3 class="text-danger">
                        <?php echo number_format($totals['total_deposits'] ?? 0, 0, ',', ' '); ?> Kč
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Celkové výběry</h5>
                    <h3 class="text-success">
                        <?php echo number_format($totals['total_withdrawals'] ?? 0, 0, ',', ' '); ?> Kč
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">ROI</h5>
                    <h3 class="<?php echo $total_balance >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <?php 
                        $roi = $totals['total_deposits'] > 0 
                            ? (($total_balance / $totals['total_deposits']) * 100) 
                            : 0;
                        echo number_format($roi, 2, ',', ' '); 
                        ?> %
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Graf -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Přehled transakcí</h5>
                <div class="btn-group mb-3">
                    <button type="button" class="btn btn-outline-primary" data-period="daily">Denní</button>
                    <button type="button" class="btn btn-outline-primary" data-period="weekly">Týdenní</button>
                    <button type="button" class="btn btn-outline-primary" data-period="monthly">Měsíční</button>
                    <button type="button" class="btn btn-outline-primary active" data-period="all">Celková</button>
                </div>
            </div>
            <canvas id="transactionsChart"></canvas>
        </div>
    </div>

    <div class="row">
        <!-- Poslední transakce -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Poslední transakce</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Datum</th>
                                    <th>Kasino</th>
                                    <th>Typ</th>
                                    <th>Čstka</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_transactions as $transaction): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            if ($transaction['transaction_date']) {
                                                echo date('d.m.Y', strtotime($transaction['transaction_date']));
                                            } else {
                                                echo "-- -- ----";
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($transaction['casino']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $transaction['type'] == 'vklad' ? 'danger' : 'success'; ?>">
                                                <?php echo htmlspecialchars($transaction['type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo number_format($transaction['amount'], 0, ',', ' '); ?> Kč</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top kasina -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Top kasina</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kasino</th>
                                    <th>Bilance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($casino_stats as $stat): ?>
                                    <?php $balance = $stat['withdrawals'] - $stat['deposits']; ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['casino']); ?></td>
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
    </div>
</div>

<?php require_once "includes/transaction_modal.php"; ?>

<!-- Přidejte kód pro graf z předchozí verze -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Přidáme globální proměnnou pro graf
let currentChart = null;

// Definice dat
const dailyData = <?php echo json_encode($daily_data ?? []); ?>;
const weeklyData = <?php echo json_encode($weekly_data ?? []); ?>;
const monthlyData = <?php echo json_encode($monthly_data ?? []); ?>;
const allTimeData = <?php echo json_encode($all_time_data ?? []); ?>;

// Pro debugování - vypíšeme data do konzole
console.log('Daily data:', dailyData);
console.log('Weekly data:', weeklyData);
console.log('Monthly data:', monthlyData);
console.log('All time data:', allTimeData);

function formatDate(dateStr, period) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    switch(period) {
        case 'daily':
            return date.toLocaleDateString('cs-CZ', { day: '2-digit', month: '2-digit' });
        case 'weekly':
            return `Týden ${date.toLocaleDateString('cs-CZ', { day: '2-digit', month: '2-digit' })}`;
        case 'monthly':
        case 'all':
            return date.toLocaleDateString('cs-CZ', { month: 'short', year: 'numeric' });
        default:
            return dateStr;
    }
}

function prepareChartData(data, period) {
    if (!Array.isArray(data)) {
        console.error('Data nejsou pole:', period, data);
        return { labels: [], deposits: [], withdrawals: [] };
    }

    let cumulativeDeposits = 0;
    let cumulativeWithdrawals = 0;

    const chartData = {
        labels: data.map(item => {
            const dateStr = period === 'daily' ? item.date : 
                           period === 'weekly' ? item.week_start :
                           item.month_start;
            return formatDate(dateStr, period);
        }),
        deposits: data.map(item => {
            cumulativeDeposits += Number(item.deposits) || 0;
            return cumulativeDeposits;
        }),
        withdrawals: data.map(item => {
            cumulativeWithdrawals += Number(item.withdrawals) || 0;
            return cumulativeWithdrawals;
        })
    };

    console.log('Prepared cumulative chart data:', chartData);
    return chartData;
}

function updateChart(period) {
    console.log('Updating chart for period:', period);
    
    let data;
    switch(period) {
        case 'daily':
            data = dailyData;
            break;
        case 'weekly':
            data = weeklyData;
            break;
        case 'monthly':
            data = monthlyData;
            break;
        case 'all':
            data = allTimeData;
            break;
        default:
            data = allTimeData;
    }

    const chartData = prepareChartData(data, period);
    const ctx = document.getElementById('transactionsChart');

    if (!ctx) {
        console.error('Canvas element not found');
        return;
    }

    if (currentChart) {
        currentChart.destroy();
    }

    currentChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Celkové vklady',
                data: chartData.deposits,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.1,
                fill: true
            }, {
                label: 'Celkové výběry',
                data: chartData.withdrawals,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('cs-CZ', {
                                style: 'currency',
                                currency: 'CZK',
                                maximumFractionDigits: 0
                            }).format(value);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + 
                                new Intl.NumberFormat('cs-CZ', {
                                    style: 'currency',
                                    currency: 'CZK',
                                    maximumFractionDigits: 0
                                }).format(context.raw);
                        }
                    }
                }
            }
        }
    });
}

// Počkáme na načtení DOM
document.addEventListener('DOMContentLoaded', function() {
    // Inicializace grafu s výchozím zobrazením
    updateChart('all');

    // Přidání event listenerů na tlačítka
    document.querySelectorAll('[data-period]').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('[data-period]').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            updateChart(this.dataset.period);
        });
    });
});
</script>

<?php require_once "includes/footer.php"; ?>
