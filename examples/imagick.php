<?php
/**
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;

require_once __DIR__.'/../vendor/autoload.php';

$options = new QROptions([
	'version'             => 7,
	'outputType'          => QROutputInterface::IMAGICK,
	'eccLevel'            => EccLevel::L,
	'bgColor'             => '#cccccc', // overrides the imageTransparent setting
	'imageTransparent'    => true,
	'scale'               => 20,
	'drawLightModules'    => true,
	'drawCircularModules' => true,
	'circleRadius'        => 0.4,
	'keepAsSquare'        => [QRMatrix::M_FINDER|QRMatrix::IS_DARK, QRMatrix::M_FINDER_DOT, QRMatrix::M_ALIGNMENT|QRMatrix::IS_DARK],
	'moduleValues'        => [
		// finder
		QRMatrix::M_FINDER | QRMatrix::IS_DARK     => '#A71111', // dark (true)
		QRMatrix::M_FINDER                         => '#FFBFBF', // light (false)
		QRMatrix::M_FINDER_DOT | QRMatrix::IS_DARK => '#A71111', // finder dot, dark (true)
		// alignment
		QRMatrix::M_ALIGNMENT | QRMatrix::IS_DARK  => '#A70364',
		QRMatrix::M_ALIGNMENT                      => '#FFC9C9',
		// timing
		QRMatrix::M_TIMING | QRMatrix::IS_DARK     => '#98005D',
		QRMatrix::M_TIMING                         => '#FFB8E9',
		// format
		QRMatrix::M_FORMAT | QRMatrix::IS_DARK     => '#003804',
		QRMatrix::M_FORMAT                         => '#CCFB12',
		// version
		QRMatrix::M_VERSION | QRMatrix::IS_DARK    => '#650098',
		QRMatrix::M_VERSION                        => '#E0B8FF',
		// data
		QRMatrix::M_DATA | QRMatrix::IS_DARK       => '#4A6000',
		QRMatrix::M_DATA                           => '#ECF9BE',
		// darkmodule
		QRMatrix::M_DARKMODULE | QRMatrix::IS_DARK => '#080063',
		// separator
		QRMatrix::M_SEPARATOR                      => '#DDDDDD',
		// quietzone
		QRMatrix::M_QUIETZONE                      => '#DDDDDD',
	],
]);

header('Content-type: image/png');

echo (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

exit;
