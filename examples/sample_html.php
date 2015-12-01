<?php

require_once '../vendor/autoload.php';

use codemasher\QRCode\QRCode;
use codemasher\QRCode\QRConst;
//---------------------------------------------------------

$qrcode = new QRCode;


print('<h4>明示的に型番を指定</h4>');

// エラー訂正レベルを設定
// QR_ERROR_CORRECT_LEVEL_L : 7%
// QR_ERROR_CORRECT_LEVEL_M : 15%
// QR_ERROR_CORRECT_LEVEL_Q : 25%
// QR_ERROR_CORRECT_LEVEL_H : 30%
$qrcode->setErrorCorrectLevel(QRConst::ERROR_CORRECT_LEVEL_L);

// 型番(大きさ)を設定
// 1～10
$qrcode->setTypeNumber(3);

// データ(文字列※)を設定
// ※日本語はSJIS
$qrcode->addData('QRコード');

// QRコードを作成
$qrcode->make();

// HTML出力
$qrcode->printHTML();

//---------------------------------------------------------
print('<h4>型番自動</h4>');

// 型番が最小となるQRコードを作成
$qr = $qrcode->getMinimumQRCode('QRコード', QRConst::ERROR_CORRECT_LEVEL_L);

// HTML出力
$qr->printHTML();