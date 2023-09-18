<?php
/**
 * SVG with logo example
 *
 * @created      05.03.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

use chillerlan\QRCode\{QRCode, QRCodeException, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QROutputInterface, QRMarkupSVG};

require_once __DIR__.'/../vendor/autoload.php';

/*
 * Class definition
 */

/**
 * Create SVG QR Codes with embedded logos (that are also SVG)
 */
class QRSvgWithLogo extends QRMarkupSVG{

	/**
	 * @inheritDoc
	 */
	protected function paths():string{
		$size = (int)ceil($this->moduleCount * $this->options->svgLogoScale);

		// we're calling QRMatrix::setLogoSpace() manually, so QROptions::$addLogoSpace has no effect here
		$this->matrix->setLogoSpace($size, $size);

		$svg = parent::paths();
		$svg .= $this->getLogo();

		return $svg;
	}

	/**
	 * @inheritDoc
	 */
	protected function path(string $path, int $M_TYPE):string{
		// omit the "fill" and "opacity" attributes on the path element
		return sprintf('<path class="%s" d="%s"/>', $this->getCssClass($M_TYPE), $path);
	}

	/**
	 * returns a <g> element that contains the SVG logo and positions it properly within the QR Code
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Element/g
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/transform
	 */
	protected function getLogo():string{
		// @todo: customize the <g> element to your liking (css class, style...)
		return sprintf(
			'%5$s<g transform="translate(%1$s %1$s) scale(%2$s)" class="%3$s">%5$s	%4$s%5$s</g>',
			(($this->moduleCount - ($this->moduleCount * $this->options->svgLogoScale)) / 2),
			$this->options->svgLogoScale,
			$this->options->svgLogoCssClass,
			file_get_contents($this->options->svgLogo),
			$this->options->eol
		);
	}

}


/**
 * augment the QROptions class
 */
class SVGWithLogoOptions extends QROptions{
	// path to svg logo
	protected string $svgLogo;
	// logo scale in % of QR Code size, clamped to 10%-30%
	protected float $svgLogoScale = 0.20;
	// css class for the logo (defined in $svgDefs)
	protected string $svgLogoCssClass = '';

	// check logo
	protected function set_svgLogo(string $svgLogo):void{

		if(!file_exists($svgLogo) || !is_readable($svgLogo)){
			throw new QRCodeException('invalid svg logo');
		}

		// @todo: validate svg

		$this->svgLogo = $svgLogo;
	}

	// clamp logo scale
	protected function set_svgLogoScale(float $svgLogoScale):void{
		$this->svgLogoScale = max(0.05, min(0.3, $svgLogoScale));
	}

}


/*
 * Runtime
 */

$options = new SVGWithLogoOptions;

// SVG logo options (see extended class)
$options->svgLogo             = __DIR__.'/github.svg'; // logo from: https://github.com/simple-icons/simple-icons
$options->svgLogoScale        = 0.25;
$options->svgLogoCssClass     = 'dark';
// QROptions
$options->version             = 5;
$options->outputType          = QROutputInterface::CUSTOM;
$options->outputInterface     = QRSvgWithLogo::class;
$options->outputBase64        = false;
$options->eccLevel            = EccLevel::H; // ECC level H is necessary when using logos
$options->addQuietzone        = true;
$options->drawLightModules    = true;
$options->connectPaths        = true;
$options->drawCircularModules = true;
$options->circleRadius        = 0.45;
$options->keepAsSquare        = [
	QRMatrix::M_FINDER_DARK,
	QRMatrix::M_FINDER_DOT,
	QRMatrix::M_ALIGNMENT_DARK,
];
// https://developer.mozilla.org/en-US/docs/Web/SVG/Element/linearGradient
$options->svgDefs = '
	<linearGradient id="gradient" x1="100%" y2="100%">
		<stop stop-color="#D70071" offset="0"/>
		<stop stop-color="#9C4E97" offset="0.5"/>
		<stop stop-color="#0035A9" offset="1"/>
	</linearGradient>
	<style><![CDATA[
		.dark{fill: url(#gradient);}
		.light{fill: #eaeaea;}
	]]></style>';


$out = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');


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
