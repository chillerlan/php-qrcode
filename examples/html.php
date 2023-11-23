<?php
/**
 * @created      21.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;

require_once '../vendor/autoload.php';

$options = new QROptions([
	'version'      => 5,
	'outputType'   => QRCode::OUTPUT_MARKUP_HTML,
	'cssClass'     => 'qrcode',
	'eccLevel'     => QRCode::ECC_L,
	'moduleValues' => [
		// finder
		QRMatrix::M_FINDER_DARK    => '#A71111', // dark (true)
		QRMatrix::M_FINDER_DOT     => '#A71111',
		QRMatrix::M_FINDER         => '#FFBFBF', // light (false)
		// alignment
		QRMatrix::M_ALIGNMENT_DARK => '#A70364',
		QRMatrix::M_ALIGNMENT      => '#FFC9C9',
		// timing
		QRMatrix::M_TIMING_DARK    => '#98005D',
		QRMatrix::M_TIMING         => '#FFB8E9',
		// format
		QRMatrix::M_FORMAT_DARK    => '#003804',
		QRMatrix::M_FORMAT         => '#00FB12',
		// version
		QRMatrix::M_VERSION_DARK   => '#650098',
		QRMatrix::M_VERSION        => '#E0B8FF',
		// data
		QRMatrix::M_DATA_DARK      => '#4A6000',
		QRMatrix::M_DATA           => '#ECF9BE',
		// darkmodule
		QRMatrix::M_DARKMODULE     => '#080063',
		// separator
		QRMatrix::M_SEPARATOR      => '#AFBFBF',
		// quietzone
		QRMatrix::M_QUIETZONE      => '#FFFFFF',
	],
]);


header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title>QRCode test</title>
	<style>
		body{
			margin: 1em;
			padding: 0;
		}

		div.qrcode{
			margin: 0;
			padding: 0;
		}

		/* rows */
		div.qrcode > div {
			margin: 0;
			padding: 0;
			height: 10px;
		}

		/* modules */
		div.qrcode > div > span {
			display: inline-block;
			width: 10px;
			height: 10px;
		}

		div.qrcode > div > span {
			background-color: #ccc;
		}
	</style>
</head>
<body>
<?php

	echo (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

?>
</body>
</html>
