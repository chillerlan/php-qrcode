<?php

require_once '../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QRConst;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRString;
use chillerlan\QRCode\Output\QRStringOptions;

$starttime = microtime(true);

$qrStringOptions = new QRStringOptions;
$qrStringOptions->type = QRConst::OUTPUT_STRING_TEXT;
$qrStringOptions->textDark  = '+';
$qrStringOptions->textLight = '-';

$qrOptions = new QROptions;
$qrOptions->typeNumber = QRConst::TYPE_05;
$qrOptions->errorCorrectLevel = QRConst::ERROR_CORRECT_LEVEL_M;
$qrOptions->output = new QRString($qrStringOptions);

// google authenticator
// https://chart.googleapis.com/chart?chs=200x200&chld=M%7C0&cht=qr&chl=otpauth%3A%2F%2Ftotp%2Ftest%3Fsecret%3DB3JX4VCVJDVNXNZ5%26issuer%3Dchillerlan.net
$qrcode = new QRCode('otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=buildwars.net', $qrOptions);
var_dump($qrcode->getRawData());

$qrcode->setData('otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', $qrOptions);
var_dump($qrcode->output());

echo 'QRCode: '.round((microtime(true)-$starttime), 5).PHP_EOL;
