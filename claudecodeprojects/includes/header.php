<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Login App') ?></title>
    <script>
        // Apply the saved theme before the page draws, so it doesn't flash
        try {
            var saved = localStorage.getItem('theme');
            if (saved === 'dark' || saved === 'light') document.documentElement.setAttribute('data-theme', saved);
        } catch (e) {}
    </script>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<button type="button" class="theme-toggle" id="theme-toggle" aria-label="Switch between dark and light mode" title="Dark / light mode">
    <span class="icon-moon" aria-hidden="true">&#x263E;</span>
    <span class="icon-sun" aria-hidden="true">&#x2600;</span>
</button>
<main class="container">
