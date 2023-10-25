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
use chillerlan\QRCode\Output\{QROutputInterface, QRStringText};

require_once __DIR__.'/../vendor/autoload.php';

$options = new QROptions;

$options->version       = 3;
$options->quietzoneSize = 2;
$options->outputType    = QROutputInterface::STRING_TEXT;
$options->eol           = "\n";
$options->textLineStart = str_repeat(' ', 6);
$options->moduleValues  = [
	QRMatrix::M_FINDER_DARK    => QRStringText::ansi8('██', 124),
	QRMatrix::M_FINDER         => QRStringText::ansi8('░░', 124),
	QRMatrix::M_FINDER_DOT     => QRStringText::ansi8('██', 124),
	QRMatrix::M_ALIGNMENT_DARK => QRStringText::ansi8('██', 2),
	QRMatrix::M_ALIGNMENT      => QRStringText::ansi8('░░', 2),
	QRMatrix::M_TIMING_DARK    => QRStringText::ansi8('██', 184),
	QRMatrix::M_TIMING         => QRStringText::ansi8('░░', 184),
	QRMatrix::M_FORMAT_DARK    => QRStringText::ansi8('██', 200),
	QRMatrix::M_FORMAT         => QRStringText::ansi8('░░', 200),
	QRMatrix::M_VERSION_DARK   => QRStringText::ansi8('██', 21),
	QRMatrix::M_VERSION        => QRStringText::ansi8('░░', 21),
	QRMatrix::M_DARKMODULE     => QRStringText::ansi8('██', 53),
	QRMatrix::M_DATA_DARK      => QRStringText::ansi8('██', 166),
	QRMatrix::M_DATA           => QRStringText::ansi8('░░', 166),
	QRMatrix::M_QUIETZONE      => QRStringText::ansi8('░░', 253),
	QRMatrix::M_SEPARATOR      => QRStringText::ansi8('░░', 253),
];


$out  = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');


printf("\n\n\n%s\n\n\n", $out);

exit;
