<?php
/**
 * SVG with logo and custom shapes example
 *
 * @see https://github.com/chillerlan/php-qrcode/discussions/150
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
class QRSvgWithLogoAndCustomShapes extends QRMarkupSVG{

	/**
	 * @inheritDoc
	 */
	protected function paths():string{
		// make sure connect paths is enabled
		$this->options->connectPaths = true;

		// we're calling QRMatrix::setLogoSpace() manually, so QROptions::$addLogoSpace has no effect here
		$this->matrix->setLogoSpace((int)ceil($this->moduleCount * $this->options->svgLogoScale));

		// generate the path element(s) - in this case it's just one element as we've "disabled" several options
		$svg = parent::paths();
		// add the custom shapes for the finder patterns
		$svg .= $this->getFinderPatterns();
		// and add the custom logo
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
	 * returns a path segment for a single module
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/d
	 */
	protected function module(int $x, int $y, int $M_TYPE):string{

		if(
			!$this->matrix->isDark($M_TYPE)
			// we're skipping the finder patterns here
			|| $this->matrix->checkType($x, $y, QRMatrix::M_FINDER)
			|| $this->matrix->checkType($x, $y, QRMatrix::M_FINDER_DOT)
		){
			return '';
		}

		// return a heart shape (or any custom shape for that matter)
		return sprintf('M%1$s %2$s m0.5,0.96 l-0.412,-0.412 a0.3 0.3 0 0 1 0.412,-0.435 a0.3 0.3 0 0 1 0.412,0.435Z', $x, $y);
	}

	/**
	 * returns a custom path for the 3 finder patterns
	 */
	protected function getFinderPatterns():string{

		$qz  = ($this->options->addQuietzone) ? $this->options->quietzoneSize : 0;
		// the positions for the finder patterns (top left corner)
		// $this->moduleCount includes 2* the quiet zone size already, so we need to take this into account
		$pos = [
			[(0 + $qz), (0 + $qz)],
			[(0 + $qz), ($this->moduleCount - $qz - 7)],
			[($this->moduleCount - $qz - 7), (0 + $qz)],
		];

		// the custom path for one finder pattern - the first move (M) is parametrized, the rest are relative coordinates
		$path   = 'M%1$s,%2$s m2,0 h3 q2,0 2,2 v3 q0,2 -2,2 h-3 q-2,0 -2,-2 v-3 q0,-2 2,-2z m0,1 q-1,0 -1,1 v3 '.
		          'q0,1 1,1 h3 q1,0 1,-1 v-3 q0,-1 -1,-1z m0,2.5 a1.5,1.5 0 1 0 3,0 a1.5,1.5 0 1 0 -3,0Z';
		$finder = [];

		foreach($pos as [$ix, $iy]){
			$finder[] = sprintf($path, $ix, $iy);
		}

		return sprintf(
			'%s<path class="%s" d="%s"/>',
			$this->options->eol,
			$this->getCssClass(QRMatrix::M_FINDER_DARK),
			implode(' ', $finder)
		);
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
class SVGWithLogoAndCustomShapesOptions extends QROptions{
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

// please excuse the IDE yelling https://youtrack.jetbrains.com/issue/WI-66549
$options = new SVGWithLogoAndCustomShapesOptions;

// SVG logo options (see extended class below)
$options->svgLogo         = __DIR__.'/github.svg'; // logo from: https://github.com/simple-icons/simple-icons
$options->svgLogoScale    = 0.25;
$options->svgLogoCssClass = 'qr-logo dark';

// QROptions
$options->version         = 5;
$options->quietzoneSize   = 4;
$options->outputType      = QROutputInterface::CUSTOM;
$options->outputInterface = QRSvgWithLogoAndCustomShapes::class;
$options->outputBase64    = false;
$options->eccLevel        = EccLevel::H; // ECC level H is required when using logos
$options->addQuietzone    = true;

// https://developer.mozilla.org/en-US/docs/Web/SVG/Element/linearGradient
$options->svgDefs         = '
	<linearGradient id="gradient" x1="100%" y2="100%">
		<stop stop-color="#D70071" offset="0"/>
		<stop stop-color="#9C4E97" offset="0.5"/>
		<stop stop-color="#0035A9" offset="1"/>
	</linearGradient>
	<style><![CDATA[
		.dark{fill: url(#gradient);}
		.qr-logo{fill: #424242;}
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
