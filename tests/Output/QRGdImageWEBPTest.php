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

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRGdImageWEBP, QROutputInterface};

/**
 *
 */
final class QRGdImageWEBPTest extends QRGdImageTestAbstract{

	protected string $type = QROutputInterface::GDIMAGE_WEBP;

	protected function getOutputInterface(QROptions $options, QRMatrix $matrix):QROutputInterface{
		return new QRGdImageWEBP($options, $matrix);
	}

}
