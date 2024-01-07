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

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRGdImageBMP, QROutputInterface};

/**
 *
 */
final class QRGdImageBMPTest extends QRGdImageTestAbstract{

	protected string $type = QROutputInterface::GDIMAGE_BMP;

	protected function getOutputInterface(QROptions $options, QRMatrix $matrix):QROutputInterface{
		return new QRGdImageBMP($options, $matrix);
	}

}
