<?php
/**
 * Class QRGdImageJPGTest
 *
 * @created      11.12.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRGdImageJPEG, QROutputInterface};
use chillerlan\Settings\SettingsContainerInterface;

final class QRGdImageJPGTest extends QRGdImageTestAbstract{

	protected function getOutputInterface(
		SettingsContainerInterface|QROptions $options,
		QRMatrix                             $matrix,
	):QROutputInterface{
		return new QRGdImageJPEG($options, $matrix);
	}

}
