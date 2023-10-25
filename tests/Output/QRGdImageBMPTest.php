<?php
/**
 * Class QRGdImageBMPTest
 *
 * @created      05.09.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Output\QRGdImageBMP;
use chillerlan\QRCode\Output\QROutputInterface;

/**
 *
 */
final class QRGdImageBMPTest extends QRGdImageTestAbstract{

	protected string $type = QROutputInterface::GDIMAGE_BMP;
	protected string $FQN  = QRGdImageBMP::class;

}
