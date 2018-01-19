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

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

require_once __DIR__.'/../vendor/autoload.php';

$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';

$options = new QROptions([
	'version'      => 5,
	'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
	'eccLevel'     => QRCode::ECC_L,
	'scale'        => 10,
	'imageBase64'  => false,
	'moduleValues' => [
		// finder
		1536 => [255, 0, 0], // dark (true)
		6    => [255, 255, 255], // light (false), white is the transparency color and is enabled by default
		// alignment
		2560 => [255, 0, 0],
		10   => [255, 255, 255],
		// timing
		3072 => [0, 0, 0],
		12   => [200,  200, 200],
		// format
		3584 => [0, 0, 0],
		14   => [200,  200, 200],
		// version
		4096 => [0, 0, 0],
		16   => [200,  200, 200],
		// data
		1024 => [0, 0, 150],
		4    => [255, 255, 255],
		// darkmodule
		512  => [0, 0, 0],
		// separator
		8    => [200,  200, 200],
		// quietzone
		18   => [255, 255, 255],
	],
]);

header('Content-type: image/png');

echo (new QRCode($options))->render($data);





