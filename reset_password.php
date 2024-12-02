<?php
require_once "init.php";
require_once "config.php";

$token = $_GET['token'] ?? '';
$password_err = "";
$success = false;

// Kontrola platnosti tokenu
if (empty($token)) {
    header("location: login.php");
    exit;
}

$sql = "SELECT pr.*, u.username 
        FROM password_resets pr 
        JOIN users u ON pr.user_id = u.id 
        WHERE pr.token = :token AND pr.used = 0 AND pr.expires > NOW()";
$stmt = $pdo->prepare($sql);
$stmt->execute([':token' => $token]);

if ($stmt->rowCount() == 0) {
    $token_error = "Neplatný nebo expirovaný odkaz pro reset hesla.";
} else {
    $reset_data = $stmt->fetch();
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $password = trim($_POST["password"]);
        $confirm_password = trim($_POST["confirm_password"]);
        
        // Validace hesla
        if (empty($password)) {
            $password_err = "Zadejte heslo.";
        } elseif (strlen($password) < 8) {
            $password_err = "Heslo musí mít alespoň 8 znaků.";
        } elseif ($password != $confirm_password) {
            $password_err = "Hesla se neshodují.";
        }
        
        if (empty($password_err)) {
            try {
                $pdo->beginTransaction();
                
                // Aktualizace hesla
                $sql = "UPDATE users SET password = :password WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':password' => password_hash($password, PASSWORD_DEFAULT),
                    ':id' => $reset_data['user_id']
                ]);
                
                // Označení tokenu jako použitého
                $sql = "UPDATE password_resets SET used = 1 WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $reset_data['id']]);
                
                $pdo->commit();
                logSecurityEvent($pdo, $reset_data['user_id'], 'password_reset', 'success');
                $success = true;
                
            } catch (Exception $e) {
                $pdo->rollBack();
                logSecurityEvent($pdo, $reset_data['user_id'], 'password_reset', 'failed', $e->getMessage());
                $password_err = "Chyba při změně hesla. Zkuste to prosím znovu.";
            }
        }
    }
}

require_once "includes/header.php";
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Reset hesla</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($token_error)): ?>
                        <div class="alert alert-danger"><?php echo $token_error; ?></div>
                        <div class="text-center">
                            <a href="forgot_password.php" class="btn btn-primary">Požádat o nový reset hesla</a>
                        </div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success">
                            Vaše heslo bylo úspěšně změněno. Nyní se můžete přihlásit.
                        </div>
                        <div class="text-center">
                            <a href="login.php" class="btn btn-primary">Přejít na přihlášení</a>
                        </div>
                    <?php else: ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?token=' . htmlspecialchars($token); ?>" method="post">
                            <div class="form-group">
                                <label>Nové heslo</label>
                                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Potvrzení hesla</label>
                                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
                                <span class="invalid-feedback"><?php echo $password_err; ?></span>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Změnit heslo</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?> 