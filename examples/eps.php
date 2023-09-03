<?php
/**
 * EPS output example
 *
 * @created      10.05.2022
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2022 Smiley
 * @license      MIT
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;

require_once __DIR__.'/../vendor/autoload.php';

$options = new QROptions;

$options->version          = 7;
$options->outputType       = QROutputInterface::EPS;
$options->scale            = 5;
$options->drawLightModules = false;
// colors can be specified either as [R, G, B] or [C, M, Y, K] (0-255)
$options->bgColor          = [222, 222, 222];
$options->moduleValues     = [
	// finder
	QRMatrix::M_FINDER_DARK    => [0, 63, 255],    // dark (true)
	QRMatrix::M_FINDER_DOT     => [0, 63, 255],    // finder dot, dark (true)
	QRMatrix::M_FINDER         => [233, 233, 233], // light (false)
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
	// logo space (requires a call to QRMatrix::setLogoSpace()), see imageWithLogo example
	QRMatrix::M_LOGO           => [233, 233, 233],
];


$out = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ', __DIR__.'/qrcode.eps');

if(php_sapi_name() !== 'cli'){
	// if viewed in the browser, we should push it as file download as EPS isn't usually supported
	header('Content-type: application/postscript');
	header('Content-Disposition: filename="qrcode.eps"');
}

echo $out;

exit;
