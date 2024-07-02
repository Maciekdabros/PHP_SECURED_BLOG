<?php
session_start();
$captchaWidth = 120;
$captchaHeight = 40;
$fontFile = 'fonts/arial.ttf';
$fontSize = 20;

$captchaCode = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
$_SESSION['captcha'] = $captchaCode;

$image = imagecreatetruecolor($captchaWidth, $captchaHeight);
$bgColor = imagecolorallocate($image, 255, 255, 255);
$textColor = imagecolorallocate($image, 0, 0, 0);

imagefilledrectangle($image, 0, 0, $captchaWidth, $captchaHeight, $bgColor);
imagettftext($image, $fontSize, 0, 10, 30, $textColor, $fontFile, $captchaCode);

header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>