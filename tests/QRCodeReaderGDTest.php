<?php
/**
 * Class QRCodeReaderGDTest
 *
 * @created      12.12.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest;

use chillerlan\QRCode\Decoder\GDLuminanceSource;

/**
 * Tests the GD based reader
 */
final class QRCodeReaderGDTest extends QRCodeReaderTestAbstract{

	protected string $FQN = GDLuminanceSource::class;

}
