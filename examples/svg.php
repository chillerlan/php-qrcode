<?php
/**
 * @created      21.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;

require_once __DIR__.'/../vendor/autoload.php';

$gzip = true;

$options = new QROptions([
	'version'        => 7,
	'outputType'     => QRCode::OUTPUT_MARKUP_SVG,
	'imageBase64'    => false,
	'eccLevel'       => QRCode::ECC_L,
#	'svgViewBoxSize' => 530,
	'addQuietzone'   => true,
	'svgOpacity'     => 1.0,
	'svgDefs'        => '
		<linearGradient id="g2">
			<stop offset="0%" stop-color="#39F" />
			<stop offset="100%" stop-color="#F3F" />
		</linearGradient>
		<linearGradient id="g1">
			<stop offset="0%" stop-color="#F3F" />
			<stop offset="100%" stop-color="#39F" />
		</linearGradient>
		<style>rect{shape-rendering:crispEdges}</style>',
	'moduleValues'   => [
		// finder
		QRMatrix::M_FINDER_DARK    => 'url(#g1)', // dark (true)
		QRMatrix::M_FINDER_DOT     => 'url(#g1)',
		QRMatrix::M_FINDER         => '#fff',     // light (false)
		// alignment
		QRMatrix::M_ALIGNMENT_DARK => 'url(#g1)',
		QRMatrix::M_ALIGNMENT      => '#fff',
		// timing
		QRMatrix::M_TIMING_DARK    => 'url(#g1)',
		QRMatrix::M_TIMING         => '#fff',
		// format
		QRMatrix::M_FORMAT_DARK    => 'url(#g1)',
		QRMatrix::M_FORMAT         => '#fff',
		// version
		QRMatrix::M_VERSION_DARK   => 'url(#g1)',
		QRMatrix::M_VERSION        => '#fff',
		// data
		QRMatrix::M_DATA_DARK      => 'url(#g2)',
		QRMatrix::M_DATA           => '#fff',
		// darkmodule
		QRMatrix::M_DARKMODULE     => 'url(#g1)',
		// separator
		QRMatrix::M_SEPARATOR      => '#fff',
		// quietzone
		QRMatrix::M_QUIETZONE      => '#fff',
	],
]);


$qrcode = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

header('Content-type: image/svg+xml');

if($gzip === true){
	header('Vary: Accept-Encoding');
	header('Content-Encoding: gzip');
	$qrcode = gzencode($qrcode , 9);
}

echo $qrcode;
