<?php
/**
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
		$paths = [];

		// collect the modules for each type
		for($y = 0; $y < $this->moduleCount; $y++){
			for($x = 0; $x < $this->moduleCount; $x++){
				$M_TYPE       = $this->matrix->get($x, $y);
				$M_TYPE_LAYER = $M_TYPE;

				if($this->options->connectPaths
				   && !$this->matrix->checkTypeIn($x, $y, $this->options->excludeFromConnect)
				){
					// to connect paths we'll redeclare the $M_TYPE_LAYER to data only
					$M_TYPE_LAYER = QRMatrix::M_DATA;

					if($this->matrix->check($x, $y)){
						$M_TYPE_LAYER |= QRMatrix::IS_DARK;
					}
				}

				// randomly assign another $M_TYPE_LAYER for the given types
				// note that the layer id has to be an integer value,
				// ideally outside the several bitmask values
				if($M_TYPE_LAYER === QRMatrix::M_DATA_DARK){
					$M_TYPE_LAYER = array_rand($this->options->dotColors);
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

// our custom dot colors
$dotColors = [
	111 => '#e2453c',
	222 => '#e07e39',
	333 => '#e5d667',
	444 => '#51b95b',
	555 => '#1e72b7',
	666 => '#6f5ba7',
];

// generate the CSS for the several colored layers
$layerColors = '';

foreach($dotColors as $layer => $color){
	$layerColors .= sprintf("\n\t\t.qr-%s{ fill: %s; }", $layer, $color);
}

// prepare the options
$options = new RandomDotsOptions([
	'dotColors'           => $dotColors,
	'svgDefs'             => '
	<style><![CDATA[
		.light{ fill: #dedede; }
		.dark{ fill: #424242; }
		'.$layerColors.'
	]]></style>',

	'version'             => 5,
	'eccLevel'            => EccLevel::H,
	'addQuietzone'        => true,
	'imageBase64'         => false,
	'outputType'          => QROutputInterface::CUSTOM,
	'outputInterface'     => RandomDotsSVGOutput::class,
	'drawLightModules'    => false,

	'connectPaths'        => true,
	'excludeFromConnect'  => [
		QRMatrix::M_FINDER_DARK,
		QRMatrix::M_FINDER_DOT,
		QRMatrix::M_ALIGNMENT_DARK,
	],

	'drawCircularModules' => true,
	'circleRadius'        => 0.4,
	'keepAsSquare'        => [
		QRMatrix::M_FINDER_DARK,
		QRMatrix::M_FINDER_DOT,
		QRMatrix::M_ALIGNMENT_DARK,
	],

]);

// dump the output
if(php_sapi_name() !== 'cli'){
	header('content-type: image/svg+xml');
}

echo (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

exit;
