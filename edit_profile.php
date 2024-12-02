<?php
require_once "init.php";
require_once "config.php";
require_once "includes/functions.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$update_success = $update_error = "";

// Zpracování formuláře
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validace emailu
    $email = trim($_POST["email"]);
    if(empty($email)){
        $email_err = "Zadejte email.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $email_err = "Neplatný formát emailu.";
    } else {
        $sql = "SELECT id FROM users WHERE email = :email AND id != :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email, ':id' => $user_id]);
        if($stmt->rowCount() > 0){
            $email_err = "Tento email je již použitý.";
        }
    }

    $display_name = trim($_POST["display_name"] ?? "");
    $bio = trim($_POST["bio"] ?? "");

    // Pokud nejsou žádné chyby, aktualizujeme profil
    if(empty($email_err)){
        try {
            $sql = "UPDATE users 
                    SET email = :email, 
                        display_name = :display_name, 
                        bio = :bio 
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                ':email' => $email,
                ':display_name' => $display_name,
                ':bio' => $bio,
                ':id' => $user_id
            ]);

            if($result){
                logSecurityEvent($pdo, $user_id, 'profile_update', 'success', 
                    "Changed fields: " . implode(", ", array_keys($_POST))
                );
                $_SESSION['success_message'] = "Profil byl úspěšně aktualizován.";
                header("location: profile.php");
                exit;
            } else {
                $update_error = "Něco se pokazilo. Zkuste to prosím znovu.";
            }
        } catch(PDOException $e) {
            $update_error = "Chyba při aktualizaci profilu.";
        }
    }
}

// Načtení aktuálních dat uživatele
$sql = "SELECT username, email, display_name, bio FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Nyní načteme header
require_once "includes/header.php";
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Upravit profil</h2>
                    <a href="profile.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Zpět na profil
                    </a>
                </div>
                <div class="card-body">
                    <?php if(!empty($update_error)): ?>
                        <div class="alert alert-danger"><?php echo $update_error; ?></div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label>Uživatelské jméno</label>
                            <input type="text" class="form-control" value="<?php echo h($user_data['username']); ?>" disabled>
                            <small class="form-text text-muted">Uživatelské jméno nelze změnit</small>
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                            value="<?php echo h($user_data['email']); ?>" required>
                            <?php if(!empty($email_err)): ?>
                                <div class="invalid-feedback"><?php echo $email_err; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>Zobrazované jméno</label>
                            <input type="text" name="display_name" class="form-control" value="<?php echo h($user_data['display_name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Biografie</label>
                            <textarea name="bio" class="form-control" rows="3"><?php echo h($user_data['bio']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Uložit změny</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?> 