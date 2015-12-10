<?php

require_once '../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRString;
use chillerlan\QRCode\Output\QRStringOptions;

$starttime = microtime(true);

$qrOptions = new QROptions;
$qrOptions->typeNumber = QRCode::TYPE_05;
$qrOptions->errorCorrectLevel = QRCode::ERROR_CORRECT_LEVEL_M;

$qrStringOptions = new QRStringOptions;
$qrStringOptions->type = QRCode::OUTPUT_STRING_TEXT;
$qrStringOptions->textDark  = '#';
$qrStringOptions->textLight = ' ';

// google authenticator
// https://chart.googleapis.com/chart?chs=200x200&chld=M%7C0&cht=qr&chl=otpauth%3A%2F%2Ftotp%2Ftest%3Fsecret%3DB3JX4VCVJDVNXNZ5%26issuer%3Dchillerlan.net
$qrcode = new QRCode('otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', new QRString($qrStringOptions), $qrOptions);
echo '<pre>'.$qrcode->output().'</pre>';

echo '<pre>'.print_r($qrcode->getRawData(), true).'</pre>';

echo 'QRCode: '.round((microtime(true)-$starttime), 5).PHP_EOL;
