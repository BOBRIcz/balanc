<?php
require_once "config.php";
require_once "includes/header.php";

// Kontrola přihlášení
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];

// Získání seznamu kasin pro filtr
$sql_casinos = "SELECT DISTINCT casino FROM transactions WHERE user_id = :user_id ORDER BY casino";
$stmt = $pdo->prepare($sql_casinos);
$stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
$stmt->execute();
$casinos = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Nastavení filtrů
$where_conditions = ["user_id = :user_id"];
$params = [":user_id" => $user_id];

if (!empty($_GET['casino'])) {
    $where_conditions[] = "casino = :casino";
    $params[':casino'] = $_GET['casino'];
}

if (!empty($_GET['type'])) {
    $where_conditions[] = "type = :type";
    $params[':type'] = $_GET['type'];
}

if (!empty($_GET['date_from'])) {
    $where_conditions[] = "DATE(transaction_date) >= :date_from";
    $params[':date_from'] = $_GET['date_from'];
}

if (!empty($_GET['date_to'])) {
    $where_conditions[] = "DATE(transaction_date) <= :date_to";
    $params[':date_to'] = $_GET['date_to'];
}

// Sestavení SQL dotazu
$where_clause = implode(" AND ", $where_conditions);
$sql = "SELECT * FROM transactions 
        WHERE {$where_clause} 
        ORDER BY transaction_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Výpočet součtů
$total_deposits = 0;
$total_withdrawals = 0;
foreach ($transactions as $transaction) {
    if ($transaction['type'] == 'vklad') {
        $total_deposits += $transaction['amount'];
    } else {
        $total_withdrawals += $transaction['amount'];
    }
}
$balance = $total_withdrawals - $total_deposits;

// Nastavení stránkování
$items_per_page = 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Upravený SQL dotaz pro počet záznamů
$count_sql = "SELECT COUNT(*) FROM transactions WHERE {$where_clause}";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $items_per_page);

// Upravený SQL dotaz pro stránkování
$sql = "SELECT * FROM transactions 
        WHERE {$where_clause} 
        ORDER BY transaction_date DESC 
        LIMIT :offset, :items_per_page";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':items_per_page', $items_per_page, PDO::PARAM_INT);
foreach($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h2>Přehled transakcí</h2>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#newTransactionModal">
                <i class="fas fa-plus mr-2"></i>Nová transakce
            </button>
        </div>
    </div>

    <!-- Filtry -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Kasino</label>
                        <select name="casino" class="form-control">
                            <option value="">Všechna kasina</option>
                            <?php foreach($casinos as $casino): ?>
                                <option value="<?php echo htmlspecialchars($casino); ?>" 
                                    <?php echo (!empty($_GET['casino']) && $_GET['casino'] == $casino) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($casino); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Typ</label>
                        <select name="type" class="form-control">
                            <option value="">Vše</option>
                            <option value="vklad" <?php echo (!empty($_GET['type']) && $_GET['type'] == 'vklad') ? 'selected' : ''; ?>>Vklad</option>
                            <option value="výběr" <?php echo (!empty($_GET['type']) && $_GET['type'] == 'výběr') ? 'selected' : ''; ?>>Výběr</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Od data</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo $_GET['date_from'] ?? ''; ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Do data</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo $_GET['date_to'] ?? ''; ?>">
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary mr-2">Filtrovat</button>
                        <a href="transactions.php" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Souhrn -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Celkové vklady</h5>
                    <h3 class="text-danger"><?php echo number_format($total_deposits, 0, ',', ' '); ?> Kč</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Celkové výběry</h5>
                    <h3 class="text-success"><?php echo number_format($total_withdrawals, 0, ',', ' '); ?> Kč</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Bilance</h5>
                    <h3 class="<?php echo $balance >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo number_format($balance, 0, ',', ' '); ?> Kč
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabulka transakcí -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Kasino</th>
                            <th>Typ</th>
                            <th>Částka</th>
                            <th>Poznámka</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($transactions as $transaction): ?>
                            <tr>
                                <td>
                                    <?php 
                                    echo $transaction['transaction_date'] 
                                        ? date('d.m.Y', strtotime($transaction['transaction_date'])) 
                                        : "-- -- ----"; 
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($transaction['casino']); ?></td>
                                <td>
                                    <span class="transaction-type <?php echo $transaction['type'] === 'vklad' ? 'deposit' : 'withdrawal'; ?>">
                                        <?php echo htmlspecialchars($transaction['type']); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($transaction['amount'], 0, ',', ' '); ?> Kč</td>
                                <td><?php echo htmlspecialchars($transaction['description'] ?? ''); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editTransaction(<?php echo $transaction['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteTransaction(<?php echo $transaction['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Stránkování -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Stránkování transakcí" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Tlačítko Předchozí -->
                    <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php 
                            $_GET['page'] = $current_page - 1;
                            echo '?' . http_build_query(array_filter($_GET));
                        ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    
                    <!-- Čísla stránek -->
                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    if ($start_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?';
                        $_GET['page'] = 1;
                        echo http_build_query(array_filter($_GET));
                        echo '">1</a></li>';
                        if ($start_page > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        echo '<li class="page-item ';
                        if ($i == $current_page) echo 'active';
                        echo '"><a class="page-link" href="?';
                        $_GET['page'] = $i;
                        echo http_build_query(array_filter($_GET));
                        echo '">' . $i . '</a></li>';
                    }
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="?';
                        $_GET['page'] = $total_pages;
                        echo http_build_query(array_filter($_GET));
                        echo '">' . $total_pages . '</a></li>';
                    }
                    ?>
                    
                    <!-- Tlačítko Další -->
                    <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php 
                            $_GET['page'] = $current_page + 1;
                            echo '?' . http_build_query(array_filter($_GET));
                        ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal pro novou transakci (stejný jako v dashboard.php) -->
<?php require_once "includes/transaction_modal.php"; ?>

<script>
const casinos = <?php echo json_encode($casinos); ?>;

function editTransaction(id) {
    // Načtení dat transakce
    fetch(`edit_transaction.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const transaction = data.data;
                
                // Vytvoření modálního okna pro editaci
                const modal = `
                <div class="modal fade" id="editTransactionModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Upravit transakci</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="editTransactionForm">
                                    <input type="hidden" name="id" value="${transaction.id}">
                                    <div class="form-group">
                                        <label>Kasino</label>
                                        <select class="form-control" name="casino" required>
                                            ${casinos.map(casino => `
                                                <option value="${casino}" ${casino === transaction.casino ? 'selected' : ''}>
                                                    ${casino}
                                                </option>
                                            `).join('')}
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Typ</label>
                                        <select class="form-control" name="type" required>
                                            <option value="vklad" ${transaction.type === 'vklad' ? 'selected' : ''}>Vklad</option>
                                            <option value="výběr" ${transaction.type === 'výběr' ? 'selected' : ''}>Výběr</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Částka (Kč)</label>
                                        <input type="number" class="form-control" name="amount" value="${transaction.amount}" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Datum</label>
                                        <input type="datetime-local" class="form-control" name="transaction_date" 
                                               value="${transaction.transaction_date.replace(' ', 'T')}" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Poznámka</label>
                                        <textarea class="form-control" name="note">${transaction.description || ''}</textarea>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Zrušit</button>
                                <button type="button" class="btn btn-primary" onclick="saveTransaction()">Uložit změny</button>
                            </div>
                        </div>
                    </div>
                </div>`;
                
                // Přidání modálního okna do stránky
                document.body.insertAdjacentHTML('beforeend', modal);
                $('#editTransactionModal').modal('show');
                
                // Odstranění modálního okna po zavření
                $('#editTransactionModal').on('hidden.bs.modal', function() {
                    this.remove();
                });
            } else {
                alert('Chyba při načítání transakce: ' + data.message);
            }
        });
}

function saveTransaction() {
    const form = document.getElementById('editTransactionForm');
    const formData = new FormData(form);

    fetch('edit_transaction.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#editTransactionModal').modal('hide');
            location.reload(); // Obnovení stránky pro zobrazení změn
        } else {
            alert('Chyba při ukládání změn: ' + data.message);
        }
    });
}

function deleteTransaction(id) {
    if (confirm('Opravdu chcete smazat tuto transakci?')) {
        const formData = new FormData();
        formData.append('id', id);

        fetch('delete_transaction.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Obnovení stránky pro zobrazení změn
            } else {
                alert('Chyba při mazání transakce: ' + data.message);
            }
        });
    }
}

// Inicializace Select2 pro filtry
$(document).ready(function() {
    $('select[name="casino"]').select2({
        placeholder: 'Vyberte kasino',
        allowClear: true
    });
});
</script>

<?php require_once "includes/footer.php"; ?>