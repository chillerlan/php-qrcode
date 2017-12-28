<?php
/**
 *
 * @filesource   html.php
 * @created      21.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeExamples;

use chillerlan\QRCode\{QRCode, QROptions};

require_once '../vendor/autoload.php';

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
			margin: 5em;
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
	<div class="qrcode">
<?php

	$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';

	$options = new QROptions([
		'version' => 5,
		'outputType' => QRCode::OUTPUT_MARKUP_HTML,
		'eccLevel' => QRCode::ECC_L,
		'moduleValues' => [
			// finder
			1536 => '#A71111', // dark (true)
			6    => '#FFBFBF', // light (false)
			// alignment
			2560 => '#A70364',
			10   => '#FFC9C9',
			// timing
			3072 => '#98005D',
			12   => '#FFB8E9',
			// format
			3584 => '#003804',
			14   => '#00FB12',
			// version
			4096 => '#650098',
			16   => '#E0B8FF',
			// data
			1024 => '#4A6000',
			4    => '#ECF9BE',
			// darkmodule
			512  => '#080063',
			// separator
			8    => '#AFBFBF',
			// quietzone
			18   => '#FFFFFF',
		],
	]);

	echo (new QRCode($options))->render($data);

?>
	</div>
</body>
</html>



