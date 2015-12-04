<?php

require_once '../vendor/autoload.php';

use codemasher\QRCode\QRCode;
use codemasher\QRCode\QRConst;

$starttime = microtime(true);

// google authenticator
// https://chart.googleapis.com/chart?chs=200x200&chld=M%7C0&cht=qr&chl=otpauth%3A%2F%2Ftotp%2Ftest%3Fsecret%3DB3JX4VCVJDVNXNZ5%26issuer%3Dchillerlan.net

$qrcode = new QRCode('otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', QRConst::ERROR_CORRECT_LEVEL_M, 5);

for($row = 0; $row < $qrcode->moduleCount; $row++){
	for($col = 0; $col < $qrcode->moduleCount; $col++){
		echo (bool)$qrcode->modules[$row][$col] ? '#' : ' ';
	}
	echo PHP_EOL;
}

echo 'QRCode '.round((microtime(true)-$starttime), 5).PHP_EOL;
