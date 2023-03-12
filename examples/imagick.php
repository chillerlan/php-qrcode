<?php
/**
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;

require_once __DIR__.'/../vendor/autoload.php';

$options = new QROptions([
	'version'      => 7,
	'outputType'   => QRCode::OUTPUT_IMAGICK,
	'eccLevel'     => QRCode::ECC_L,
	'scale'        => 5,
	'moduleValues' => [
		// finder
		(QRMatrix::M_FINDER << 8)     => '#A71111', // dark (true)
		(QRMatrix::M_FINDER_DOT << 8) => '#A71111',
		QRMatrix::M_FINDER            => '#FFBFBF', // light (false)
		// alignment
		(QRMatrix::M_ALIGNMENT << 8)  => '#A70364',
		QRMatrix::M_ALIGNMENT         => '#FFC9C9',
		// timing
		(QRMatrix::M_TIMING << 8)     => '#98005D',
		QRMatrix::M_TIMING            => '#FFB8E9',
		// format
		(QRMatrix::M_FORMAT << 8)     => '#003804',
		QRMatrix::M_FORMAT            => '#00FB12',
		// version
		(QRMatrix::M_VERSION << 8)    => '#650098',
		QRMatrix::M_VERSION           => '#E0B8FF',
		// data
		(QRMatrix::M_DATA << 8)       => '#4A6000',
		QRMatrix::M_DATA              => '#ECF9BE',
		// darkmodule
		(QRMatrix::M_DARKMODULE << 8) => '#080063',
		// separator
		QRMatrix::M_SEPARATOR         => '#DDDDDD',
		// quietzone
		QRMatrix::M_QUIETZONE         => '#DDDDDD',
	],
]);


header('Content-type: image/png');

echo (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
