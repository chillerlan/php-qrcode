<?php
/**
 * This code generates an SVG QR code with rounded corners. It uses a round rect for each square and then additional
 * paths to fill in the gap where squares are next to each other. Adjacent squares overlap - to almost completely
 * eliminate hairline antialias "cracks" that tend to appear when two SVG paths are exactly adjacent to each other.
 *
 * @see https://github.com/chillerlan/php-qrcode/issues/127
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QROutputInterface, QRMarkupSVG};

require_once __DIR__.'/../vendor/autoload.php';

/*
 * Class definition
 */

/**
 *  the extended SVG output module
 */
class MeltedSVGQRCodeOutput extends QRMarkupSVG{

	/**
	 * @inheritDoc
	 */
	protected function path(string $path, int $M_TYPE):string{
		// omit the "fill" and "opacity" attributes on the path element
		return sprintf('<path class="%s" d="%s"/>', $this->getCssClass($M_TYPE), $path);
	}

	/**
	 * @inheritDoc
	 */
	protected function collectModules(Closure $transform):array{
		$paths = [];

		// collect the modules for each type
		for($y = 0; $y < $this->moduleCount; $y++){
			for($x = 0; $x < $this->moduleCount; $x++){
				$M_TYPE       = $this->matrix->get($x, $y);
				$M_TYPE_LAYER = $M_TYPE;

				if($this->options->connectPaths && !$this->matrix->checkTypeIn($x, $y, $this->options->excludeFromConnect)){
					// to connect paths we'll redeclare the $M_TYPE_LAYER to data only
					$M_TYPE_LAYER = QRMatrix::M_DATA;

					if($this->matrix->check($x, $y)){
						$M_TYPE_LAYER |= QRMatrix::IS_DARK;
					}
				}

				// if we're going to "melt" the matrix, we'll declare *all* modules as dark,
				// so that light modules with dark parts are rendered in the same path
				if($this->options->melt){
					$M_TYPE_LAYER |= QRMatrix::IS_DARK;
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

	/**
	 * @inheritDoc
	 */
	protected function module(int $x, int $y, int $M_TYPE):string{
		$bits     = $this->matrix->checkNeighbours($x, $y, null);
		$check    = fn(int $all, int $any = 0):bool => ($bits & ($all | (~$any & 0xff))) === $all;

		$template = ($M_TYPE & QRMatrix::IS_DARK) === QRMatrix::IS_DARK
			? $this->darkModule($check, $this->options->inverseMelt)
			: $this->lightModule($check, $this->options->inverseMelt);

		$r = $this->options->meltRadius;

		return sprintf($template, $x, $y, $r, (1 - $r), (1 - 2 * $r));
	}

	/**
	 * returns a dark module for the given values
	 */
	protected function darkModule(Closure $check, bool $invert):string{

		switch(true){
			// 4 rounded
			case !$invert && $check(0b00000000, 0b01010101):
			case  $invert && $check(0b00000000, 0b00000000):
				return 'M%1$s,%2$s m0,%3$s v%5$s q0,%3$s %3$s,%3$s h%5$s q%3$s,0 %3$s,-%3$s v-%5$s q0,-%3$s -%3$s,-%3$s h-%5$s q-%3$s,0 -%3$s,%3$sZ';

			// 3 rounded
			case $invert && $check(0b01000000, 0b00000000):  // 135
				return 'M%1$s,%2$s m0,1 h%4$s q%3$s,0 %3$s,-%3$s v-%5$s q0,-%3$s -%3$s,-%3$s h-%5$s q-%3$s,0 -%3$s,%3$sZ';
			case $invert && $check(0b00000001, 0b00000000):  // 357
				return 'M%1$s,%2$s v%4$s q0,%3$s %3$s,%3$s h%5$s q%3$s,0 %3$s,-%3$s v-%5$s q0,-%3$s -%3$s,-%3$sZ';
			case $invert && $check(0b00000100, 0b00000000):  // 571
				return 'M%1$s,%2$s m1,0 v%4$s q0,%3$s -%3$s,%3$s h-%5$s q-%3$s,0 -%3$s,-%3$s v-%5$s q0,-%3$s %3$s,-%3$sZ';
			case $invert && $check(0b00010000, 0b00000000):  // 713
				return 'M%1$s,%2$s m1,1 h-%4$s q-%3$s,0 -%3$s,-%3$s v-%5$s q0,-%3$s %3$s,-%3$s h%5$s q%3$s,0 %3$s,%3$sZ';

			// 2 rounded
			case !$invert && $check(0b00100000, 0b01010101): // 13
			case  $invert && $check(0b00000000, 0b01110000):
				return 'M%1$s,%2$s m0,1 h1 v-%4$s q0,-%3$s -%3$s,-%3$s h-%5$s q-%3$s,0 -%3$s,%3$sZ';
			case !$invert && $check(0b10000000, 0b01010101): // 35
			case  $invert && $check(0b00000000, 0b11000001):
				return 'M%1$s,%2$s v1 h%4$s q%3$s,0 %3$s,-%3$s v-%5$s q0,-%3$s -%3$s,-%3$sZ';
			case !$invert && $check(0b00000010, 0b01010101): // 57
			case  $invert && $check(0b00000000, 0b00000111):
				return 'M%1$s,%2$s v%4$s q0,%3$s %3$s,%3$s h%5$s q%3$s,0 %3$s,-%3$s v-%4$sZ';
			case !$invert && $check(0b00001000, 0b01010101): // 71
			case  $invert && $check(0b00000000, 0b00011100):
				return 'M%1$s,%2$s m1,1 v-1 h-%4$s q-%3$s,0 -%3$s,%3$s v%5$s q0,%3$s %3$s,%3$sZ';
			// diagonal
			case  $invert && $check(0b01000100, 0b00000000):  // 15
				return 'M%1$s,%2$s m0,1 h%4$s q%3$s,0 %3$s,-%3$s v-%4$s h-%4$s q-%3$s,0 -%3$s,%3$sZ';
			case  $invert && $check(0b00010001, 0b00000000):  // 37
				return 'M%1$s,%2$s h%4$s q%3$s,0 %3$s,%3$s v%4$s h-%4$s q-%3$s,0 -%3$s,-%3$sZ';

			// 1 rounded
			case !$invert && $check(0b00101000, 0b01010101): // 1
			case  $invert && $check(0b00000000, 0b01111100):
				return 'M%1$s,%2$s m0,1 h1 v-1 h-%4$s q-%3$s,0 -%3$s,%3$sZ';
			case !$invert && $check(0b10100000, 0b01010101): // 3
			case  $invert && $check(0b00000000, 0b11110001):
				return 'M%1$s,%2$s h%4$s q%3$s,0 %3$s,%3$s v%4$s h-1Z';
			case !$invert && $check(0b10000010, 0b01010101): // 5
			case  $invert && $check(0b00000000, 0b11000111):
				return 'M%1$s,%2$s h1 v%4$s q0,%3$s -%3$s,%3$s h-%4$sZ';
			case !$invert && $check(0b00001010, 0b01010101): // 7
			case  $invert && $check(0b00000000, 0b00011111):
				return 'M%1$s,%2$s v%4$s q0,%3$s %3$s,%3$s h%4$s v-1Z';
		}

		// full square
		return 'M%1$s,%2$s h1 v1 h-1Z';
	}

	/**
	 * returns a light module for the given values
	 */
	protected function lightModule(Closure $check, bool $invert):string{

		switch(true){
			// 4 rounded
			case !$invert && $check(0b11111111, 0b01010101):
			case  $invert && $check(0b10101010, 0b01010101):
				return 'M%1$s,%2$s h%3$s q-%3$s,0 -%3$s,%3$sz m1,0 v%3$s q0,-%3$s -%3$s,-%3$sz m0,1 h-%3$s q%3$s,0 %3$s,-%3$sz m-1,0 v-%3$s q0,%3$s %3$s,%3$sZ';

			// 3 rounded
			case !$invert && $check(0b10111111, 0b00000000):  // 135
				return 'M%1$s,%2$s h%3$s q-%3$s,0 -%3$s,%3$sz m1,0 v%3$s q0,-%3$s -%3$s,-%3$sz m0,1 h-%3$s q%3$s,0 %3$s,-%3$sZ';
			case !$invert && $check(0b11111110, 0b00000000):  // 357
				return 'M%1$s,%2$s m1,0 v%3$s q0,-%3$s -%3$s,-%3$sz m0,1 h-%3$s q%3$s,0 %3$s,-%3$sz m-1,0 v-%3$s q0,%3$s %3$s,%3$sZ';
			case !$invert && $check(0b11111011, 0b00000000):  // 571
				return 'M%1$s,%2$s h%3$s q-%3$s,0 -%3$s,%3$sz m0,1 v-%3$s q0,%3$s %3$s,%3$sz m1,0 h-%3$s q%3$s,0 %3$s,-%3$sZ';
			case !$invert && $check(0b11101111, 0b00000000):  // 713
				return 'M%1$s,%2$s h%3$s q-%3$s,0 -%3$s,%3$sz m0,1 v-%3$s q0,%3$s %3$s,%3$sz m1,-1 v%3$s q0,-%3$s -%3$s,-%3$sZ';

			// 2 rounded
			case !$invert && $check(0b10001111, 0b01110000): // 13
			case  $invert && $check(0b10001010, 0b01010101):
				return 'M%1$s,%2$s h%3$s q-%3$s,0 -%3$s,%3$sz m1,0 v%3$s q0,-%3$s -%3$s,-%3$sZ';
			case !$invert && $check(0b00111110, 0b11000001): // 35
			case  $invert && $check(0b00101010, 0b01010101):
				return 'M%1$s,%2$s m1,0 v%3$s q0,-%3$s -%3$s,-%3$sz m0,1 h-%3$s q%3$s,0 %3$s,-%3$sZ';
			case !$invert && $check(0b11111000, 0b00000111): // 57
			case  $invert && $check(0b10101000, 0b01010101):
				return 'M%1$s,%2$s m1,1 h-%3$s q%3$s,0 %3$s,-%3$sz m-1,0 v-%3$s q0,%3$s %3$s,%3$sZ';
			case !$invert && $check(0b11100011, 0b00011100): // 71
			case  $invert && $check(0b10100010, 0b01010101):
				return 'M%1$s,%2$s h%3$s q-%3$s,0 -%3$s,%3$sz m0,1 v-%3$s q0,%3$s %3$s,%3$sZ';
			// diagonal
			case !$invert && $check(0b10111011, 0b00000000): // 15
				return 'M%1$s,%2$s h%3$s q-%3$s,0 -%3$s,%3$sz m1,1 h-%3$s q%3$s,0 %3$s,-%3$sZ';
			case !$invert && $check(0b11101110, 0b00000000): // 37
				return 'M%1$s,%2$s m1,0 v%3$s q0,-%3$s -%3$s,-%3$sz m-1,1 v-%3$s q0,%3$s %3$s,%3$sZ';

			// 1 rounded
			case !$invert && $check(0b10000011, 0b01111100): // 1
			case  $invert && $check(0b10000010, 0b01010101):
				return 'M%1$s,%2$s h%3$s q-%3$s,0 -%3$s,%3$sZ';
			case !$invert && $check(0b00001110, 0b11110001): // 3
			case  $invert && $check(0b00001010, 0b01010101):
				return 'M%1$s,%2$s m1,0 v%3$s q0,-%3$s -%3$s,-%3$sZ';
			case !$invert && $check(0b00111000, 0b11000111): // 5
			case  $invert && $check(0b00101000, 0b01010101):
				return 'M%1$s,%2$s m1,1 h-%3$s q%3$s,0 %3$s,-%3$sZ';
			case !$invert && $check(0b11100000, 0b00011111): // 7
			case  $invert && $check(0b10100000, 0b01010101):
				return 'M%1$s,%2$s m0,1 v-%3$s q0,%3$s %3$s,%3$sZ';
		}

		// empty block
		return '';
	}

}


/**
 * the augmented options class
 */
class MeltedOutputOptions extends QROptions{

	/**
	 * enable "melt" effect
	 */
	protected bool $melt = false;

	/**
	 * whether to let the melt effect flow along the dark or light modules
	 */
	protected bool $inverseMelt = false;

	/**
	 * the corner radius for melted modules
	 */
	protected float $meltRadius = 0.15;

	/**
	 * clamp/set melt corner radius
	 */
	protected function set_meltRadius(float $meltRadius):void{
		$this->meltRadius = max(0.01, min(0.5, $meltRadius));
	}

}


/*
 * Runtime
 */

$options = new MeltedOutputOptions([
	'melt'               => true,
	'inverseMelt'        => true,
	'meltRadius'         => 0.4,

	'version'            => 7,
	'eccLevel'           => EccLevel::H,
	'addQuietzone'       => true,
	'addLogoSpace'       => true,
	'logoSpaceWidth'     => 13,
	'logoSpaceHeight'    => 13,
	'connectPaths'       => true,
	'imageBase64'        => false,

	'outputType'         => QROutputInterface::CUSTOM,
	'outputInterface'    => MeltedSVGQRCodeOutput::class,
	'excludeFromConnect' => [
		QRMatrix::M_FINDER_DARK,
		QRMatrix::M_FINDER_DOT,
	],
	'svgDefs'            => '
	<linearGradient id="rainbow" x1="100%" y2="100%">
		<stop stop-color="#e2453c" offset="2.5%"/>
		<stop stop-color="#e07e39" offset="21.5%"/>
		<stop stop-color="#e5d667" offset="40.5%"/>
		<stop stop-color="#51b95b" offset="59.5%"/>
		<stop stop-color="#1e72b7" offset="78.5%"/>
		<stop stop-color="#6f5ba7" offset="97.5%"/>
	</linearGradient>
	<style><![CDATA[
		.light, .dark{fill: url(#rainbow);}
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
