<?php
/**
 * @created      18.11.2020
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeExamples;

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;

require_once __DIR__.'/../vendor/autoload.php';

$data = 'https://github.com';

$options = new QROptions([
	'version'             => 5,
	'eccLevel'            => EccLevel::H,
	'imageBase64'         => false,
	'addLogoSpace'        => true,
	'logoSpaceWidth'      => 13,
	'logoSpaceHeight'     => 13,
	'scale'               => 6,
	'imageTransparent'    => false,
	'drawCircularModules' => true,
	'circleRadius'        => 0.45,
	'keepAsSquare'        => [QRMatrix::M_FINDER, QRMatrix::M_FINDER_DOT],
]);

$qrcode = new QRCode($options);
$qrcode->addByteSegment($data);

header('Content-type: image/png');

$qrOutputInterface = new QRImageWithLogo($options, $qrcode->getMatrix());

// dump the output, with an additional logo
echo $qrOutputInterface->dump(null, __DIR__.'/octocat.png');
