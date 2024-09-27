<?php
/**
 * intervention/image output example
 *
 * @created      04.05.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRInterventionImage;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

require_once __DIR__.'/../vendor/autoload.php';

$options = new QROptions;

$options->version             = 7;
$options->outputInterface     = QRInterventionImage::class;
$options->scale               = 20;
$options->outputBase64        = false;
$options->bgColor             = '#cccccc';
$options->imageTransparent    = false;
$options->transparencyColor   = '#cccccc';
$options->drawLightModules    = true;
$options->drawCircularModules = true;
$options->circleRadius        = 0.4;
$options->keepAsSquare        = [
	QRMatrix::M_FINDER_DARK,
	QRMatrix::M_FINDER_DOT,
	QRMatrix::M_ALIGNMENT_DARK,
];
$options->moduleValues        = [
	// finder
	QRMatrix::M_FINDER_DARK    => '#A71111', // dark (true)
	QRMatrix::M_FINDER_DOT     => '#A71111', // finder dot, dark (true)
	QRMatrix::M_FINDER         => '#FFBFBF', // light (false)
	// alignment
	QRMatrix::M_ALIGNMENT_DARK => '#A70364',
	QRMatrix::M_ALIGNMENT      => '#FFC9C9',
	// timing
	QRMatrix::M_TIMING_DARK    => '#98005D',
	QRMatrix::M_TIMING         => '#FFB8E9',
	// format
	QRMatrix::M_FORMAT_DARK    => '#003804',
	QRMatrix::M_FORMAT         => '#CCFB12',
	// version
	QRMatrix::M_VERSION_DARK   => '#650098',
	QRMatrix::M_VERSION        => '#E0B8FF',
	// data
	QRMatrix::M_DATA_DARK      => '#4A6000',
	QRMatrix::M_DATA           => '#ECF9BE',
	// darkmodule
	QRMatrix::M_DARKMODULE     => '#080063',
	// separator
	QRMatrix::M_SEPARATOR      => '#DDDDDD',
	// quietzone
	QRMatrix::M_QUIETZONE      => '#DDDDDD',
];

$qrcode = new QRCode($options);
$qrcode->addByteSegment('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

$qrOutputInterface = new QRInterventionImage($options, $qrcode->getQRMatrix());
// set a different driver
$qrOutputInterface->setDriver(new GdDriver);

$out = $qrOutputInterface->dump();

header('Content-type: image/png');

echo $out;

exit;
