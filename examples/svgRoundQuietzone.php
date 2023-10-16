<?php
/**
 * round quiet zone example
 *
 * @see https://github.com/chillerlan/php-qrcode/discussions/137
 *
 * @created      09.07.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 *
 * @noinspection PhpIllegalPsrClassPathInspection
 */

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QROutputInterface, QRMarkupSVG};
use chillerlan\QRCode\{QRCode, QRCodeException, QROptions};

require_once __DIR__.'/../vendor/autoload.php';

/*
 * Class definition
 */

/**
 * Create SVG QR Codes with embedded logos (that are also SVG),
 * randomly colored dots and a round quiet zone with added circle
 */
class RoundQuietzoneSVGoutput extends QRMarkupSVG{

	/**
	 * @inheritDoc
	 */
	protected function createMarkup(bool $saveToFile):string{
		// some Pythagorean magick
		$diameter      = sqrt(2 * pow(($this->moduleCount + $this->options->additionalModules), 2));
		// calculate the quiet zone size, add 1 to it as the outer circle stroke may go outside of it
		$quietzoneSize = ((int)ceil(($diameter - $this->moduleCount) / 2) + 1);
		// add the quiet zone to fill the circle
		$this->matrix->setQuietZone($quietzoneSize);
		// update the matrix dimensions to avoid errors in subsequent calculations
		// the moduleCount is now QR Code matrix + 2x quiet zone
		$this->setMatrixDimensions();
		// color the quiet zone
		$this->colorQuietzone($quietzoneSize, ($diameter / 2));

		// calculate the logo space
		$logoSpaceSize = (int)(ceil($this->moduleCount * $this->options->svgLogoScale) + 1);
		// we're calling QRMatrix::setLogoSpace() manually, so QROptions::$addLogoSpace has no effect here
		$this->matrix->setLogoSpace($logoSpaceSize);

		// start SVG output
		$svg = $this->header();

		if(!empty($this->options->svgDefs)){
			$svg .= sprintf('<defs>%1$s%2$s</defs>%2$s', $this->options->svgDefs, $this->options->eol);
		}

		$svg .= $this->paths();
		$svg .= $this->getLogo();
		$svg .= $this->addCircle($diameter / 2);

		// close svg
		$svg .= sprintf('%1$s</svg>%1$s', $this->options->eol);

		// transform to data URI only when not saving to file
		if(!$saveToFile && $this->options->outputBase64){
			$svg = $this->toBase64DataURI($svg, 'image/svg+xml');
		}

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
	 * Sets random modules of the quiet zone to dark
	 */
	protected function colorQuietzone(int $quietzoneSize, float $radius):void{
		$l1 = ($quietzoneSize - 1);
		$l2 = ($this->moduleCount - $quietzoneSize);
		// substract 1/2 stroke width and module radius from the circle radius to not cut off modules
		$r  = ($radius - $this->options->circleRadius * 2);

		for($y = 0; $y < $this->moduleCount; $y++){
			for($x = 0; $x < $this->moduleCount; $x++){

				// skip anything that's not quiet zone
				if(!$this->matrix->checkType($x, $y, QRMatrix::M_QUIETZONE)){
					continue;
				}

				// leave one row of quiet zone around the matrix
				if(
					   ($x === $l1 && $y >= $l1 && $y <= $l2)
					|| ($x === $l2 && $y >= $l1 && $y <= $l2)
					|| ($y === $l1 && $x >= $l1 && $x <= $l2)
					|| ($y === $l2 && $x >= $l1 && $x <= $l2)
				){
					continue;
				}

				// we need to add 0.5 units to the check values since we're calculating the element centers
				// ($x/$y is the element's assumed top left corner)
				if($this->checkIfInsideCircle(($x + 0.5), ($y + 0.5), $r)){
					$this->matrix->set($x, $y, (bool)rand(0, 1), QRMatrix::M_QUIETZONE);
				}
			}
		}

	}

	/**
	 * @see https://stackoverflow.com/a/7227057
	 */
	protected function checkIfInsideCircle(float $x, float $y, float $radius):bool{
		$dx = abs($x - $this->moduleCount / 2);
		$dy = abs($y - $this->moduleCount / 2);

		if(($dx + $dy) <= $radius){
			return true;
		}

		if($dx > $radius || $dy > $radius){
			return false;
		}

		if((pow($dx, 2) + pow($dy, 2)) <= pow($radius, 2)){
			return true;
		}

		return false;
	}

	/**
	 * add a solid circle around the matrix
	 */
	protected function addCircle(float $radius):string{
		return sprintf(
			'%4$s<circle id="circle" cx="%1$s" cy="%1$s" r="%2$s" stroke-width="%3$s"/>',
			($this->moduleCount / 2),
			round($radius, 5),
			($this->options->circleRadius * 2),
			$this->options->eol
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

	/**
	 * @inheritDoc
	 */
	protected function collectModules(Closure $transform):array{
		$paths     = [];
		$dotColors = $this->options->dotColors; // avoid magic getter in long loops

		// collect the modules for each type
		foreach($this->matrix->getMatrix() as $y => $row){
			foreach($row as $x => $M_TYPE){
				$M_TYPE_LAYER = $M_TYPE;

				if($this->connectPaths && !$this->matrix->checkTypeIn($x, $y, $this->excludeFromConnect)){
					// to connect paths we'll redeclare the $M_TYPE_LAYER to data only
					$M_TYPE_LAYER = QRMatrix::M_DATA;

					if($this->matrix->isDark($M_TYPE)){
						$M_TYPE_LAYER = QRMatrix::M_DATA_DARK;
					}
				}

				// randomly assign another $M_TYPE_LAYER for the given types
				// note that the layer id has to be an integer value,
				// ideally outside the several bitmask values
				if($M_TYPE_LAYER === QRMatrix::M_DATA_DARK){
					$M_TYPE_LAYER = array_rand($dotColors);
				}

				// collect the modules per $M_TYPE
				$module = $transform($x, $y, $M_TYPE, $M_TYPE_LAYER);

				if(!empty($module)){
					$paths[$M_TYPE_LAYER][] = $module;
				}
			}
		}

		// beautify output
		ksort($paths);

		return $paths;
	}

}

/**
 * the augmented options class
 */
class RoundQuietzoneOptions extends QROptions{

	/**
	 * The amount of additional modules to be used in the circle diameter calculation
	 *
	 * Note that the middle of the circle stroke goes through the (assumed) outer corners
	 * or centers of the QR Code (excluding quiet zone)
	 *
	 * Example:
	 *
	 * - a value of -1 would go through the center of the outer corner modules of the finder patterns
	 * - a value of 0 would go through the corner of the outer modules of the finder patterns
	 * - a value of 3 would go through the center of the module outside next to the finder patterns, in a 45-degree angle
	 */
	protected int $additionalModules = 0;

	/**
	 * a map of $M_TYPE_LAYER => color
	 *
	 * @see \array_rand()
	 */
	protected array $dotColors = [];

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

$options = new RoundQuietzoneOptions;

// custom dot options (see extended class)
$options->additionalModules = 5;
$options->dotColors         = [
	111 => '#e2453c',
	222 => '#e07e39',
	333 => '#e5d667',
	444 => '#51b95b',
	555 => '#1e72b7',
	666 => '#6f5ba7',
];

// generate the CSS for the several colored layers
$layerColors = '';

foreach($options->dotColors as $layer => $color){
	$layerColors .= sprintf("\n\t\t.qr-%s{ fill: %s; }", $layer, $color);
}

// https://developer.mozilla.org/en-US/docs/Web/SVG/Element/linearGradient
// please forgive me for I have committed colorful crimes
$options->svgDefs = '
	<linearGradient id="blurple" x1="100%" y2="100%">
		<stop stop-color="#D70071" offset="0"/>
		<stop stop-color="#9C4E97" offset="0.5"/>
		<stop stop-color="#0035A9" offset="1"/>
	</linearGradient>
	<linearGradient id="rainbow" x1="100%" y2="100%">
		<stop stop-color="#e2453c" offset="2.5%"/>
		<stop stop-color="#e07e39" offset="21.5%"/>
		<stop stop-color="#e5d667" offset="40.5%"/>
		<stop stop-color="#51b95b" offset="59.5%"/>
		<stop stop-color="#1e72b7" offset="78.5%"/>
		<stop stop-color="#6f5ba7" offset="97.5%"/>
	</linearGradient>
	<style><![CDATA[
		.light{ fill: #dedede; }
		.dark{ fill: url(#rainbow); }
		.logo{ fill: url(#blurple); }
		#circle{ fill: none; stroke: url(#blurple); }
		'.$layerColors.'
	]]></style>';

// custom SVG logo options
$options->svgLogo             = __DIR__.'/github.svg'; // logo from: https://github.com/simple-icons/simple-icons
$options->svgLogoScale        = 0.2;
$options->svgLogoCssClass     = 'logo';

// common QRCode options
$options->version             = 7;
$options->eccLevel            = EccLevel::H;
$options->addQuietzone        = false; // we're not adding a quiet zone, this is done internally in our own module
$options->outputBase64        = false; // avoid base64 URI output for the example
$options->outputType          = QROutputInterface::CUSTOM;
$options->outputInterface     = RoundQuietzoneSVGoutput::class; // load our own output class
$options->drawLightModules    = false; // set to true to add the light modules
// common SVG options
#$options->connectPaths = true; // this has been set to "always on" internally
$options->excludeFromConnect  = [
	QRMatrix::M_FINDER_DARK,
	QRMatrix::M_FINDER_DOT,
	QRMatrix::M_ALIGNMENT_DARK,
	QRMatrix::M_QUIETZONE_DARK,
];
$options->drawCircularModules = true;
$options->circleRadius        = 0.4;
$options->keepAsSquare        = [
	QRMatrix::M_FINDER_DARK,
	QRMatrix::M_FINDER_DOT,
	QRMatrix::M_ALIGNMENT_DARK,
];


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
