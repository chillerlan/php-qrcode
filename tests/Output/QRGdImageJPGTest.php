<?php
/**
 * Class QRGdImageJPGTest
 *
 * @created      11.12.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Output\QRGdImageJPEG;
use chillerlan\QRCode\Output\QROutputInterface;

/**
 *
 */
final class QRGdImageJPGTest extends QRGdImageTestAbstract{

	protected string $type = QROutputInterface::GDIMAGE_JPG;
	protected string $FQN  = QRGdImageJPEG::class;

}
