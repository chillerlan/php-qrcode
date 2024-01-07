<?php
/**
 * Class QRGdImagePNGTest
 *
 * @created      11.12.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRGdImagePNG, QROutputInterface};

/**
 *
 */
final class QRGdImagePNGTest extends QRGdImageTestAbstract{

	protected string $type = QROutputInterface::GDIMAGE_PNG;

	protected function getOutputInterface(QROptions $options, QRMatrix $matrix):QROutputInterface{
		return new QRGdImagePNG($options, $matrix);
	}

}
