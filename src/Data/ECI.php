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

	protected int $datamode = Mode::DATA_ECI;

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
	 * Unused, but required as per interface
	 *
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public static function validateString(string $string):bool{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function write(BitBuffer $bitBuffer, int $versionNumber):void{
		$bitBuffer
			->put($this->datamode, 4)
			->put($this->encoding, 8)
		;
	}

}
