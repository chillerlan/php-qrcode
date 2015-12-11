<?php

require_once '../vendor/autoload.php';

use chillerlan\QRCode\Output\QRImage;
use chillerlan\QRCode\Output\QRImageOptions;
use chillerlan\QRCode\QRCode;

$qrImageOptions = new QRImageOptions;
$qrImageOptions->pixelSize = 10;
#$qrImageOptions->cachefile = 'example_image.png';

$im = (new QRCode('https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s', new QRImage($qrImageOptions)))->output();

echo '<img src="'.$im.'" />';
