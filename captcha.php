<?php
// captcha.php: genera una imagen captcha simple
session_start();

// Generar texto aleatorio
$caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$captcha_texto = '';
for ($i = 0; $i < 5; $i++) {
    $captcha_texto .= $caracteres[rand(0, strlen($caracteres) - 1)];
}

$_SESSION['captcha'] = $captcha_texto;

// Crear imagen
$ancho = 120;
$alto = 40;
$imagen = imagecreatetruecolor($ancho, $alto);

$blanco = imagecolorallocate($imagen, 255, 255, 255);
$negro = imagecolorallocate($imagen, 0, 0, 0);
$gris = imagecolorallocate($imagen, 128, 128, 128);

imagefilledrectangle($imagen, 0, 0, $ancho, $alto, $blanco);

// Agregar texto
$fuente = __DIR__ . '/arial.ttf';
if (file_exists($fuente)) {
    imagettftext($imagen, 20, 0, 10, 30, $negro, $fuente, $captcha_texto);
} else {
    imagestring($imagen, 5, 20, 10, $captcha_texto, $negro);
}

// Líneas de ruido
for ($i = 0; $i < 5; $i++) {
    imageline($imagen, rand(0, $ancho), rand(0, $alto), rand(0, $ancho), rand(0, $alto), $gris);
}

// Salida de imagen
header('Content-Type: image/png');
imagepng($imagen);
imagedestroy($imagen);
