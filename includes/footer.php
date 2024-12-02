    <footer class="footer">
        <div class="container">
            <span>© <?php echo date("Y"); ?> Casino Tracker</span>
        </div>
    </footer>

    <!-- Nejdřív načteme jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Pak ostatní knihovny -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const cookieModal = document.getElementById('cookie-modal');
        const acceptButton = document.getElementById('accept-cookies');
        
        if (cookieModal) {
            // Přidáme třídu pro zakázání scrollování
            document.body.classList.add('cookie-modal-open');
        }
        
        if (cookieModal && acceptButton) {
            acceptButton.addEventListener('click', function() {
                // Okamžitě skryjeme modal
                cookieModal.style.opacity = '0';
                document.body.classList.remove('cookie-modal-open');
                setTimeout(() => cookieModal.remove(), 300);

                // Odešleme požadavek na server pro nastavení cookie
                fetch('set_cookie.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=accept_cookies'
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Nepodařilo se nastavit cookie');
                    }
                });
            });
        }
    });
    </script>
    <script>
    // Dark mode toggle
    const toggleSwitch = document.querySelector('#checkbox');
    const currentTheme = localStorage.getItem('theme');

    // Synchronizujeme stav přepínače s aktuálním tématem
    if (currentTheme === 'dark') {
        toggleSwitch.checked = true;
    }

    function switchTheme(e) {
        if (e.target.checked) {
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
        }    
    }

    toggleSwitch.addEventListener('change', switchTheme, false);
    </script>
</body>
</html> 