<?php
/**
 * String output example (console QR Codes for Lynx users!)
 *
 * @created      21.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QROutputInterface, QRString};

require_once __DIR__.'/../vendor/autoload.php';

$options = new QROptions;

$options->version       = 3;
$options->quietzoneSize = 2;
$options->outputType    = QROutputInterface::STRING_TEXT;
$options->eol           = "\n";
$options->textLineStart = str_repeat(' ', 6);
$options->textDark      = QRString::ansi8('██', 253);
$options->textLight     = QRString::ansi8('░░', 253);
$options->moduleValues  = [
	// finder
	QRMatrix::M_FINDER_DARK    => QRString::ansi8('██', 124),
	QRMatrix::M_FINDER         => QRString::ansi8('░░', 124),
	QRMatrix::M_FINDER_DOT     => QRString::ansi8('██', 124),
	// alignment
	QRMatrix::M_ALIGNMENT_DARK => QRString::ansi8('██', 2),
	QRMatrix::M_ALIGNMENT      => QRString::ansi8('░░', 2),
	// timing
	QRMatrix::M_TIMING_DARK    => QRString::ansi8('██', 184),
	QRMatrix::M_TIMING         => QRString::ansi8('░░', 184),
	// format
	QRMatrix::M_FORMAT_DARK    => QRString::ansi8('██', 200),
	QRMatrix::M_FORMAT         => QRString::ansi8('░░', 200),
	// version
	QRMatrix::M_VERSION_DARK   => QRString::ansi8('██', 21),
	QRMatrix::M_VERSION        => QRString::ansi8('░░', 21),
	// dark module
	QRMatrix::M_DARKMODULE     => QRString::ansi8('██', 53),
	// data
	QRMatrix::M_DATA_DARK      => QRString::ansi8('██', 166),
	QRMatrix::M_DATA           => QRString::ansi8('░░', 166),
];


$out  = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');


echo "\n\n\n$out\n\n\n";

exit;
