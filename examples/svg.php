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

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

require_once __DIR__.'/../vendor/autoload.php';

$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';
$gzip = true;

$options = new QROptions([
	'version'      => 5,
	'outputType'   => QRCode::OUTPUT_MARKUP_SVG,
	'eccLevel'     => QRCode::ECC_L,
	'scale'        => 5,
	'addQuietzone' => false,
	'cssClass'     => 'my-css-class',
	'svgOpacity'   => 0.8,
	'svgDefs'      => '
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
		1536 => 'url(#g1)', // dark (true)
		6    => '#eee', // light (false)
		// alignment
		2560 => 'url(#g1)',
		10   => '#eee',
		// timing
		3072 => 'url(#g1)',
		12   => '#eee',
		// format
		3584 => 'url(#g1)',
		14   => '#eee',
		// version
		4096 => 'url(#g1)',
		16   => '#eee',
		// data
		1024 => 'url(#g2)',
		4    => '#eee',
		// darkmodule
		512  => 'url(#g1)',
		// separator
		8    => '#eee',
		// quietzone
		18   => '#eee',
	],
]);

header('Content-type: image/svg+xml');

$qr = (new QRCode($options))->render($data);

if($gzip === true){
	header('Vary: Accept-Encoding');
	header('Content-Encoding: gzip');
	$qr = gzencode($qr ,9);
}

echo $qr;
