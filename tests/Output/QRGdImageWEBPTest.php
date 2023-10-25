<?php
/**
 * Class QRGdImageWEBPTest
 *
 * @created      05.09.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Output\QRGdImageWEBP;
use chillerlan\QRCode\Output\QROutputInterface;

/**
 *
 */
final class QRGdImageWEBPTest extends QRGdImageTestAbstract{

	protected string $type = QROutputInterface::GDIMAGE_WEBP;
	protected string $FQN  = QRGdImageWEBP::class;

}
