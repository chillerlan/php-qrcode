<?php
/**
 * Class QRGdImageBMPTest
 *
 * @created      05.09.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRGdImageBMP, QROutputInterface};
use chillerlan\Settings\SettingsContainerInterface;

final class QRGdImageBMPTest extends QRGdImageTestAbstract{

	protected function getOutputInterface(
		SettingsContainerInterface|QROptions $options,
		QRMatrix                             $matrix,
	):QROutputInterface{
		return new QRGdImageBMP($options, $matrix);
	}

}
