<?php

require_once '../vendor/autoload.php';

use codemasher\QRCode\QRCode;
use codemasher\QRCode\QRConst;

// google authenticator
$qr = new QRCode('otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', QRConst::ERROR_CORRECT_LEVEL_L);

header('Content-type: image/png');

$im = $qr->createImage(5, 5);
imagepng($im);
imagedestroy($im);
