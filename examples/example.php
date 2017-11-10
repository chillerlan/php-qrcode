<?php
/**
 * @filesource   example.php
 * @created      10.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

require_once '../vendor/autoload.php';

use chillerlan\QRCode\Output\{
	QRMarkup, QRMarkupOptions, QRImage, QRString, QRStringOptions
};
use chillerlan\QRCode\QRCode;

$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';

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

// image
echo '<img alt="qrcode" src="'.(new QRCode($data, new QRImage))->output().'" />';

// markup - svg
echo '<div>'.(new QRCode($data, new QRMarkup))->output().'</div>';

// markup - html
$outputOptions = new QRMarkupOptions;
$outputOptions->type = QRCode::OUTPUT_MARKUP_HTML;

echo '<div class="qrcode">'.(new QRCode($data, new QRMarkup($outputOptions)))->output().'</div>';

// string - json
echo '<pre>'.(new QRCode($data, new QRString))->output().'<pre>';

// string - text
$outputOptions = new QRStringOptions;
$outputOptions->type = QRCode::OUTPUT_STRING_TEXT;
$outputOptions->textDark = '🔴';
$outputOptions->textLight = '⭕';

echo '<pre>'.(new QRCode($data, new QRString($outputOptions)))->output().'</pre>';

?>
</body>
</html>
