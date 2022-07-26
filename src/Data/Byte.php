<?php
/**
 * Class Byte
 *
 * @created      25.11.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\Common\{BitBuffer, Mode};

use function chr, ord;

/**
 * Byte mode, ISO-8859-1 or UTF-8
 *
 * ISO/IEC 18004:2000 Section 8.3.4
 * ISO/IEC 18004:2000 Section 8.4.4
 */
final class Byte extends QRDataModeAbstract{

	/**
	 * @inheritDoc
	 */
	protected static int $datamode = Mode::BYTE;

	/**
	 * @inheritDoc
	 */
	public function getLengthInBits():int{
		return $this->getCharCount() * 8;
	}

	/**
	 * @inheritDoc
	 */
	public static function validateString(string $string):bool{
		return $string !== '';
	}

	/**
	 * @inheritDoc
	 */
	public function write(BitBuffer $bitBuffer, int $versionNumber):void{
		$len = $this->getCharCount();

		$bitBuffer
			->put($this::$datamode, 4)
			->put($len, $this::getLengthBits($versionNumber))
		;

		$i = 0;

		while($i < $len){
			$bitBuffer->put(ord($this->data[$i]), 8);
			$i++;
		}

	}

	/**
	 * @inheritDoc
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public static function decodeSegment(BitBuffer $bitBuffer, int $versionNumber):string{
		$length = $bitBuffer->read(self::getLengthBits($versionNumber));

		if($bitBuffer->available() < 8 * $length){
			throw new QRCodeDataException('not enough bits available'); // @codeCoverageIgnore
		}

		$readBytes = '';

		for($i = 0; $i < $length; $i++){
			$readBytes .= chr($bitBuffer->read(8));
		}

		return $readBytes;
	}

}
