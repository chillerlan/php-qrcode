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

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRGdImageJPEG, QROutputInterface};

/**
 *
 */
final class QRGdImageJPGTest extends QRGdImageTestAbstract{

	protected string $type = QROutputInterface::GDIMAGE_JPG;

	protected function getOutputInterface(QROptions $options, QRMatrix $matrix):QROutputInterface{
		return new QRGdImageJPEG($options, $matrix);
	}

}
