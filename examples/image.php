<?php
/**
 *
 * @filesource   image.php
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeExamples;

use chillerlan\QRCode\{QRCode, QROptions};

require_once __DIR__.'/../vendor/autoload.php';

$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';

$options = new QROptions([
	'version'      => 10,
	'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
	'eccLevel'     => QRCode::ECC_H,
	'scale'        => 5,
	'imageBase64'  => false,
	'moduleValues' => [
		// finder
		1536 => [0, 63, 255], // dark (true)
		6    => [255, 255, 255], // light (false), white is the transparency color and is enabled by default
		5632 => [241, 28, 163], // finder dot, dark (true)
		// alignment
		2560 => [255, 0, 255],
		10   => [255, 255, 255],
		// timing
		3072 => [255, 0, 0],
		12   => [255, 255, 255],
		// format
		3584 => [67, 99, 84],
		14   => [255, 255, 255],
		// version
		4096 => [62, 174, 190],
		16   => [255, 255, 255],
		// data
		1024 => [0, 0, 0],
		4    => [255, 255, 255],
		// darkmodule
		512  => [0, 0, 0],
		// separator
		8    => [255, 255, 255],
		// quietzone
		18   => [255, 255, 255],
		// logo (requires a call to QRMatrix::setLogoSpace())
		20    => [255, 255, 255],
	],
]);

header('Content-type: image/png');

echo (new QRCode($options))->render($data);





