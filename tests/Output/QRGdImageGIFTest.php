<?php
/**
 * Class QRGdImageGIFTest
 *
 * @created      11.12.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Output\QRGdImageGIF;
use chillerlan\QRCode\Output\QROutputInterface;

/**
 *
 */
final class QRGdImageGIFTest extends QRGdImageTestAbstract{

	protected string $type = QROutputInterface::GDIMAGE_GIF;
	protected string $FQN  = QRGdImageGIF::class;

}
