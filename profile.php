<?php
require_once "init.php";
require_once "config.php";
require_once "includes/functions.php";
require_once "includes/header.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];

// Na začátku souboru
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Načtení dat uživatele včetně statistik a public_profile
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM transactions t WHERE t.user_id = u.id) as total_transactions,
        (SELECT SUM(amount) FROM transactions t WHERE t.user_id = u.id AND t.type = 'vklad') as total_deposits,
        (SELECT SUM(amount) FROM transactions t WHERE t.user_id = u.id AND t.type = 'výběr') as total_withdrawals,
        u.public_profile
        FROM users u 
        WHERE u.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Na začátku souboru po načtení dat
error_log("User data loaded: " . print_r($user_data, true));

// Výpočet celkového zisku/ztráty
$total_profit = ($user_data['total_withdrawals'] ?? 0) - ($user_data['total_deposits'] ?? 0);
?>

<div class="container mt-4">
    <?php if(isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); 
            ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-user-circle fa-5x mb-3"></i>
                    <h4><?php echo h($user_data['display_name'] ?: $user_data['username']); ?></h4>
                    <p class="text-muted"><?php echo h($user_data['email']); ?></p>
                    <?php if(!empty($user_data['bio'])): ?>
                        <p class="mt-3"><?php echo h($user_data['bio']); ?></p>
                    <?php endif; ?>
                    <a href="edit_profile.php" class="btn btn-primary mt-3">
                        <i class="fas fa-edit mr-2"></i>Upravit profil
                    </a>

                    <div class="visibility-switch">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="profileVisibility" 
                                   <?php echo $user_data['public_profile'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="profileVisibility">
                                <span id="visibilityStatus">
                                    <?php echo $user_data['public_profile'] ? 'Veřejný profil' : 'Soukromý profil'; ?>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Statistiky</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <h6 class="text-muted">Celkem transakcí</h6>
                            <h4><?php echo number_format($user_data['total_transactions'], 0, ',', ' '); ?></h4>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <h6 class="text-muted">Celkem vloženo</h6>
                            <h4 class="text-danger"><?php echo formatMoney($user_data['total_deposits'] ?? 0); ?></h4>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <h6 class="text-muted">Celkem vybráno</h6>
                            <h4 class="text-success"><?php echo formatMoney($user_data['total_withdrawals'] ?? 0); ?></h4>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <h5>Celkový zisk/ztráta</h5>
                        <h3 class="<?php echo $total_profit >= 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo formatMoney($total_profit); ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?> 

<script>
document.getElementById('profileVisibility').addEventListener('change', function() {
    const statusText = document.getElementById('visibilityStatus');
    const checkbox = this;
    
    const formData = new FormData();
    formData.append('public', this.checked ? 1 : 0);
    formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

    fetch('toggle_profile_visibility.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusText.textContent = checkbox.checked ? 'Veřejný profil' : 'Soukromý profil';
            
            const toast = `
                <div class="position-fixed bottom-0 right-0 p-3" style="z-index: 5; right: 0; bottom: 0;">
                    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2000">
                        <div class="toast-body bg-success text-white">
                            Nastavení profilu bylo aktualizováno
                        </div>
                    </div>
                </div>`;
            
            document.body.insertAdjacentHTML('beforeend', toast);
            $('.toast').toast('show');
            
            $('.toast').on('hidden.bs.toast', function () {
                $(this).parent().remove();
            });
        } else {
            checkbox.checked = !checkbox.checked;
            statusText.textContent = checkbox.checked ? 'Veřejný profil' : 'Soukromý profil';
            alert('Nepodařilo se aktualizovat nastavení profilu');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        checkbox.checked = !checkbox.checked;
        statusText.textContent = checkbox.checked ? 'Veřejný profil' : 'Soukromý profil';
        alert('Chyba při aktualizaci nastavení profilu');
    });
});
</script> 

<style>
.visibility-switch {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 1rem;
    padding: 0.5rem;
}

.visibility-switch .custom-control-label {
    padding-left: 0.5rem;
    cursor: pointer;
}

.visibility-switch .custom-control {
    padding-left: 2.5rem; /* Zvětšíme odsazení pro checkbox */
}

.visibility-switch .custom-switch .custom-control-label::before {
    left: -2.5rem; /* Posuneme switch doleva */
}

.visibility-switch .custom-switch .custom-control-label::after {
    left: calc(-2.5rem + 2px); /* Posuneme kolečko switche */
}
</style> 