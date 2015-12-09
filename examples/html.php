<?php

require_once '../vendor/autoload.php';

use chillerlan\QRCode\Output\QRString;
use chillerlan\QRCode\Output\QRStringOptions;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QRConst;
use chillerlan\QRCode\QROptions;

//---------------------------------------------------------

echo '<style>

.qrcode,
.qrcode > p,
.qrcode > p > b,
.qrcode > p > i {
	margin:0;
	padding:0;
}

/* row element */
.qrcode > p {
	height: 1.25mm;
	display: block;
}

/* column element(s) */
.qrcode > p > b, .qrcode > p > i{
	display: inline-block;
	width: 1.25mm;
	height: 1.25mm;
}

.qrcode > p > b{
	background-color: #000;
}

.qrcode > p > i{
	background-color: #fff;
}

</style>';

$qrStringOptions = new QRStringOptions;
$qrStringOptions->type = QRConst::OUTPUT_STRING_HTML;

$qrOptions = new QROptions;
$qrOptions->typeNumber = QRConst::TYPE_05;
$qrOptions->errorCorrectLevel = QRConst::ERROR_CORRECT_LEVEL_M;
$qrOptions->output = new QRString($qrStringOptions);

$qr = new QRCode('skype://callto:echo123', $qrOptions);

echo '<div class="qrcode">'.$qr->output().'</div>';
