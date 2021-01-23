<?php
/**
 * Class ECI
 *
 * @filesource   ECI.php
 * @created      20.11.2020
 * @package      chillerlan\QRCode\Data
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\Common\{BitBuffer, Mode};

/**
 * Adds an ECI Designator
 *
 * Please note that you have to take care for the correct data encoding when adding with QRCode::add*Segment()
 */
final class ECI extends QRDataModeAbstract{

	protected static int $datamode = Mode::DATA_ECI;

	/**
	 * The current ECI encoding id
	 */
	protected int $encoding;

	/**
	 * @inheritDoc
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct(int $encoding){
		$this->encoding = $encoding;
	}

	/**
	 * @inheritDoc
	 */
	public function getLengthInBits():int{
		return 8;
	}

		/**
	 * @inheritDoc
	 */
	public function write(BitBuffer $bitBuffer, int $versionNumber):void{
		$bitBuffer
			->put($this::$datamode, 4)
			->put($this->encoding, 8)
		;
	}

	/**
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public static function parseValue(BitBuffer $bitBuffer):int{
		$firstByte = $bitBuffer->read(8);

		if(($firstByte & 0x80) === 0){
			// just one byte
			return $firstByte & 0x7f;
		}

		if(($firstByte & 0xc0) === 0x80){
			// two bytes
			$secondByte = $bitBuffer->read(8);

			return (($firstByte & 0x3f) << 8) | $secondByte;
		}

		if(($firstByte & 0xe0) === 0xC0){
			// three bytes
			$secondThirdBytes = $bitBuffer->read(16);

			return (($firstByte & 0x1f) << 16) | $secondThirdBytes;
		}

		throw new QRCodeDataException('error decoding ECI value');
	}

	/**
	 * @codeCoverageIgnore Unused, but required as per interface
	 */
	public static function validateString(string $string):bool{
		return true;
	}

	/**
	 * @codeCoverageIgnore Unused, but required as per interface
	 */
	public static function decodeSegment(BitBuffer $bitBuffer, int $versionNumber):string{
		return '';
	}

}
