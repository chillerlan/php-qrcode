<?php
/**
 *
 * @filesource   text.php
 * @created      21.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

use chillerlan\QRCode\{QRCode, QROptions};

require_once __DIR__.'/../vendor/autoload.php';

$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';

$options = new QROptions([
	'version'      => 5,
	'outputType'   => QRCode::OUTPUT_STRING_TEXT,
	'eccLevel'     => QRCode::ECC_L,
]);

// <pre> to view it in a browser
echo '<pre style="font-size: 75%; line-height: 1;">'.(new QRCode($options))->render($data).'</pre>';





