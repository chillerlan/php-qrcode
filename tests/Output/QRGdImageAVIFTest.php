<?php
/**
 * Class QRGdImageAVIFTest
 *
 * @created      27.11.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

namespace Output;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCodeTest\Output\QRGdImageTestAbstract;
use chillerlan\QRCode\Output\{QRGdImageAVIF, QROutputInterface};
use chillerlan\Settings\SettingsContainerInterface;

/**
 *
 */
final class QRGdImageAVIFTest extends QRGdImageTestAbstract{

	protected function getOutputInterface(
		SettingsContainerInterface|QROptions $options,
		QRMatrix                             $matrix
	):QROutputInterface{
		return new QRGdImageAVIF($options, $matrix);
	}

}
