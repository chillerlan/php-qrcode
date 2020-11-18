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
	'version'      => 7,
	'outputType'   => QRCode::OUTPUT_IMAGICK,
	'eccLevel'     => QRCode::ECC_L,
	'scale'        => 5,
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
		8    => '#DDDDDD',
		// quietzone
		18   => '#DDDDDD',
	],
]);

header('Content-type: image/png');

echo (new QRCode($options))->render($data);





