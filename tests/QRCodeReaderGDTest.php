<?php
/**
 * Class QRCodeReaderGDTest
 *
 * @created      12.12.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest;

use chillerlan\QRCode\Common\{GDLuminanceSource, LuminanceSourceInterface};
use chillerlan\QRCode\QROptions;
use chillerlan\Settings\SettingsContainerInterface;

/**
 * Tests the GD based reader
 */
final class QRCodeReaderGDTest extends QRCodeReaderTestAbstract{

	protected function getLuminanceSourceFromFile(
		string                               $file,
		SettingsContainerInterface|QROptions $options,
	):LuminanceSourceInterface{
		return GDLuminanceSource::fromFile($file, $options);
	}

}
