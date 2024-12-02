<?php
require_once "config.php";
require_once "includes/header.php";

// Kontrola přihlášení
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];

// Celkové statistiky
$sql_totals = "SELECT 
    SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END) as total_deposits,
    SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) as total_withdrawals,
    COUNT(CASE WHEN type = 'vklad' THEN 1 END) as deposit_count,
    COUNT(CASE WHEN type = 'výběr' THEN 1 END) as withdrawal_count
    FROM transactions 
    WHERE user_id = :user_id";

// Statistiky podle kasina
$sql_by_casino = "SELECT 
    casino,
    SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END) as deposits,
    SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) as withdrawals,
    COUNT(*) as transaction_count
    FROM transactions 
    WHERE user_id = :user_id 
    GROUP BY casino 
    ORDER BY (SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) - 
             SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END)) DESC";

// SQL dotazy pro data grafu - upravené pro zahrnutí všech transakcí
$sql_daily = "SELECT 
    COALESCE(DATE(transaction_date), CURRENT_DATE) as date,
    SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END) as deposits,
    SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) as withdrawals
    FROM transactions
    WHERE user_id = :user_id 
    GROUP BY COALESCE(DATE(transaction_date), CURRENT_DATE)
    ORDER BY date DESC
    LIMIT 30";

$sql_weekly = "SELECT 
    COALESCE(DATE(DATE_SUB(transaction_date, INTERVAL WEEKDAY(transaction_date) DAY)), 
             DATE(DATE_SUB(CURRENT_DATE, INTERVAL WEEKDAY(CURRENT_DATE) DAY))) as week_start,
    SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END) as deposits,
    SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) as withdrawals
    FROM transactions
    WHERE user_id = :user_id 
    GROUP BY week_start
    ORDER BY week_start DESC
    LIMIT 12";

$sql_monthly = "SELECT 
    COALESCE(DATE_FORMAT(transaction_date, '%Y-%m-01'), 
             DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')) as month_start,
    SUM(CASE WHEN type = 'vklad' THEN amount ELSE 0 END) as deposits,
    SUM(CASE WHEN type = 'výběr' THEN amount ELSE 0 END) as withdrawals
    FROM transactions
    WHERE user_id = :user_id 
    GROUP BY month_start
    ORDER BY month_start DESC
    LIMIT 12";

try {
    // Získání celkových statistik
    $stmt = $pdo->prepare($sql_totals);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $totals = $stmt->fetch(PDO::FETCH_ASSOC);

    // Získání statistik podle kasina
    $stmt = $pdo->prepare($sql_by_casino);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $casino_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Získání dat pro grafy
    $stmt = $pdo->prepare($sql_daily);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $daily_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare($sql_weekly);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $weekly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare($sql_monthly);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $monthly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    echo "Chyba: " . $e->getMessage();
}

$total_balance = ($totals['total_withdrawals'] ?? 0) - ($totals['total_deposits'] ?? 0);
?>

<div class="container mt-4">
    <h2>Statistiky</h2>

    <!-- Celkové statistiky -->
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
                    <small class="text-muted">Počet vkladů: <?php echo $totals['deposit_count'] ?? 0; ?></small>
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
                    <small class="text-muted">Počet výběrů: <?php echo $totals['withdrawal_count'] ?? 0; ?></small>
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

    <div class="row">
        <!-- Přepínací graf -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Přehled transakcí</h5>
                    <div class="btn-group mb-3">
                        <button type="button" class="btn btn-outline-primary" data-period="daily">Denní</button>
                        <button type="button" class="btn btn-outline-primary" data-period="weekly">Týdenní</button>
                        <button type="button" class="btn btn-outline-primary active" data-period="monthly">Měsíční</button>
                    </div>
                    <canvas id="transactionsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Statistiky podle kasina -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Statistiky podle kasina</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kasino</th>
                                    <th>Bilance</th>
                                    <th>ROI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($casino_stats as $stat): ?>
                                    <?php 
                                    $balance = $stat['withdrawals'] - $stat['deposits'];
                                    $roi = $stat['deposits'] > 0 ? (($balance / $stat['deposits']) * 100) : 0;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['casino']); ?></td>
                                        <td class="<?php echo $balance >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo number_format($balance, 0, ',', ' '); ?> Kč
                                        </td>
                                        <td class="<?php echo $roi >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo number_format($roi, 2, ',', ' '); ?> %
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

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Top kasina podle výdělku</h5>
                    <canvas id="casinoProfitChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const dailyData = <?php echo json_encode($daily_data); ?>;
const weeklyData = <?php echo json_encode($weekly_data); ?>;
const monthlyData = <?php echo json_encode($monthly_data); ?>;

let currentChart = null;

document.addEventListener('DOMContentLoaded', function() {
    updateChart('monthly');

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

function updateChart(period) {
    const data = period === 'monthly' ? monthlyData : 
                 period === 'weekly' ? weeklyData : 
                 dailyData;
    
    const chartData = prepareChartData(data, period);

    if (currentChart) {
        currentChart.destroy();
    }

    const ctx = document.getElementById('transactionsChart').getContext('2d');
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

function prepareChartData(data, period) {
    return {
        labels: data.map(item => formatDate(period === 'monthly' ? item.month_start : 
                                          period === 'weekly' ? item.week_start : 
                                          item.date, period)),
        deposits: data.map(item => item.deposits),
        withdrawals: data.map(item => item.withdrawals)
    };
}

function formatDate(dateStr, period) {
    const date = new Date(dateStr);
    switch(period) {
        case 'daily':
            return date.toLocaleDateString('cs-CZ', { day: '2-digit', month: '2-digit' });
        case 'weekly':
            return `Týden ${date.toLocaleDateString('cs-CZ', { day: '2-digit', month: '2-digit' })}`;
        case 'monthly':
            return date.toLocaleDateString('cs-CZ', { month: 'short', year: 'numeric' });
    }
}

const casinoData = <?php 
    // Vypočítáme celkový zisk ze všech kasin
    $total_profit = array_reduce($casino_stats, function($carry, $stat) {
        $profit = $stat['withdrawals'] - $stat['deposits'];
        return $carry + ($profit > 0 ? $profit : 0); // Počítáme pouze kladné zisky
    }, 0);

    // Minimální procentuální podíl pro zobrazení kasina (10%)
    $min_percentage = 10;

    // Seřadíme kasina podle zisku
    $casino_profit_data = array_map(function($stat) {
        return [
            'casino' => $stat['casino'],
            'profit' => $stat['withdrawals'] - $stat['deposits']
        ];
    }, $casino_stats);

    // Seřadíme podle zisku sestupně
    usort($casino_profit_data, function($a, $b) {
        return $b['profit'] - $a['profit'];
    });

    // Filtrujeme kasina podle procentuálního podílu
    $significant_casinos = [];
    $others_profit = 0;

    foreach ($casino_profit_data as $casino) {
        if ($casino['profit'] > 0) { // Započítáváme pouze zisková kasina
            $percentage = ($casino['profit'] / $total_profit) * 100;
            
            if ($percentage >= $min_percentage) {
                $significant_casinos[] = $casino;
            } else {
                $others_profit += $casino['profit'];
            }
        }
    }

    // Přidáme "Ostatní" pokud existují nějaká menší kasina
    if ($others_profit > 0) {
        $significant_casinos[] = [
            'casino' => 'Ostatní',
            'profit' => $others_profit
        ];
    }

    echo json_encode($significant_casinos);
?>;

// Vytvoříme koláčový graf
const profitCtx = document.getElementById('casinoProfitChart').getContext('2d');
new Chart(profitCtx, {
    type: 'pie',
    data: {
        labels: casinoData.map(item => item.casino),
        datasets: [{
            data: casinoData.map(item => item.profit),
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(128, 128, 128, 0.8)' // Šedá pro "Ostatní"
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(128, 128, 128, 1)' // Šedá pro "Ostatní"
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'right'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const value = context.raw;
                        const percentage = ((value / context.dataset.data.reduce((a, b) => a + b, 0)) * 100).toFixed(1);
                        return `${context.label}: ${new Intl.NumberFormat('cs-CZ', {
                            style: 'currency',
                            currency: 'CZK',
                            maximumFractionDigits: 0
                        }).format(value)} (${percentage}%)`;
                    }
                }
            }
        }
    }
});
</script>

<?php require_once "includes/footer.php"; ?> 