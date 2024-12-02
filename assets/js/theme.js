document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const body = document.body;
    
    if (!darkModeToggle) {
        console.error('Dark mode toggle not found!');
        return;
    }
    
    // Načtení preferovaného tématu
    const savedTheme = localStorage.getItem('theme') || 'light';
    console.log('Initial theme:', savedTheme);
    
    // Nastavení počátečního stavu
    body.setAttribute('data-theme', savedTheme);
    darkModeToggle.checked = savedTheme === 'dark';
    
    // Přepínání dark mode
    darkModeToggle.addEventListener('change', function() {
        const newTheme = this.checked ? 'dark' : 'light';
        console.log('Changing theme to:', newTheme);
        
        body.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });
}); 