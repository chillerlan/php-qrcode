<?php
/**
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;

require_once __DIR__.'/../vendor/autoload.php';

$options = new QROptions([
	'version'             => 7,
	'outputType'          => QROutputInterface::GDIMAGE_PNG,
	'eccLevel'            => EccLevel::L,
	'scale'               => 10,
	'imageBase64'         => false,
	'bgColor'             => [200, 200, 200],
	'imageTransparent'    => false,
	'drawCircularModules' => true,
	'circleRadius'        => 0.4,
	'keepAsSquare'        => [QRMatrix::M_FINDER|QRMatrix::IS_DARK, QRMatrix::M_FINDER_DOT, QRMatrix::M_ALIGNMENT|QRMatrix::IS_DARK],
	'moduleValues'        => [
		// finder
		QRMatrix::M_FINDER | QRMatrix::IS_DARK     => [0, 63, 255], // dark (true)
		QRMatrix::M_FINDER                         => [233, 233, 233], // light (false), white is the transparency color and is enabled by default
		QRMatrix::M_FINDER_DOT | QRMatrix::IS_DARK => [0, 63, 255], // finder dot, dark (true)
		// alignment
		QRMatrix::M_ALIGNMENT | QRMatrix::IS_DARK  => [255, 0, 255],
		QRMatrix::M_ALIGNMENT                      => [233, 233, 233],
		// timing
		QRMatrix::M_TIMING | QRMatrix::IS_DARK     => [255, 0, 0],
		QRMatrix::M_TIMING                         => [233, 233, 233],
		// format
		QRMatrix::M_FORMAT | QRMatrix::IS_DARK     => [67, 159, 84],
		QRMatrix::M_FORMAT                         => [233, 233, 233],
		// version
		QRMatrix::M_VERSION | QRMatrix::IS_DARK    => [62, 174, 190],
		QRMatrix::M_VERSION                        => [233, 233, 233],
		// data
		QRMatrix::M_DATA | QRMatrix::IS_DARK       => [0, 0, 0],
		QRMatrix::M_DATA                           => [233, 233, 233],
		// darkmodule
		QRMatrix::M_DARKMODULE | QRMatrix::IS_DARK => [0, 0, 0],
		// separator
		QRMatrix::M_SEPARATOR                      => [233, 233, 233],
		// quietzone
		QRMatrix::M_QUIETZONE                      => [233, 233, 233],
		// logo (requires a call to QRMatrix::setLogoSpace()), see QRImageWithLogo
		QRMatrix::M_LOGO                           => [233, 233, 233],
	],
]);


try{
	$im = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
}
catch(Throwable $e){
	exit($e->getMessage());
}

header('Content-type: image/png');

echo $im;

exit;
