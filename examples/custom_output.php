<?php
/**
 *
 * @filesource   custom_output.php
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeExamples;

use chillerlan\QRCode\{QRCode, QROptions};

require_once __DIR__.'/../vendor/autoload.php';

$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';

// invoke the QROutputInterface manually
$options = new QROptions([
	'version'      => 5,
	'eccLevel'     => QRCode::ECC_L,
]);

$qrOutputInterface = new MyCustomOutput($options, (new QRCode($options))->getMatrix($data));

var_dump($qrOutputInterface->dump());


// or just
$options = new QROptions([
	'version'         => 5,
	'eccLevel'        => QRCode::ECC_L,
	'outputType'      => QRCode::OUTPUT_CUSTOM,
	'outputInterface' => MyCustomOutput::class,
]);

var_dump((new QRCode($options))->render($data));
