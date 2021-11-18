<?php
/**
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeExamples;

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\EccLevel;

require_once __DIR__.'/../vendor/autoload.php';

$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';

// invoke the QROutputInterface manually
$options = new QROptions([
	'version'      => 5,
	'eccLevel'     => EccLevel::L,
]);

$qrcode = new QRCode($options);
$qrcode->addByteSegment($data);

$qrOutputInterface = new MyCustomOutput($options, $qrcode->getMatrix());

var_dump($qrOutputInterface->dump());


// or just
$options = new QROptions([
	'version'         => 5,
	'eccLevel'        => EccLevel::L,
	'outputType'      => QRCode::OUTPUT_CUSTOM,
	'outputInterface' => MyCustomOutput::class,
]);

var_dump((new QRCode($options))->render($data));
