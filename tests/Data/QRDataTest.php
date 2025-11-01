<?php
/**
 * Class QRDataTest
 *
 * @created      08.08.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Data;

use PHPUnit\Framework\Attributes\Test;
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\{BitBuffer, EccLevel, MaskPattern};
use chillerlan\QRCode\Data\{Byte, QRData};
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCodeTest\Traits\QRMatrixDebugTrait;
use PHPUnit\Framework\TestCase;

final class QRDataTest extends TestCase{
	use QRMatrixDebugTrait;

	/**
	 * tests setting the BitBuffer object directly
	 */
	#[Test]
	public function setBitBuffer():void{
		$rawBytes = [
			67, 22, 135, 71, 71, 7, 51, 162, 242, 247, 119, 119, 114, 231, 150, 247,
			87, 71, 86, 38, 82, 230, 54, 246, 210, 247, 118, 23, 70, 54, 131, 247,
			99, 212, 68, 199, 167, 135, 39, 164, 100, 55, 148, 247, 50, 103, 67, 211,
			67, 55, 48, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236,
			17, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236,
			17, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236,
			17, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236,
			17, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236,
			17, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236,
			17, 236, 17, 236, 17, 236, 17, 236, 17, 236, 17, 236,
		];

		$options     = new QROptions(['version' => 3]);
		$bitBuffer   = new BitBuffer($rawBytes);
		$matrix      = (new QRData($options))->setBitBuffer($bitBuffer)->writeMatrix();
		$maskPattern = MaskPattern::getBestPattern($matrix);

		$matrix->setFormatInfo($maskPattern)->mask($maskPattern);

		$this::assertSame(3, $matrix->getVersion()->getVersionNumber());

		// attempt to read
		$options->outputBase64                = false;
		$options->readerUseImagickIfAvailable = false;

		$output       = new QRGdImagePNG($options, $matrix);
		$decodeResult = (new QRCode($options))->readFromBlob($output->dump());

		$this->debugMatrix($matrix);

		$this::assertSame('https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s', $decodeResult->data);
	}

	#[Test]
	public function estimateTotalBitLength():void{

		$options = new QROptions([
			'versionMin'          => 10,
			'quietzoneSize'       => 2,
			'eccLevel'            => EccLevel::H,
			'outputBase64'        => false,
			'cssClass'            => 'pma-2fa-qrcode',
			'drawCircularModules' => true,
		]);

		// version 10H has a maximum of 976 bits, which is the exact length of the string below
		// QRData::estimateTotalBitLength() used to substract 4 bits for a hypothetical data mode indicator
		// we're now going the safe route and do not do that anymore...
		$str = 'otpauth://totp/user?secret=P2SXMJFJ7DJGHLVEQYBNH2EYM4FH66CR'.
		       '&issuer=phpMyAdmin%20%28%29&digits=6&algorithm=SHA1&period=30';

		$qrData = new QRData($options, [new Byte($str)]);

		$this::assertSame(976, $qrData->estimateTotalBitLength());
		$this::assertSame(11, $qrData->getMinimumVersion()->getVersionNumber()); // version adjusted to 11
	}

}
