<?php
// The code is created by captcha.php and kept in the session.
// Each code works once: it is removed after every check, right or wrong.
function verify_captcha($input)
{
    $expected = $_SESSION['captcha_code'] ?? '';
    unset($_SESSION['captcha_code']);

    $input = strtoupper(trim((string) $input));
    return $expected !== '' && $input !== '' && hash_equals($expected, $input);
}

function captcha_field()
{
    return '
        <label for="captcha">Enter the characters shown</label>
        <div class="captcha">
            <img src="captcha.php?r=' . bin2hex(random_bytes(4)) . '" alt="Captcha image" width="150" height="50" id="captcha-img">
            <button type="button" class="icon-btn" id="captcha-refresh" title="Show a new image" aria-label="Show a new captcha image">&#x21bb;</button>
        </div>
        <input type="text" id="captcha" name="captcha" autocomplete="off" maxlength="5" required>';
}
