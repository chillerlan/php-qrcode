<?php
/**
 * Randomly colored modules example
 *
 * @see https://github.com/chillerlan/php-qrcode/discussions/136
 *
 * @created      09.07.2022
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2022 Smiley
 * @license      MIT
 *
 * @noinspection PhpIllegalPsrClassPathInspection
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QROutputInterface, QRMarkupSVG};

require_once __DIR__.'/../vendor/autoload.php';

/*
 * Class definition
 */

// the extended SVG output module
class RandomDotsSVGOutput extends QRMarkupSVG{

	/**
	 * @inheritDoc
	 */
	protected function path(string $path, int $M_TYPE):string{
		// omit the "fill" and "opacity" attributes on the path element
		return sprintf('<path class="%s" d="%s"/>', $this->getCssClass($M_TYPE), $path);
	}

	/**
	 * To alter the layer a module appears on, we need to re-implement the collection method
	 *
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

// the extended options with the $dotColors option
class RandomDotsOptions extends QROptions{

	/**
	 * a map of $M_TYPE_LAYER => color
	 *
	 * @see \array_rand()
	 */
	protected array $dotColors = [];

}


/*
 * Runtime
 */

// prepare the options
$options = new RandomDotsOptions;

// our custom dot colors
// adding the IS_DARK flag so that the proper layer css class is assigned
$options->dotColors = [
	(111 | QRMatrix::IS_DARK) => '#e2453c',
	(222 | QRMatrix::IS_DARK) => '#e07e39',
	(333 | QRMatrix::IS_DARK) => '#e5d667',
	(444 | QRMatrix::IS_DARK) => '#51b95b',
	(555 | QRMatrix::IS_DARK) => '#1e72b7',
	(666 | QRMatrix::IS_DARK) => '#6f5ba7',
];

// generate the CSS for the several colored layers
$layerColors = '';

foreach($options->dotColors as $layer => $color){
	$layerColors .= sprintf("\n\t\t.qr-%s{ fill: %s; }", $layer, $color);
}

$options->svgDefs = '
	<style><![CDATA[
		.dark{ fill: #424242; }
		'.$layerColors.'
	]]></style>';

// set the custom output interface
$options->outputType          = QROutputInterface::CUSTOM;
$options->outputInterface     = RandomDotsSVGOutput::class;

// common qrcode options
$options->version             = 5;
$options->eccLevel            = EccLevel::H;
$options->addQuietzone        = true;
$options->outputBase64        = false;
$options->drawLightModules    = false;
$options->connectPaths        = true;
$options->excludeFromConnect  = [
	QRMatrix::M_FINDER_DARK,
	QRMatrix::M_FINDER_DOT,
	QRMatrix::M_ALIGNMENT_DARK,
];
$options->drawCircularModules = true;
$options->circleRadius        = 0.4;
$options->keepAsSquare        = [
	QRMatrix::M_FINDER_DARK,
	QRMatrix::M_FINDER_DOT,
	QRMatrix::M_ALIGNMENT_DARK,
];



$out = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');


// dump the output
if(php_sapi_name() !== 'cli'){
	header('content-type: image/svg+xml');
}

echo $out;

exit;
