<?php
/**
 * Class QRMarkupXMLTest
 *
 * @created      01.05.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRMarkupXML, QROutputInterface};
use chillerlan\Settings\SettingsContainerInterface;

class QRMarkupXMLTest extends QRMarkupTestAbstract{

	protected function getOutputInterface(
		SettingsContainerInterface|QROptions $options,
		QRMatrix                             $matrix,
	):QROutputInterface{
		return new QRMarkupXML($options, $matrix);
	}

}
