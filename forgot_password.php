<?php
require_once "init.php";
require_once "config.php";

$email_err = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    
    if (empty($email)) {
        $email_err = "Zadejte email.";
    } else {
        // Kontrola, zda email existuje
        $sql = "SELECT id, username FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch();
            
            // Generování tokenu
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Uložení tokenu do databáze
            $sql = "INSERT INTO password_resets (user_id, token, expires) VALUES (:user_id, :token, :expires)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $user['id'],
                ':token' => $token,
                ':expires' => $expires
            ]);
            
            // Odeslání emailu
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/balanc/reset_password.php?token=" . $token;
            $to = $email;
            $subject = "Reset hesla - Casino Tracker";
            $message = "Dobrý den,\n\n";
            $message .= "Obdrželi jsme žádost o reset hesla pro váš účet.\n\n";
            $message .= "Pro reset hesla klikněte na tento odkaz:\n";
            $message .= $reset_link . "\n\n";
            $message .= "Odkaz je platný 1 hodinu.\n\n";
            $message .= "Pokud jste o reset hesla nežádali, tento email ignorujte.\n\n";
            $message .= "S pozdravem,\nCasino Tracker Team";
            
            $headers = "From: noreply@casinotracker.com";
            
            if(mail($to, $subject, $message, $headers)) {
                logSecurityEvent($pdo, $user['id'], 'password_reset_request', 'success');
                $success_message = "Pokud je zadaný email registrován, poslali jsme na něj instrukce k resetování hesla.";
            } else {
                logSecurityEvent($pdo, $user['id'], 'password_reset_request', 'failed', "Email sending failed");
                $email_err = "Nepodařilo se odeslat email. Zkuste to prosím později.";
            }
        } else {
            // Pro bezpečnost zobrazíme stejnou zprávu i když email neexistuje
            $success_message = "Pokud je zadaný email registrován, poslali jsme na něj instrukce k resetování hesla.";
            logSecurityEvent($pdo, null, 'password_reset_request', 'failed', "Email not found: $email");
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
                    <h4 class="mb-0">Zapomenuté heslo</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php else: ?>
                        <p>Zadejte svůj email a my vám pošleme instrukce k resetování hesla.</p>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" required>
                                <span class="invalid-feedback"><?php echo $email_err; ?></span>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Odeslat</button>
                                <a href="login.php" class="btn btn-link">Zpět na přihlášení</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?> 