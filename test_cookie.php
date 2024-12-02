<?php
echo "<!-- Debug: Cookie Status: " . (isset($_COOKIE['cookies_accepted']) ? 'exists' : 'not exists') . " -->";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Cookie</title>
</head>
<body>
    <h1>Test Cookie Status</h1>
    <p>Cookie 'cookies_accepted' je: <?php echo isset($_COOKIE['cookies_accepted']) ? 'nastaveno' : 'nenastaveno'; ?></p>
    <p>Všechny cookies:</p>
    <pre><?php print_r($_COOKIE); ?></pre>
</body>
</html> 