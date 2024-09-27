<?php
/**
 * A jitter effect for square modules (Mosaic)
 *
 * @created      17.09.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\Settings\SettingsContainerInterface;

require_once __DIR__.'/../vendor/autoload.php';


/*
 * Class definition
 */

/**
 *  the extended SVG output module
 */
class ModuleJitterSVGoutput extends QRMarkupSVG{

	protected const ROUND_PRECISION = 5;

	protected readonly float $sideLength;

	public function __construct(QROptions|SettingsContainerInterface $options, QRMatrix $matrix){
		parent::__construct($options, $matrix);

		// copy the value to a local property to avoid excessive magic getter calls
		$this->sideLength = $this->options->sideLength;
	}

	// emulates JS Math.random()
	protected function random():float{
		return (random_int(0, PHP_INT_MAX) / PHP_INT_MAX);
	}

	protected function module(int $x, int $y, int $M_TYPE):string{

		// skip light modules
		if((!$this->options->drawLightModules && !$this->matrix->check($x, $y))){
			return '';
		}

		// early exit on pure square modules
		if($this->matrix->checkTypeIn($x, $y, $this->options->keepAsSquare)){
			// phpcs:ignore
			return "M$x,$y h1 v1 h-1Z";
		}

		// calculate the maximum tilt angle of the square with the previously determined side length
		$maxAngle = (45 - rad2deg(acos(1 / hypot($this->sideLength, $this->sideLength))));
		// set the maximum angle from the options and clamp between valid min/max
		$maxAngle = max(0, min($maxAngle, $this->options->maxAngle));
		// randomize the tilt angle
		$a = ($this->random() * $maxAngle);
		// calculate the opposite and adjacent sides of the triangle
		$opp = round((cos(deg2rad($a)) * $this->sideLength), self::ROUND_PRECISION);
		$adj = round((sin(deg2rad($a)) * $this->sideLength), self::ROUND_PRECISION);

		// tilt to the left
		if($this->random() > 0.5){
			$x = round(($x + 0.5 - $opp / 2 - $adj / 2), self::ROUND_PRECISION);
			$y = round(($y + 0.5 - $opp / 2 + $adj / 2), self::ROUND_PRECISION);

			// phpcs:ignore
			return "M$x,$y l$opp,-$adj l$adj,$opp l-$opp,$adj Z";
		}

		// tilt right
		$x = round(($x + 0.5 - $opp / 2 + $adj / 2), self::ROUND_PRECISION);
		$y = round(($y + 0.5 - $opp / 2 - $adj / 2), self::ROUND_PRECISION);

		// phpcs:ignore
		return "M$x,$y l$opp,$adj l-$adj,$opp l-$opp,-$adj Z";
	}

}


/**
 * the augmented options class
 *
 * @property float $sideLength
 * @property float $maxAngle
 */
class ModuleJitterOptions extends QROptions{

	/**
	 * the side length of the modules (calmped internally between square root of 0.5 (at 45°) and 1 (full length))
	 */
	protected float $sideLength = 0.8;

	/**
	 * The maximum tilt angle (clamped inside the 1x1 module, at a maximum of 45 degrees)
	 */
	protected float $maxAngle = 45.0;

	/**
	 * clamp the side length
	 */
	protected function set_sideLength(float $sideLength):void{
		$this->sideLength = max(M_SQRT1_2, min(1.0, $sideLength));
	}

}


/*
 * Runtime
 */
$options = new ModuleJitterOptions;

// settings from the custom options class
$options->sideLength           = 0.85;
$options->maxAngle             = 45.0;

$options->version              = 7;
$options->outputInterface      = ModuleJitterSVGoutput::class;
$options->drawLightModules     = false;
$options->svgUseFillAttributes = false;
$options->outputBase64         = false;
$options->addQuietzone         = true;
$options->connectPaths         = true;
$options->keepAsSquare         = [
	QRMatrix::M_FINDER_DARK,
	QRMatrix::M_FINDER_DOT,
	QRMatrix::M_ALIGNMENT_DARK,
];
$options->svgDefs              = '
	<linearGradient id="rainbow" x1="100%" y2="100%">
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


$out = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');


if(PHP_SAPI !== 'cli'){
	header('Content-type: image/svg+xml');

	if(extension_loaded('zlib')){
		header('Vary: Accept-Encoding');
		header('Content-Encoding: gzip');
		$out = gzencode($out, 9);
	}
}

echo $out;

exit;
