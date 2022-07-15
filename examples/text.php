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
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;
use PHPUnit\Util\Color;

require_once __DIR__.'/../vendor/autoload.php';

$options = new QROptions([
	'version'      => 7,
	'outputType'   => QROutputInterface::STRING_TEXT,
	'eccLevel'     => EccLevel::L,
	'eol'          => Color::colorize('reset', "\x00\n"),
	'moduleValues' => [
		// finder
		QRMatrix::M_FINDER | QRMatrix::IS_DARK     => Color::colorize('fg-black', '🔴'), // dark (true)
		QRMatrix::M_FINDER                         => Color::colorize('fg-black', '⭕'), // light (false)
		QRMatrix::M_FINDER_DOT | QRMatrix::IS_DARK => Color::colorize('fg-black', '🔴'), // finder dot, dark (true)
		// alignment
		QRMatrix::M_ALIGNMENT | QRMatrix::IS_DARK  => Color::colorize('fg-blue', '🔴'),
		QRMatrix::M_ALIGNMENT                      => Color::colorize('fg-blue', '⭕'),
		// timing
		QRMatrix::M_TIMING | QRMatrix::IS_DARK     => Color::colorize('fg-red', '🔴'),
		QRMatrix::M_TIMING                         => Color::colorize('fg-red', '⭕'),
		// format
		QRMatrix::M_FORMAT | QRMatrix::IS_DARK     => Color::colorize('fg-magenta', '🔴'),
		QRMatrix::M_FORMAT                         => Color::colorize('fg-magenta', '⭕'),
		// version
		QRMatrix::M_VERSION | QRMatrix::IS_DARK    => Color::colorize('fg-green', '🔴'),
		QRMatrix::M_VERSION                        => Color::colorize('fg-green', '⭕'),
		// data
		QRMatrix::M_DATA | QRMatrix::IS_DARK       => Color::colorize('fg-white', '🔴'),
		QRMatrix::M_DATA                           => Color::colorize('fg-white', '⭕'),
		// darkmodule
		QRMatrix::M_DARKMODULE | QRMatrix::IS_DARK => Color::colorize('fg-black', '🔴'),
		// separator
		QRMatrix::M_SEPARATOR                      => Color::colorize('fg-cyan', '⭕'),
		// quietzone
		QRMatrix::M_QUIETZONE                      => Color::colorize('fg-cyan', '⭕'),
		// logo space
		QRMatrix::M_LOGO                           => Color::colorize('fg-yellow', '⭕'),
		// empty
		QRMatrix::M_NULL                           => Color::colorize('fg-black', '⭕'),
		// data
		QRMatrix::M_TEST | QRMatrix::IS_DARK       => Color::colorize('fg-white', '🔴'),
		QRMatrix::M_TEST                           => Color::colorize('fg-black', '⭕'),
	],
]);

echo (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

exit;
