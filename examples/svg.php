<?php
/**
 * SVG output example
 *
 * @created      21.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;

require_once __DIR__.'/../vendor/autoload.php';

$options = new QROptions;

$options->version              = 7;
$options->outputType           = QROutputInterface::MARKUP_SVG;
$options->outputBase64         = false;
// if set to false, the light modules won't be rendered
$options->drawLightModules     = true;
$options->svgUseFillAttributes = false;
// draw the modules as circles isntead of squares
$options->drawCircularModules  = true;
$options->circleRadius         = 0.4;
// connect paths
$options->connectPaths         = true;
// keep modules of these types as square
$options->keepAsSquare         = [
	QRMatrix::M_FINDER_DARK,
	QRMatrix::M_FINDER_DOT,
	QRMatrix::M_ALIGNMENT_DARK,
];
// https://developer.mozilla.org/en-US/docs/Web/SVG/Element/linearGradient
$options->svgDefs             = '
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
	]]></style>';


try{
	$out = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
}
catch(Throwable $e){
	// handle the exception in whatever way you need
	exit($e->getMessage());
}


if(php_sapi_name() !== 'cli'){
	header('Content-type: image/svg+xml');

	if(extension_loaded('zlib')){
		header('Vary: Accept-Encoding');
		header('Content-Encoding: gzip');
		$out = gzencode($out, 9);
	}
}

echo $out;

exit;
