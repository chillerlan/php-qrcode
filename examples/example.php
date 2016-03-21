<?php
/**
 * @filesource   example.php
 * @created      10.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

require_once '../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\Output\QRImage;
use chillerlan\QRCode\Output\QRString;
use chillerlan\QRCode\Output\QRStringOptions;

$data = 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net';
#$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';
#$data = 'skype://echo123';

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title>QRCode test</title>
	<style>
		body{
			margin: 0;
			padding: 0;
		}

		div.qrcode{
			margin: 0 5px;
		}

		/* row element */
		div.qrcode > p {
			margin: 0;
			padding: 0;
			height: 5px;
		}

		/* column element(s) */
		div.qrcode > p > b,
		div.qrcode > p > i {
			display: inline-block;
			width: 5px;
			height: 5px;
		}

		div.qrcode > p > b {
			background-color: #000;
		}

		div.qrcode > p > i {
			background-color: #fff;
		}
	</style>
</head>
<body>
<?php

echo '<img class="qrcode" alt="qrcode" src="'.(new QRCode($data, new QRImage))->output().'" />';
echo '<div class="qrcode">'.(new QRCode($data, new QRString))->output().'</div>';

$qrStringOptions = new QRStringOptions;
$qrStringOptions->type = QRCode::OUTPUT_STRING_TEXT;

echo '<pre class="qrcode">'.(new QRCode($data, new QRString($qrStringOptions)))->output().'</pre>';

?>
</body>
</html>
