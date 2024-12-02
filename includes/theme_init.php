<!-- Theme initialization script - must be in head -->
<script>
    // Načteme theme z localStorage hned na začátku
    let theme = localStorage.getItem('theme');
    if (theme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    } else if (theme === null && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        // Pokud není nastaveno téma a systém používá dark mode
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
    }
</script> 