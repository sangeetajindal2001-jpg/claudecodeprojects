(function () {
    var root = document.documentElement;

    // Dark mode toggle: remembers the choice; otherwise follows the system setting
    var toggle = document.getElementById('theme-toggle');
    if (toggle) {
        toggle.addEventListener('click', function () {
            var current = root.getAttribute('data-theme') ||
                (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            var next = current === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) {}
        });
    }

    // Load a new captcha image without reloading the page
    var refresh = document.getElementById('captcha-refresh');
    var img = document.getElementById('captcha-img');
    if (refresh && img) {
        refresh.addEventListener('click', function () {
            img.src = 'captcha.php?r=' + Date.now();
            var input = document.getElementById('captcha');
            if (input) { input.value = ''; input.focus(); }
        });
    }
})();
