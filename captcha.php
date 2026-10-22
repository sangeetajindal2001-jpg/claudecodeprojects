<?php
require __DIR__ . '/includes/auth.php';

// Letters and digits that are hard to confuse (no 0/O, 1/I/L)
$alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
$code = '';
for ($i = 0; $i < 5; $i++) {
    $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
}
$_SESSION['captcha_code'] = $code;

// Draw the text small, then scale it up so it looks less like plain text
$small = imagecreatetruecolor(75, 25);
imagefill($small, 0, 0, imagecolorallocate($small, 240, 240, 245));
for ($i = 0; $i < 5; $i++) {
    $color = imagecolorallocate($small, random_int(20, 100), random_int(20, 100), random_int(60, 140));
    imagestring($small, 5, 6 + $i * 13, random_int(1, 8), $code[$i], $color);
}

$img = imagecreatetruecolor(150, 50);
imagecopyresampled($img, $small, 0, 0, 0, 0, 150, 50, 75, 25);

// Noise: lines and dots
for ($i = 0; $i < 6; $i++) {
    $color = imagecolorallocate($img, random_int(120, 200), random_int(120, 200), random_int(120, 200));
    imageline($img, random_int(0, 150), random_int(0, 50), random_int(0, 150), random_int(0, 50), $color);
}
for ($i = 0; $i < 300; $i++) {
    $color = imagecolorallocate($img, random_int(100, 220), random_int(100, 220), random_int(100, 220));
    imagesetpixel($img, random_int(0, 149), random_int(0, 49), $color);
}

header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate');
imagepng($img);
imagedestroy($small);
imagedestroy($img);
