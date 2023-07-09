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
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;

require_once __DIR__.'/../vendor/autoload.php';

$options = new QROptions([
	'version'             => 7,
	'outputType'          => QROutputInterface::MARKUP_SVG,
	'imageBase64'         => false,
	'eccLevel'            => EccLevel::L,
	'addQuietzone'        => true,
	// if set to false, the light modules won't be rendered
	'drawLightModules'    => true,
	// empty the default value to remove the fill* and opacity* attributes from the <path> elements
	'markupDark'          => '',
	'markupLight'         => '',
	// draw the modules as circles isntead of squares
	'drawCircularModules' => true,
	'circleRadius'        => 0.4,
	// connect paths
	'connectPaths'        => true,
	// keep modules of these types as square
	'keepAsSquare'        => [
		QRMatrix::M_FINDER_DARK,
		QRMatrix::M_FINDER_DOT,
		QRMatrix::M_ALIGNMENT_DARK,
	],
	// https://developer.mozilla.org/en-US/docs/Web/SVG/Element/linearGradient
	'svgDefs'             => '
	<linearGradient id="rainbow" x1="1" y2="1">
		<stop stop-color="#e2453c" offset="0"/>
		<stop stop-color="#e07e39" offset="0.2"/>
		<stop stop-color="#e5d667" offset="0.4"/>
		<stop stop-color="#51b95b" offset="0.6"/>
		<stop stop-color="#1e72b7" offset="0.8"/>
		<stop stop-color="#6f5ba7" offset="1"/>
	</linearGradient>
	<style><![CDATA[
		.dark{fill: url(#rainbow);}
		.light{fill: #eee;}
	]]></style>',
]);

$qrcode = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

if(php_sapi_name() !== 'cli'){
	header('Content-type: image/svg+xml');

	if(extension_loaded('zlib')){
		header('Vary: Accept-Encoding');
		header('Content-Encoding: gzip');
		$qrcode = gzencode($qrcode, 9);
	}
}

echo $qrcode;

exit;
