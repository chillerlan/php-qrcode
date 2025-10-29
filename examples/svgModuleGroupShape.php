<?php
/**
 * Module group shape, by Rastian95
 *
 * This example works very similar to the "melted" modules, but creates a different effect
 *
 * @see https://github.com/chillerlan/php-qrcode/discussions/233
 */
declare(strict_types=1);

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRMarkupSVG;

require_once __DIR__.'/../vendor/autoload.php';

/*
 * Class definition
 */

/**
 *  the extended SVG output module
 */
class GroupShapeSVGQRCodeOutput extends QRMarkupSVG{

	protected function path(string $path, int $M_TYPE):string{
		// omit the "fill" and "opacity" attributes on the path element
		return sprintf('<path class="%s" d="%s"/>', $this->getCssClass($M_TYPE), $path);
	}

	protected function collectModules():array{
		$paths = [];
		$melt  = $this->options->melt; // avoid magic getter in long loops

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

				// if we're going to "melt" the matrix, we'll declare *all* modules as dark,
				// so that light modules with dark parts are rendered in the same path
				if($melt){
					$M_TYPE_LAYER |= QRMatrix::IS_DARK;
				}

				// collect the modules per $M_TYPE
				$module = $this->moduleTransform($x, $y, $M_TYPE, $M_TYPE_LAYER);

				if($module !== null){
					$paths[$M_TYPE_LAYER][] = $module;
				}
			}
		}

		// beautify output
		ksort($paths);

		return $paths;
	}

	protected function moduleTransform(int $x, int $y, int $M_TYPE, int $M_TYPE_LAYER):string|null{
		$bits     = $this->matrix->checkNeighbours($x, $y, null);
		$check    = fn(int $all, int $any = 0):bool => ($bits & ($all | (~$any & 0xff))) === $all;

		$template = ($M_TYPE & QRMatrix::IS_DARK) === QRMatrix::IS_DARK
			? $this->darkModule($check, $this->options->inverseMelt)
			: $this->lightModule($check, $this->options->inverseMelt);

		if($template === ''){
			return null;
		}

		$r = $this->options->meltRadius;

		return sprintf($template, $x, $y, $r, (1 - $r), (1 - 2 * $r));
	}

	/**
	 * returns a dark module for the given values
	 */
	protected function darkModule(Closure $check, bool $invert):string {
		return match (true) {
			!$invert && $check(0b00000000, 0b01010101),
			 $invert && $check(0b00000000, 0b00000000)
				=> 'M%1$s %2$s m0.5,0 l0.5,0.5 l-0.5,0.5 l-0.5,-0.5Z',
			 $invert && $check(0b01000000, 0b00000000)
				=> 'M%1$s,%2$s m0,1 h%4$s q%3$s,0 %3$s,-%3$s v-%5$s q0,-%3$s -%3$s,-%3$s h-%5$s q-%3$s,0 -%3$s,%3$sZ',
			 $invert && $check(0b00000001, 0b00000000)
				=> 'M%1$s,%2$s v%4$s q0,%3$s %3$s,%3$s h%5$s q%3$s,0 %3$s,-%3$s v-%5$s q0,-%3$s -%3$s,-%3$sZ',
			 $invert && $check(0b00000100, 0b00000000)
				=> 'M%1$s,%2$s m1,0 v%4$s q0,%3$s -%3$s,%3$s h-%5$s q-%3$s,0 -%3$s,-%3$s v-%5$s q0,-%3$s %3$s,-%3$sZ',
			 $invert && $check(0b00010000, 0b00000000)
				=> 'M%1$s,%2$s m1,1 h-%4$s q-%3$s,0 -%3$s,-%3$s v-%5$s q0,-%3$s %3$s,-%3$s h%5$s q%3$s,0 %3$s,%3$sZ',
			!$invert && $check(0b00100000, 0b01010101),
			 $invert && $check(0b00000000, 0b01110000)
				=> 'M%1$s,%2$s m0,1 h1 l-0.5,-1Z',
			!$invert && $check(0b10000000, 0b01010101),
			 $invert && $check(0b00000000, 0b11000001)
				=> 'M%1$s,%2$s v1 l1,-0.5Z',
			!$invert && $check(0b00000010, 0b01010101),
			 $invert && $check(0b00000000, 0b00000111)
				=> 'M%1$s,%2$s h1 l-0.5,1Z',
			!$invert && $check(0b00001000, 0b01010101),
			 $invert && $check(0b00000000, 0b00011100)
				=> 'M%1$s,%2$s m1,1 v-1 l-1,0.5Z',
			 $invert && $check(0b01000100, 0b00000000)
				=> 'M%1$s,%2$s m0,1 h%4$s q%3$s,0 %3$s,-%3$s v-%4$s h-%4$s q-%3$s,0 -%3$s,%3$sZ',
			 $invert && $check(0b00010001, 0b00000000)
				=> 'M%1$s,%2$s h%4$s q%3$s,0 %3$s,%3$s v%4$s h-%4$s q-%3$s,0 -%3$s,-%3$sZ',
			!$invert && $check(0b00101000, 0b01010101),
			 $invert && $check(0b00000000, 0b01111100)
				=> 'M%1$s,%2$s m0,1 h1 v-1 h-%4$s q-%3$s,0 -%3$s,%3$sZ',
			!$invert && $check(0b10100000, 0b01010101),
			 $invert && $check(0b00000000, 0b11110001)
				=> 'M%1$s,%2$s h%4$s q%3$s,0 %3$s,%3$s v%4$s h-1Z',
			!$invert && $check(0b10000010, 0b01010101),
			 $invert && $check(0b00000000, 0b11000111)
				=> 'M%1$s,%2$s h1 v%4$s q0,%3$s -%3$s,%3$s h-%4$sZ',
			!$invert && $check(0b00001010, 0b01010101),
			 $invert && $check(0b00000000, 0b00011111)
				=> 'M%1$s,%2$s v%4$s q0,%3$s %3$s,%3$s h%4$s v-1Z',
			default
				=> 'M%1$s,%2$s h1 v1 h-1Z',
		};
	}

	/**
	 * returns a light module for the given values
	 */
	protected function lightModule(Closure $check, bool $invert):string {
		return match (true) {
			!$invert && $check(0b11111111, 0b01010101), $invert && $check(0b10101010, 0b01010101)
				=> 'M%1$s,%2$s h%3$s q-%3$s,0 -%3$s,%3$sz m1,0 v%3$s q0,-%3$s -%3$s,-%3$sz m0,1 h-%3$s q%3$s,0 %3$s,-%3$sz m-1,0 v-%3$s q0,%3$s %3$s,%3$sZ',
			!$invert && $check(0b10111111, 0b00000000)
				=> 'M%1$s,%2$s h%3$sz m1,0 v%3$s q0,-%3$s -%3$s,-%3$sz m0,1 h-%3$s Z',
			!$invert && $check(0b11111110, 0b00000000)
				=> 'M%1$s,%2$s m1,0 v%3$s z m0,1 h-%3$s q%3$s,0 %3$s,-%3$sz m-1,0 v-%3$s Z',
			!$invert && $check(0b11111011, 0b00000000)
				=> 'M%1$s,%2$s h%3$s z m0,1 v-%3$s q0,%3$s %3$s,%3$sz m1,0 h-%3$s Z',
			!$invert && $check(0b11101111, 0b00000000)
				=> 'M%1$s,%2$s h%3$s q-%3$s,0 -%3$s,%3$sz m0,1 v-%3$s z m1,-1 v%3$s Z',
			!$invert && $check(0b10001111, 0b01110000),
			 $invert && $check(0b10001010, 0b01010101),
			!$invert && $check(0b00111110, 0b11000001),
			 $invert && $check(0b00101010, 0b01010101),
			!$invert && $check(0b11111000, 0b00000111),
			 $invert && $check(0b10101000, 0b01010101),
			!$invert && $check(0b11100011, 0b00011100),
			 $invert && $check(0b10100010, 0b01010101),
			!$invert && $check(0b10111011, 0b00000000),
			!$invert && $check(0b11101110, 0b00000000),
			!$invert && $check(0b10000011, 0b01111100),
			 $invert && $check(0b10000010, 0b01010101),
			!$invert && $check(0b00001110, 0b11110001),
			 $invert && $check(0b00001010, 0b01010101),
			!$invert && $check(0b00111000, 0b11000111),
			 $invert && $check(0b00101000, 0b01010101),
			!$invert && $check(0b11100000, 0b00011111),
			 $invert && $check(0b10100000, 0b01010101)
				=> 'M%1$s,%2$s ',
			default => '',
		};
	}

}


/**
 * the augmented options class
 *
 * @property bool $melt
 * @property bool $inverseMelt
 * @property float $meltRadius
 */
class GroupShapeOutputOptions extends QROptions{

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
$options = new GroupShapeOutputOptions;

// settings from the custom options class
$options->melt               = true;
$options->inverseMelt        = true;
$options->meltRadius         = 0.5;

$options->version            = 5;
$options->outputInterface    = GroupShapeSVGQRCodeOutput::class;
$options->outputBase64       = false;
$options->addQuietzone       = true;
$options->eccLevel           = EccLevel::H;
$options->addLogoSpace       = true;
$options->logoSpaceWidth     = 13;
$options->logoSpaceHeight    = 13;
$options->connectPaths       = true;
$options->excludeFromConnect = [
	QRMatrix::M_FINDER_DARK,
	QRMatrix::M_FINDER_DOT,
];
$options->svgDefs            = '
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
