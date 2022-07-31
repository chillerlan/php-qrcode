<?php
/**
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
use chillerlan\QRCode\{QRCode, QROptions};

require_once __DIR__.'/../vendor/autoload.php';

/*
 * Class definition
 */

/**
 * the extended SVG output module
 */
class RoundQuietzoneSVGoutput extends QRMarkupSVG{

	/**
	 * @inheritDoc
	 */
	protected function createMarkup(bool $saveToFile):string{
		// some Pythagorean magick
		$diameter      = sqrt(2 * pow($this->moduleCount + $this->options->additionalModules, 2));
		// calculate the quiet zone size, add 1 to it as the outer circle stroke may go outside of it
		$quietzoneSize = (int)ceil(($diameter - $this->moduleCount) / 2) + 1;
		// add the quiet zone to fill the circle
		$this->matrix->setQuietZone($quietzoneSize);
		// update the matrix dimensions to avoid errors in subsequent calculations
		// the moduleCount is now QR Code matrix + 2x quiet zone
		$this->setMatrixDimensions();
		// color the quiet zone
		$this->colorQuietzone($quietzoneSize, $diameter / 2);

		// start SVG output
		$svg = $this->header();

		if(!empty($this->options->svgDefs)){
			$svg .= sprintf('<defs>%1$s%2$s</defs>%2$s', $this->options->svgDefs, $this->options->eol);
		}

		$svg .= $this->paths();
		$svg .= $this->addCircle($diameter / 2);

		// close svg
		$svg .= sprintf('%1$s</svg>%1$s', $this->options->eol);

		// transform to data URI only when not saving to file
		if(!$saveToFile && $this->options->imageBase64){
			$svg = $this->toBase64DataURI($svg, 'image/svg+xml');
		}

		return $svg;
	}

	/**
	 * Sets random modules of the quiet zone to dark
	 */
	protected function colorQuietzone(int $quietzoneSize, float $radius):void{
		$l1 = $quietzoneSize - 1;
		$l2 = $this->moduleCount - $quietzoneSize;
		// substract 1/2 stroke width and module radius from the circle radius to not cut off modules
		$r  = $radius - $this->options->circleRadius * 2;

		foreach($this->matrix->matrix() as $y => $row){
			foreach($row as $x => $value){

				// skip anything that's not quiet zone
				if($value !== QRMatrix::M_QUIETZONE){
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
				if($this->checkIfInsideCircle($x + 0.5, $y + 0.5, $r)){
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

		if($dx + $dy <= $radius){
			return true;
		}

		if($dx > $radius || $dy > $radius){
			return false;
		}

		if(pow($dx, 2) + pow($dy, 2) <= pow($radius, 2)){
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
			$this->moduleCount / 2,
			round($radius, 5),
			$this->options->circleRadius * 2,
			$this->options->eol
		);
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
	 * - a value of 3 would go through the center of the module outside next to the finder patterns, in a 45 degree angle
	 */
	protected int $additionalModules = 0;

}


/*
 * Runtime
 */

$options = new RoundQuietzoneOptions([
	'additionalModules'   => 5,

	'version'             => 7,
	'eccLevel'            => EccLevel::H, // maximum error correction capacity, esp. for print
	'addQuietzone'        => false, // we're not adding a quiet zone, this is done internally in our own module
	'imageBase64'         => false, // avoid base64 URI output
	'outputType'          => QROutputInterface::CUSTOM,
	'outputInterface'     => RoundQuietzoneSVGoutput::class, // load our own output class
	'markupDark'          => '', // avoid "fill" attributes on paths
	'markupLight'         => '',
	'drawLightModules'    => false, // set to true to add the light modules

	'connectPaths'        => true,
	'excludeFromConnect'  => [
		 QRMatrix::M_FINDER|QRMatrix::IS_DARK,
		 QRMatrix::M_FINDER_DOT|QRMatrix::IS_DARK,
		 QRMatrix::M_ALIGNMENT|QRMatrix::IS_DARK,
		 QRMatrix::M_QUIETZONE|QRMatrix::IS_DARK
	],

	'drawCircularModules' => true,
	'circleRadius'        => 0.4,
	'keepAsSquare'        => [
		 QRMatrix::M_FINDER|QRMatrix::IS_DARK,
		 QRMatrix::M_FINDER_DOT|QRMatrix::IS_DARK,
		 QRMatrix::M_ALIGNMENT|QRMatrix::IS_DARK,
	],
	// https://developer.mozilla.org/en-US/docs/Web/SVG/Element/linearGradient
	'svgDefs'             => '
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
		.dark{ fill: url(#rainbow); }
		.light{ fill: #dedede; }
		.qr-2304{ fill: url(#blurple); }
		#circle{ fill: none; stroke: url(#blurple); }
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
