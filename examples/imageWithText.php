<?php
/**
 * example for additional text
 * @link https://github.com/chillerlan/php-qrcode/issues/35
 *
 * @filesource   imageWithText.php
 * @created      22.06.2019
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2019 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeExamples;

use chillerlan\QRCode\{QRCode, QROptions};

require_once __DIR__.'/../vendor/autoload.php';

$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';

$options = new QROptions([
	'version'      => 7,
	'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
	'scale'        => 3,
	'imageBase64'  => false,
]);

header('Content-type: image/png');

$qrOutputInterface = new QRImageWithText($options, (new QRCode($options))->getMatrix($data));

// dump the output, with additional text
echo $qrOutputInterface->dump(null, 'example text');
