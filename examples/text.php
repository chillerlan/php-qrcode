<?php
/**
 * @created      21.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;

require_once __DIR__.'/../vendor/autoload.php';

/**
 * a little helper to create a proper ANSI 8-bit color escape sequence
 *
 * @see https://en.wikipedia.org/wiki/ANSI_escape_code#8-bit
 * @see https://en.wikipedia.org/wiki/Block_Elements
 *
 * @codeCoverageIgnore
 */
function ansi8(string $str, int $color, bool $background = null):string{
	$color      = max(0, min($color, 255));
	$background = ($background === true) ? 48 : 38;

	return sprintf("\x1b[%s;5;%sm%s\x1b[0m", $background, $color, $str);
}


$options = new QROptions([
	'version'      => 5,
	'outputType'   => QRCode::OUTPUT_STRING_TEXT,
	'eccLevel'     => QRCode::ECC_L,
	'moduleValues' => [
		// finder
		QRMatrix::M_FINDER_DARK    => ansi8('██', 124), // dark (true)
		QRMatrix::M_FINDER_DOT     => ansi8('██', 124), // dark (true)
		QRMatrix::M_FINDER         => ansi8('░░', 124), // light (false)
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
		// data
		QRMatrix::M_DATA_DARK      => ansi8('██', 166),
		QRMatrix::M_DATA           => ansi8('░░', 166),
		// darkmodule
		QRMatrix::M_DARKMODULE     => ansi8('██', 53),
		// separator
		QRMatrix::M_SEPARATOR      => ansi8('░░', 253),
		// quietzone
		QRMatrix::M_QUIETZONE      => ansi8('░░', 253),
	],
]);

echo (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
