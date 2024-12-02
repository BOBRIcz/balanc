<?php
require_once "header.php";
require_once "../config.php";

// Kontrola admin práv je už v header.php, není třeba ji opakovat

try {
    // Jednoduchý výpis uživatelů bez stránkování
    $sql = "SELECT * FROM users ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Chyba databáze: " . $e->getMessage());
}
?>

<div class="container-fluid mt-4 admin-panel">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Správa uživatelů</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Uživatel</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Akce</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php if($user['is_admin']): ?>
                                                <span class="badge badge-danger">Admin</span>
                                            <?php endif; ?>
                                            <?php if($user['is_banned']): ?>
                                                <span class="badge badge-warning">Blokován</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> Upravit
                                            </button>
                                            <?php if(!$user['is_admin'] || $_SESSION['id'] != $user['id']): ?>
                                                <button class="btn btn-sm btn-outline-<?php echo $user['is_banned'] ? 'success' : 'warning'; ?>">
                                                    <i class="fas fa-<?php echo $user['is_banned'] ? 'unlock' : 'ban'; ?>"></i>
                                                    <?php echo $user['is_banned'] ? 'Odblokovat' : 'Blokovat'; ?>
                                                </button>
                                            <?php endif; ?>
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

<?php require_once "../includes/footer.php"; ?> 