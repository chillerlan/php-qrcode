<?php

require_once '../vendor/autoload.php';

use chillerlan\QRCode\Output\QRImage;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QRConst;
use chillerlan\QRCode\QROptions;

$qrOptions = new QROptions;
$qrOptions->output = new QRImage();

$qr =  new QRCode('https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s', $qrOptions);

header('Content-type: image/png');

$im = $qr->output();
imagepng($im);
imagedestroy($im);
