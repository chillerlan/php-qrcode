<?php
/**
 * Class QRGdImageAVIFTest
 *
 * @created      27.11.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRGdImageAVIF, QROutputInterface};
use chillerlan\Settings\SettingsContainerInterface;
use function function_exists;

final class QRGdImageAVIFTest extends QRGdImageTestAbstract{

	protected function setUp():void{
		parent::setUp();

		if(!function_exists('imageavif')){
			$this::markTestSkipped('no AVIF support');
		}

	}

	protected function getOutputInterface(
		SettingsContainerInterface|QROptions $options,
		QRMatrix                             $matrix,
	):QROutputInterface{
		return new QRGdImageAVIF($options, $matrix);
	}

}
