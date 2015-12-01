<?php

require_once '../vendor/autoload.php';

use codemasher\QRCode\QRCode;
use codemasher\QRCode\QRConst;

$qrcode = new QRCode;
$qr = $qrcode->getMinimumQRCode('イメージ作成(引数:サイズ,マージン', QRConst::ERROR_CORRECT_LEVEL_L);


header('Content-type: text/plain');

$m = $qr->getModuleCount();

for($row = 0; $row < $m; $row++){
	for($col = 0; $col < $m; $col++){
		echo $qr->isDark($row, $col) ? '#' : ' ';
	}
	echo PHP_EOL;
}

