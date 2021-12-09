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

require_once __DIR__.'/../vendor/autoload.php';

$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';

$options = new QROptions([
	'version'          => 7,
	'eccLevel'         => EccLevel::H,
	'imageBase64'      => false,
	'addLogoSpace'     => true,
	'logoSpaceWidth'   => 13,
	'logoSpaceHeight'  => 13,
	'scale'            => 5,
	'imageTransparent' => false,
]);

$qrcode = new QRCode($options);
$qrcode->addByteSegment($data);

header('Content-type: image/png');

$qrOutputInterface = new QRImageWithLogo($options, $qrcode->getMatrix());

// dump the output, with an additional logo
echo $qrOutputInterface->dump(null, __DIR__.'/octocat.png');
