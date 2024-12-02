<?php
ob_start();

require_once "init.php";

// Pokud je uživatel již přihlášen, přesměrujeme ho
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}

require_once "config.php";

$username = $password = "";
$username_err = $password_err = $login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validace uživatelského jména
    if(empty(trim($_POST["username"]))){
        $username_err = "Prosím zadejte uživatelské jméno.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Validace hesla
    if(empty(trim($_POST["password"]))){
        $password_err = "Prosím zadejte heslo.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Ověření přihlašovacích údajů
    if(empty($username_err) && empty($password_err)){
        // Upravený SQL dotaz pro kontrolu banu
        $sql = "SELECT id, username, password, is_banned, ban_reason, banned_until, is_admin 
                FROM users WHERE username = :username";
        
        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $param_username = trim($_POST["username"]);
            
            if($stmt->execute()){
                if($stmt->rowCount() == 1){
                    if($row = $stmt->fetch()){
                        $id = $row["id"];
                        $username = $row["username"];
                        $hashed_password = $row["password"];
                        
                        // Kontrola banu
                        if($row["is_banned"]){
                            $ban_message = "Váš účet byl zablokován.";
                            
                            // Přidáme důvod banu, pokud existuje
                            if(!empty($row["ban_reason"])){
                                $ban_message .= " Důvod: " . htmlspecialchars($row["ban_reason"]);
                            }
                            
                            // Přidáme informaci o době trvání banu, pokud existuje
                            if(!empty($row["banned_until"])){
                                $banned_until = new DateTime($row["banned_until"]);
                                $now = new DateTime();
                                
                                if($banned_until > $now){
                                    $ban_message .= " Ban vyprší: " . $banned_until->format('d.m.Y H:i');
                                } else {
                                    // Ban vypršel, můžeme ho automaticky odstranit
                                    $sql_unban = "UPDATE users SET is_banned = FALSE, ban_reason = NULL, banned_until = NULL WHERE id = :id";
                                    $stmt_unban = $pdo->prepare($sql_unban);
                                    $stmt_unban->execute([":id" => $id]);
                                    
                                    // Pokračujeme v přihlášení
                                    goto process_login;
                                }
                            }
                            
                            $login_err = $ban_message;
                            goto end_login;
                        }
                        
                        process_login:
                        if(password_verify($password, $hashed_password)){
                            // Aktualizace času posledního přihlášení
                            $update_login = "UPDATE users SET last_login = NOW() WHERE id = :id";
                            $stmt = $pdo->prepare($update_login);
                            $stmt->execute([':id' => $id]);
                            
                            // Inicializace session
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["is_admin"] = (bool)$row["is_admin"];
                            $_SESSION["cookie_notice_accepted"] = true;
                            
                            // Zpracování "Zapamatovat si mě"
                            if(isset($_POST["remember"]) && $_POST["remember"] == "on") {
                                $token = bin2hex(random_bytes(32));
                                
                                $sql = "UPDATE users SET remember_token = :token WHERE id = :id";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([
                                    ":token" => $token,
                                    ":id" => $id
                                ]);
                                
                                setcookie("remember_user", $username, time() + (86400 * 30), "/");
                                setcookie("remember_token", $token, time() + (86400 * 30), "/");
                            }
                            
                            // Vyčistíme buffer a přesměrujeme
                            ob_end_clean();
                            header("location: index.php");
                            exit();
                        } else{
                            $login_err = "Neplatné uživatelské jméno nebo heslo.";
                        }
                    }
                } else{
                    $login_err = "Neplatné uživatelské jméno nebo heslo.";
                }
            }
        }
        end_login:
    }
}
?>
 
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přihlášení - Casino Tracker</title>
    
    <?php require_once "includes/theme_init.php"; ?>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2 class="text-center mb-4">
                <i class="fas fa-sign-in-alt mr-2"></i>Přihlášení
            </h2>
            
            <?php if(!empty($login_err)): ?>
                <div class="alert alert-danger"><?php echo $login_err; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Uživatelské jméno</label>
                    <input type="text" name="username" 
                           class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo $username; ?>">
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>    
                <div class="form-group">
                    <label>Heslo</label>
                    <input type="password" name="password" 
                           class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                        <label class="custom-control-label" for="remember">Zapamatovat si mě</label>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Přihlásit se</button>
                    <a href="forgot_password.php" class="btn btn-link">Zapomenuté heslo?</a>
                </div>
                <p class="text-center">Nemáte účet? <a href="register.php">Zaregistrujte se zde</a></p>
            </form>
        </div>
    </div>

    <?php require_once "includes/footer.php"; ?>
</body>
</html>