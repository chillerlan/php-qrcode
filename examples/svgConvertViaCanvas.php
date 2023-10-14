<?php
/**
 * SVG to PNG conversion via javascript canvas example
 *
 * @created      25.09.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */


use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

require_once __DIR__.'/../vendor/autoload.php';

$options = new QROptions;

$options->version              = 7;
$options->outputType           = QROutputInterface::MARKUP_SVG;
$options->outputBase64         = false;
$options->svgAddXmlHeader      = false;
$options->svgUseFillAttributes = false;
$options->drawLightModules     = false;
$options->drawCircularModules  = true;
$options->circleRadius         = 0.4;
$options->connectPaths         = true;

$options->keepAsSquare         = [
	QRMatrix::M_FINDER_DARK,
	QRMatrix::M_FINDER_DOT,
	QRMatrix::M_ALIGNMENT_DARK,
];

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
	]]></style>';


$qrcode = (new QRCode($options))->addByteSegment('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

// render the SVG
$svg_raw = $qrcode->render();
// switch to base64 output
$options->outputBase64 = true;
// render base64
$svg_base64 = $qrcode->render();

// dump the output
header('Content-type: text/html');

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title>QRCode javascript canvas conversion example</title>
</head>
<body>
<!-- raw SVG input -->
<div>
	<?php echo $svg_raw ?>
	<img id="qr-svg-dest" />
</div>
<!-- base64 encoded datd URI from img tag -->
<div>
	<img id="qr-svg-base64" src="<?php echo $svg_base64 ?>" style="width: 300px; height: 300px;" />
	<img id="qr-svg-base64-dest" />
</div>
<script type="module">
	import SVGConvert from './SVGConvert.js';

    // SVG DOM element
    SVGConvert.toDataURI(document.querySelector('svg.qr-svg'), document.getElementById('qr-svg-dest'), 300, 300, 'image/jpeg');
    // base64 data URI in image element
    SVGConvert.toDataURI(document.getElementById('qr-svg-base64'), document.getElementById('qr-svg-base64-dest'), 300, 300);
</script>
</html>
