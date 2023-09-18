<?php
/**
 * GdImage output example
 *
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;

require_once __DIR__.'/../vendor/autoload.php';

$options = new QROptions;

$options->version             = 7;
$options->outputType          = QROutputInterface::GDIMAGE_PNG;
$options->scale               = 20;
$options->outputBase64        = false;
$options->bgColor             = [200, 150, 200];
$options->imageTransparent    = true;
#$options->transparencyColor   = [233, 233, 233];
$options->drawCircularModules = true;
$options->drawLightModules    = true;
$options->circleRadius        = 0.4;
$options->keepAsSquare        = [
	QRMatrix::M_FINDER_DARK,
	QRMatrix::M_FINDER_DOT,
	QRMatrix::M_ALIGNMENT_DARK,
];
$options->moduleValues        = [
	// finder
	QRMatrix::M_FINDER_DARK    => [0, 63, 255], // dark (true)
	QRMatrix::M_FINDER_DOT     => [0, 63, 255], // finder dot, dark (true)
	QRMatrix::M_FINDER         => [233, 233, 233], // light (false), white is the transparency color and is enabled by default
	// alignment
	QRMatrix::M_ALIGNMENT_DARK => [255, 0, 255],
	QRMatrix::M_ALIGNMENT      => [233, 233, 233],
	// timing
	QRMatrix::M_TIMING_DARK    => [255, 0, 0],
	QRMatrix::M_TIMING         => [233, 233, 233],
	// format
	QRMatrix::M_FORMAT_DARK    => [67, 159, 84],
	QRMatrix::M_FORMAT         => [233, 233, 233],
	// version
	QRMatrix::M_VERSION_DARK   => [62, 174, 190],
	QRMatrix::M_VERSION        => [233, 233, 233],
	// data
	QRMatrix::M_DATA_DARK      => [0, 0, 0],
	QRMatrix::M_DATA           => [233, 233, 233],
	// darkmodule
	QRMatrix::M_DARKMODULE     => [0, 0, 0],
	// separator
	QRMatrix::M_SEPARATOR      => [233, 233, 233],
	// quietzone
	QRMatrix::M_QUIETZONE      => [233, 233, 233],
	// logo (requires a call to QRMatrix::setLogoSpace()), see QRImageWithLogo
	QRMatrix::M_LOGO           => [233, 233, 233],
];


$out = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

header('Content-type: image/png');

echo $out;

exit;
