<?php
require_once "config.php";

$username = $password = $email = $confirm_password = "";
$username_err = $password_err = $email_err = $confirm_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validace uživatelského jména
    if (empty(trim($_POST["username"]))) {
        $username_err = "Zadejte uživatelské jméno.";
    } else {
        $sql = "SELECT id FROM users WHERE username = :username";
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $param_username = trim($_POST["username"]);
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $username_err = "Toto uživatelské jméno je již zabrané.";
                } else {
                    $username = trim($_POST["username"]);
                }
            }
        }
    }
    
    // Validace emailu
    if (empty(trim($_POST["email"]))) {
        $email_err = "Zadejte email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Neplatný formát emailu.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Validace hesla
    if (empty(trim($_POST["password"]))) {
        $password_err = "Zadejte heslo.";     
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Heslo musí mít alespoň 6 znaků.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validace potvrzení hesla
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Potvrďte heslo.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Hesla se neshodují.";
        }
    }
    
    // Kontrola chyb před vložením do databáze
    if (empty($username_err) && empty($password_err) && empty($email_err) && empty($confirm_password_err)) {
        $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
        
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            
            $param_username = $username;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            
            if ($stmt->execute()) {
                logSecurityEvent($pdo, $pdo->lastInsertId(), 'registration', 'success');
                ob_start();
                header("location: login.php");
                ob_end_flush();
                exit();
            } else {
                logSecurityEvent($pdo, null, 'registration_attempt', 'failed', 
                    "Error: " . implode(", ", $errors)
                );
                echo "Něco se pokazilo. Zkuste to prosím později.";
            }
        }
    }
}

require_once "includes/header.php";  // Přidejte tento řádek před HTML obsah
?>

<div class="auth-container">
    <div class="auth-box">
        <h2 class="text-center mb-4">
            <i class="fas fa-user-plus mr-2"></i>Registrace
        </h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Uživatelské jméno</label>
                <input type="text" name="username" 
                       class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" 
                       class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo $email; ?>">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>

            <div class="form-group">
                <label>Heslo</label>
                <input type="password" name="password" 
                       class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>

            <div class="form-group">
                <label>Potvrzení hesla</label>
                <input type="password" name="confirm_password" 
                       class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Registrovat se</button>
            </div>
            <p class="text-center">Již máte účet? <a href="login.php">Přihlaste se zde</a></p>
        </form>
    </div>
</div>

<div class="theme-switch-wrapper">
    <label class="theme-switch" for="checkbox">
        <input type="checkbox" id="checkbox" <?php echo (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'checked' : ''; ?>>
        <div class="slider round">
            <i class="fas fa-sun"></i>
            <i class="fas fa-moon"></i>
        </div>
    </label>
</div>

<script src="assets/js/theme.js"></script>

<?php require_once "includes/footer.php"; ?>    