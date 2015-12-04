<?php

require_once '../vendor/autoload.php';

use codemasher\QRCode\QRCode;
use codemasher\QRCode\QRConst;
//---------------------------------------------------------

echo '<style>
.qrcode{
	border-style:none;
	border-collapse:collapse;
	margin:0;
	padding:0;
}

.dark, .light{
	margin:0;
	padding:0;
	width: 1.25mm;
	height: 1.25mm;
}

.dark{
	background-color: #000;
}

.light{
	background-color: #fff;
}
</style>';

// google authenticator
$qr = new QRCode('otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', QRConst::ERROR_CORRECT_LEVEL_M);
echo $qr->printHTML();
