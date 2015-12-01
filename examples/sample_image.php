<?php

require_once '../vendor/autoload.php';

use codemasher\QRCode\QRCode;
use codemasher\QRCode\QRConst;

$qrcode = new QRCode;
$qr = $qrcode->getMinimumQRCode('QRコード', QRConst::ERROR_CORRECT_LEVEL_L);

// イメージ作成(引数:サイズ,マージン)
$im = $qr->createImage(2, 4);

header('Content-type: image/png');
imagepng($im);

imagedestroy($im);

