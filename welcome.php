<div class="welcome-section">
    <div class="container">
        <div class="row align-items-center py-5">
            <div class="col-lg-6">
                <h1 class="display-4 mb-4">
                    Vítejte v Casino Tracker 
                    <span class="badge badge-warning align-middle" style="font-size: 0.4em;">BETA</span>
                </h1>
                <p class="lead mb-4">
                    Sledujte své casino transakce jednoduše a přehledně. Mějte své finance pod kontrolou.
                </p>
                <div class="cta-buttons">
                    <a href="register.php" class="btn btn-primary btn-lg mr-3">
                        <i class="fas fa-user-plus mr-2"></i>Registrovat
                    </a>
                    <a href="login.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>Přihlásit
                    </a>
                </div>
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0">
                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-chart-line fa-2x text-primary"></i>
                        <div class="feature-content">
                            <h3>Statistiky v reálném čase</h3>
                            <p>Sledujte své vklady a výběry přehledně v grafech</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-lock fa-2x text-primary"></i>
                        <div class="feature-content">
                            <h3>Bezpečné a soukromé</h3>
                            <p>Vaše data jsou v bezpečí a pod vaší kontrolou</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-mobile-alt fa-2x text-primary"></i>
                        <div class="feature-content">
                            <h3>Responzivní design</h3>
                            <p>Přístup z jakéhokoliv zařízení</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-history fa-2x text-primary"></i>
                        <div class="feature-content">
                            <h3>Historie transakcí</h3>
                            <p>Kompletní přehled všech vašich transakcí</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.welcome-section {
    padding: 40px 0;
}

.features-list {
    background: var(--card-bg);
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}

.feature-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 25px;
}

.feature-item:last-child {
    margin-bottom: 0;
}

.feature-item i {
    margin-right: 20px;
    margin-top: 5px;
}

.feature-content h3 {
    font-size: 1.2rem;
    margin-bottom: 5px;
}

.feature-content p {
    margin-bottom: 0;
    color: var(--text-color);
    opacity: 0.8;
}

.cta-buttons {
    margin-top: 30px;
}

@media (max-width: 991.98px) {
    .features-list {
        margin-top: 40px;
    }
}
</style> 