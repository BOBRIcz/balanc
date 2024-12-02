<?php
require_once "header.php";
?>

<div class="container-fluid mt-4 admin-panel">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>Admin Panel</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-users"></i> Správa uživatelů
                                    </h5>
                                    <p class="card-text">Správa uživatelských účtů a oprávnění</p>
                                    <a href="users.php" class="btn btn-primary">Přejít na správu uživatelů</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-shield-alt"></i> Bezpečnostní logy
                                    </h5>
                                    <p class="card-text">Přehled bezpečnostních událostí</p>
                                    <a href="security_logs.php" class="btn btn-primary">Zobrazit logy</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?> 