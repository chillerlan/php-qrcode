<?php
/**
 * @created      21.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeExamples;

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Common\EccLevel;

require_once __DIR__.'/../vendor/autoload.php';

$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';

$options = new QROptions([
	'version'      => 5,
	'outputType'   => QRCode::OUTPUT_STRING_TEXT,
	'eccLevel'     => EccLevel::L,
]);

// <pre> to view it in a browser
echo '<pre style="font-size: 75%; line-height: 1;">'.(new QRCode($options))->render($data).'</pre>';


// custom values
$options = new QROptions([
	'version'      => 5,
	'outputType'   => QRCode::OUTPUT_STRING_TEXT,
	'eccLevel'     => EccLevel::L,
	'moduleValues' => [
		// finder
		QRMatrix::M_FINDER | QRMatrix::IS_DARK     => 'A', // dark (true)
		QRMatrix::M_FINDER                         => 'a', // light (false)
		QRMatrix::M_FINDER_DOT | QRMatrix::IS_DARK => 'ä', // finder dot, dark (true)
		// alignment
		QRMatrix::M_ALIGNMENT | QRMatrix::IS_DARK  => 'B',
		QRMatrix::M_ALIGNMENT                      => 'b',
		// timing
		QRMatrix::M_TIMING | QRMatrix::IS_DARK     => 'C',
		QRMatrix::M_TIMING                         => 'c',
		// format
		QRMatrix::M_FORMAT | QRMatrix::IS_DARK     => 'D',
		QRMatrix::M_FORMAT                         => 'd',
		// version
		QRMatrix::M_VERSION | QRMatrix::IS_DARK    => 'E',
		QRMatrix::M_VERSION                        => 'e',
		// data
		QRMatrix::M_DATA | QRMatrix::IS_DARK       => 'F',
		QRMatrix::M_DATA                           => 'f',
		// darkmodule
		QRMatrix::M_DARKMODULE | QRMatrix::IS_DARK => 'G',
		// separator
		QRMatrix::M_SEPARATOR                      => 'h',
		// quietzone
		QRMatrix::M_QUIETZONE                      => 'i',
	],
]);

// <pre> to view it in a browser
echo '<pre style="font-size: 75%; line-height: 1;">'.(new QRCode($options))->render($data).'</pre>';





