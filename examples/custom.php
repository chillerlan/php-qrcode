<?php

require_once '../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QRConst;

$starttime = microtime(true);

$qrcode = (new QRCode)
	->setErrorCorrectLevel(QRConst::ERROR_CORRECT_LEVEL_M)
	->setQRType(QRConst::TYPE_05)
;

// google authenticator
// https://chart.googleapis.com/chart?chs=200x200&chld=M%7C0&cht=qr&chl=otpauth%3A%2F%2Ftotp%2Ftest%3Fsecret%3DB3JX4VCVJDVNXNZ5%26issuer%3Dchillerlan.net
$qrcode
	->setData('otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net')
	->getRawData()
;

for($row = 0; $row < $qrcode->pixelCount; $row++){
	for($col = 0; $col < $qrcode->pixelCount; $col++){
		echo $qrcode->matrix[$row][$col] ? '*' : ' ';
	}
	echo PHP_EOL;
}

echo 'QRCode: '.round((microtime(true)-$starttime), 5).PHP_EOL;
