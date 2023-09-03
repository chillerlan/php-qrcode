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
use chillerlan\QRCode\Output\QROutputInterface;

require_once __DIR__.'/../vendor/autoload.php';

$options = new QROptions;

$options->version       = 3;
$options->quietzoneSize = 2;
$options->outputType    = QROutputInterface::STRING_TEXT;
$options->eol           = "\n";
$options->textLineStart = str_repeat(' ', 6);
$options->textDark      = ansi8('██', 253);
$options->textLight     = ansi8('░░', 253);
$options->moduleValues  = [
	// finder
	QRMatrix::M_FINDER_DARK    => ansi8('██', 124),
	QRMatrix::M_FINDER         => ansi8('░░', 124),
	QRMatrix::M_FINDER_DOT     => ansi8('██', 124),
	// alignment
	QRMatrix::M_ALIGNMENT_DARK => ansi8('██', 2),
	QRMatrix::M_ALIGNMENT      => ansi8('░░', 2),
	// timing
	QRMatrix::M_TIMING_DARK    => ansi8('██', 184),
	QRMatrix::M_TIMING         => ansi8('░░', 184),
	// format
	QRMatrix::M_FORMAT_DARK    => ansi8('██', 200),
	QRMatrix::M_FORMAT         => ansi8('░░', 200),
	// version
	QRMatrix::M_VERSION_DARK   => ansi8('██', 21),
	QRMatrix::M_VERSION        => ansi8('░░', 21),
	// dark module
	QRMatrix::M_DARKMODULE     => ansi8('██', 53),
	// data
	QRMatrix::M_DATA_DARK      => ansi8('██', 166),
	QRMatrix::M_DATA           => ansi8('░░', 166),
];


$out  = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');


echo "\n\n\n$out\n\n\n";

exit;


// a little helper to a create proper ANSI 8-bit color escape sequence
function ansi8(string $str, int $color, bool $background = false):string{
	$color      = max(0, min($color, 255));
	$background = ($background ? 48 : 38);

	return sprintf("\x1b[%s;5;%sm%s\x1b[0m", $background, $color, $str);
}
