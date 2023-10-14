<?php
/**
 * SVG to raster conversion example using ImageMagick
 *
 * Please note that conversion via ImageMagick may not always produce ideal results,
 * especially when using CSS styling (external or via <defs>), also it depends on OS and Imagick version.
 *
 * Using the Inkscape command line may be the better option:
 *
 * @see https://wiki.inkscape.org/wiki/Using_the_Command_Line
 * @see https://github.com/chillerlan/php-qrcode/discussions/216
 *
 * @created      19.09.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

require_once __DIR__.'/../vendor/autoload.php';

class SVGConvert extends QRMarkupSVG{

	/** @inheritDoc */
	protected function header():string{
		[$width, $height] = $this->getOutputDimensions();

		// we need to specify the "width" and "height" attributes so that Imagick knows the output size
		$header = sprintf(
			'<svg xmlns="http://www.w3.org/2000/svg" class="qr-svg %1$s" viewBox="%2$s" preserveAspectRatio="%3$s" width="%5$s" height="%6$s">%4$s',
			$this->options->cssClass,
			$this->getViewBox(),
			$this->options->svgPreserveAspectRatio,
			$this->options->eol,
			($width * $this->scale), // use the scale option to modify the size
			($height * $this->scale)
		);

		if($this->options->svgAddXmlHeader){
			$header = sprintf('<?xml version="1.0" encoding="UTF-8"?>%s%s', $this->options->eol, $header);
		}

		return $header;
	}

	/** @inheritDoc */
	public function dump(string $file = null):string{
		$base64 = $this->options->outputBase64;
		// we don't want the SVG in base64
		$this->options->outputBase64 = false;

		$svg = $this->createMarkup($file !== null);

		// now convert the output
		$im = new Imagick;
		$im->readImageBlob($svg);
		$im->setImageFormat($this->options->imagickFormat);

		if($this->options->quality > -1){
			$im->setImageCompressionQuality(max(0, min(100, $this->options->quality)));
		}

		$imageData = $im->getImageBlob();

		$im->destroy();
		$this->saveToFile($imageData, $file);

		if($base64){
			// use finfo to guess the mime type
			$imageData = $this->toBase64DataURI($imageData, (new finfo(FILEINFO_MIME_TYPE))->buffer($imageData));
		}

		return $imageData;
	}

}


// SVG from the basic example
$options = new QROptions;

$options->version              = 7;
$options->outputType           = QROutputInterface::CUSTOM;
$options->outputInterface      = SVGConvert::class;
$options->imagickFormat        = 'png32';
$options->scale                = 20;
$options->outputBase64         = false;
$options->drawLightModules     = true;
$options->svgUseFillAttributes = false;
$options->drawCircularModules  = true;
$options->circleRadius         = 0.4;
$options->connectPaths         = true;
$options->keepAsSquare         = [
	QRMatrix::M_FINDER_DARK,
	QRMatrix::M_FINDER_DOT,
	QRMatrix::M_ALIGNMENT_DARK,
];
$options->svgDefs              = '
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
		svg{ width: 530px; height: 530px; }
	]]></style>';


// render the SVG and convert to the desired ImageMagick format
$image = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

header('Content-type: image/png');

echo $image;
