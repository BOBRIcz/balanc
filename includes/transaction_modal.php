<?php
// Získání seznamu kasin pro select
if (!isset($casinos)) {
    $sql_casinos = "SELECT name FROM casinos ORDER BY name ASC";
    $stmt = $pdo->prepare($sql_casinos);
    $stmt->execute();
    $casinos = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<div class="modal fade" id="newTransactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nová transakce</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulář pro jednotlivou transakci -->
                <form id="singleTransactionForm" action="add_transaction.php" method="POST">
                    <div class="form-group">
                        <label>Kasino</label>
                        <select class="form-control" name="casino" required>
                            <option value="">Vyberte kasino</option>
                            <?php foreach($casinos as $casino): ?>
                                <option value="<?php echo htmlspecialchars($casino); ?>">
                                    <?php echo htmlspecialchars($casino); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vklad (Kč)</label>
                                <input type="number" class="form-control" name="deposit_amount" min="0" step="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Výběr (Kč)</label>
                                <input type="number" class="form-control" name="withdrawal_amount" min="0" step="1">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Poznámka</label>
                        <textarea class="form-control" name="note" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Přidat transakci</button>
                </form>

                <!-- Formulář pro hromadné přidání -->
                <form id="bulkTransactionForm" action="add_bulk_transactions.php" method="POST" style="display: none;">
                    <div class="form-group mb-4">
                        <label>Kasino</label>
                        <select class="form-control" name="casino" required>
                            <option value="">Vyberte kasino</option>
                            <?php foreach($casinos as $casino): ?>
                                <option value="<?php echo htmlspecialchars($casino); ?>">
                                    <?php echo htmlspecialchars($casino); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div id="bulkTransactionsContainer">
                        <div class="transaction-row mb-3 pb-3 border-bottom">
                            <div class="row align-items-end">
                                <div class="col-md-5">
                                    <div class="form-group mb-0">
                                        <label>Vklad (Kč)</label>
                                        <input type="number" class="form-control" name="deposit_amount[]" min="0" step="1">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group mb-0">
                                        <label>Výběr (Kč)</label>
                                        <input type="number" class="form-control" name="withdrawal_amount[]" min="0" step="1">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="this.closest('.transaction-row').remove()">
                                        Odstranit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-secondary mb-3" id="addRow">Přidat další transakci</button>
                    <button type="submit" class="btn btn-primary">Přidat transakce</button>
                </form>

                <div class="text-center mt-3">
                    <a href="#" id="toggleBulkForm" class="btn btn-link">Přepnout na hromadné přidání</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof jQuery !== 'undefined') {
            console.log('jQuery je načtené');
            
            // Handler pro přepínání formulářů
            document.getElementById('toggleBulkForm').addEventListener('click', function(e) {
                e.preventDefault();
                
                const singleForm = document.getElementById('singleTransactionForm');
                const bulkForm = document.getElementById('bulkTransactionForm');
                
                if (singleForm.style.display !== 'none') {
                    // Přepnutí na hromadné přidání
                    singleForm.style.display = 'none';
                    bulkForm.style.display = 'block';
                    this.textContent = 'Přepnout na jednotlivou transakci';
                } else {
                    // Přepnutí zpět na jednotlivou transakci
                    singleForm.style.display = 'block';
                    bulkForm.style.display = 'none';
                    this.textContent = 'Přepnout na hromadné přidání';
                }
            });

            // Handler pro přidání nového řádku
            document.getElementById('addRow').addEventListener('click', function() {
                const newRow = document.createElement('div');
                newRow.className = 'transaction-row mb-3 pb-3 border-bottom';
                newRow.innerHTML = `
                    <div class="row align-items-end">
                        <div class="col-md-5">
                            <div class="form-group mb-0">
                                <label>Vklad (Kč)</label>
                                <input type="number" class="form-control" name="deposit_amount[]" min="0" step="1">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-0">
                                <label>Výběr (Kč)</label>
                                <input type="number" class="form-control" name="withdrawal_amount[]" min="0" step="1">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="this.closest('.transaction-row').remove()">
                                Odstranit
                            </button>
                        </div>
                    </div>
                `;
                
                document.getElementById('bulkTransactionsContainer').appendChild(newRow);
            });
            
        } else {
            console.error('jQuery není načtené!');
        }
    });
</script> 