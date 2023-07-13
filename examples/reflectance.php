<?php
/**
 * reflectance.php
 *
 * @created      13.07.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

require_once __DIR__.'/../vendor/autoload.php';

$options = new QROptions([
	'outputType'          => QROutputInterface::MARKUP_SVG,
	'imageBase64'         => false,
	'svgAddXmlHeader'     => false,
	'connectPaths'        => true,
	'drawCircularModules' => false,
	'drawLightModules'    => true,
	'addLogoSpace'        => true,
	'eccLevel'            => EccLevel::H,
	'logoSpaceWidth'      => 11,
	'moduleValues'        => [
		// finder
		QRMatrix::M_FINDER_DARK      => '#555', // dark (true)
		QRMatrix::M_FINDER           => '#ccc', // light (false)
		QRMatrix::M_FINDER_DOT       => '#555',
		QRMatrix::M_FINDER_DOT_LIGHT => '#ccc',
		// alignment
		QRMatrix::M_ALIGNMENT_DARK   => '#555',
		QRMatrix::M_ALIGNMENT        => '#ccc',
		// timing
		QRMatrix::M_TIMING_DARK      => '#555',
		QRMatrix::M_TIMING           => '#ccc',
		// format
		QRMatrix::M_FORMAT_DARK      => '#555',
		QRMatrix::M_FORMAT           => '#ccc',
		// version
		QRMatrix::M_VERSION_DARK     => '#555',
		QRMatrix::M_VERSION          => '#ccc',
		// data
		QRMatrix::M_DATA_DARK        => '#555',
		QRMatrix::M_DATA             => '#ccc',
		// darkmodule
		QRMatrix::M_DARKMODULE_LIGHT => '#ccc',
		QRMatrix::M_DARKMODULE       => '#555',
		// separator
		QRMatrix::M_SEPARATOR_DARK   => '#555',
		QRMatrix::M_SEPARATOR        => '#ccc',
		// quietzone
		QRMatrix::M_QUIETZONE_DARK   => '#555',
		QRMatrix::M_QUIETZONE        => '#ccc',
		// logo space
		QRMatrix::M_LOGO_DARK        => '#555',
		QRMatrix::M_LOGO             => '#ccc',
	],
]);

$qrcode = (new QRCode($options))->addByteSegment('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
$matrix = $qrcode->getQRMatrix();

// dump the output
header('Content-type: text/html');

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title>QRCode Reflectance reversal Example</title>
	<style>
		#reflectance-example{
			width: 500px;
			margin: 2em auto;
		}
		#reflectance-example > svg.qrcode{
			margin: 1em;
		}
	</style>
</head>
<body>
<div id="reflectance-example">
	<!-- embed the SVG directly -->
	<?php
		echo $qrcode->renderMatrix($matrix);
		echo $qrcode->renderMatrix($matrix->invert());
	?>
</div>
</body>
</html>
<?php

exit;
