<?php
/**
 *
 * @filesource   svg.php
 * @created      21.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeExamples;

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Common\EccLevel;

require_once __DIR__.'/../vendor/autoload.php';

$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';
$gzip = true;

$options = new QROptions([
	'version'        => 7,
	'outputType'     => QRCode::OUTPUT_MARKUP_SVG,
	'imageBase64'    => false,
	'eccLevel'       => EccLevel::L,
	'svgViewBoxSize' => 530,
	'addQuietzone'   => true,
	'cssClass'       => 'my-css-class',
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
	'moduleValues' => [
		// finder
		QRMatrix::M_FINDER | QRMatrix::IS_DARK     => 'url(#g1)', // dark (true)
		QRMatrix::M_FINDER                         => '#fff',     // light (false)
		QRMatrix::M_FINDER_DOT | QRMatrix::IS_DARK => 'url(#g2)', // finder dot, dark (true)
		// alignment
		QRMatrix::M_ALIGNMENT | QRMatrix::IS_DARK  => 'url(#g1)',
		QRMatrix::M_ALIGNMENT                      => '#fff',
		// timing
		QRMatrix::M_TIMING | QRMatrix::IS_DARK     => 'url(#g1)',
		QRMatrix::M_TIMING                         => '#fff',
		// format
		QRMatrix::M_FORMAT | QRMatrix::IS_DARK     => 'url(#g1)',
		QRMatrix::M_FORMAT                         => '#fff',
		// version
		QRMatrix::M_VERSION | QRMatrix::IS_DARK    => 'url(#g1)',
		QRMatrix::M_VERSION                        => '#fff',
		// data
		QRMatrix::M_DATA | QRMatrix::IS_DARK       => 'url(#g2)',
		QRMatrix::M_DATA                           => '#fff',
		// darkmodule
		QRMatrix::M_DARKMODULE | QRMatrix::IS_DARK => 'url(#g1)',
		// separator
		QRMatrix::M_SEPARATOR                      => '#fff',
		// quietzone
		QRMatrix::M_QUIETZONE                      => '#fff',
	],
]);

$qrcode = (new QRCode($options))->render($data);

header('Content-type: image/svg+xml');

if($gzip === true){
	header('Vary: Accept-Encoding');
	header('Content-Encoding: gzip');
	$qrcode = gzencode($qrcode, 9);
}
echo $qrcode;


