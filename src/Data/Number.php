<?php
/**
 * Class Number
 *
 * @created      26.11.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\Common\{BitBuffer, Mode};
use function ceil, intdiv, substr, unpack;

/**
 * Numeric mode: decimal digits 0 to 9
 *
 * ISO/IEC 18004:2000 Section 8.3.2
 * ISO/IEC 18004:2000 Section 8.4.2
 */
final class Number extends QRDataModeAbstract{

	/**
	 * @inheritDoc
	 */
	public const DATAMODE = Mode::NUMBER;

	/**
	 * @inheritDoc
	 */
	public function getLengthInBits():int{
		return (int)ceil($this->getCharCount() * (10 / 3));
	}

	/**
	 * @inheritDoc
	 */
	public static function validateString(string $string):bool{
		return (bool)preg_match('/^\d+$/', $string);
	}

	/**
	 * @inheritDoc
	 */
	public function write(BitBuffer $bitBuffer, int $versionNumber):QRDataModeInterface{
		$len = $this->getCharCount();

		$bitBuffer
			->put(self::DATAMODE, 4)
			->put($len, $this::getLengthBits($versionNumber))
		;

		$i = 0;

		// encode numeric triplets in 10 bits
		while(($i + 2) < $len){
			$bitBuffer->put($this->parseInt(substr($this->data, $i, 3)), 10);
			$i += 3;
		}

		if($i < $len){

			// encode 2 remaining numbers in 7 bits
			if(($len - $i) === 2){
				$bitBuffer->put($this->parseInt(substr($this->data, $i, 2)), 7);
			}
			// encode one remaining number in 4 bits
			elseif(($len - $i) === 1){
				$bitBuffer->put($this->parseInt(substr($this->data, $i, 1)), 4);
			}

		}

		return $this;
	}

	/**
	 * get the code for the given numeric string
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	private function parseInt(string $string):int{
		$num = 0;

		$ords = unpack('C*', $string);

		if($ords === false){
			throw new QRCodeDataException('unpack() error');
		}

		foreach($ords as $ord){
			$num = ($num * 10 + $ord - 48);
		}

		return $num;
	}

	/**
	 * @inheritDoc
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public static function decodeSegment(BitBuffer $bitBuffer, int $versionNumber):string{
		$length = $bitBuffer->read(self::getLengthBits($versionNumber));
		$result = '';
		// Read three digits at a time
		while($length >= 3){
			// Each 10 bits encodes three digits
			if($bitBuffer->available() < 10){
				throw new QRCodeDataException('not enough bits available'); // @codeCoverageIgnore
			}

			$threeDigitsBits = $bitBuffer->read(10);

			if($threeDigitsBits >= 1000){
				throw new QRCodeDataException('error decoding numeric value');
			}

			$result .= intdiv($threeDigitsBits, 100);
			$result .= (intdiv($threeDigitsBits, 10) % 10);
			$result .= ($threeDigitsBits % 10);

			$length -= 3;
		}

		if($length === 2){
			// Two digits left over to read, encoded in 7 bits
			if($bitBuffer->available() < 7){
				throw new QRCodeDataException('not enough bits available'); // @codeCoverageIgnore
			}

			$twoDigitsBits = $bitBuffer->read(7);

			if($twoDigitsBits >= 100){
				throw new QRCodeDataException('error decoding numeric value');
			}

			$result .= intdiv($twoDigitsBits, 10);
			$result .= ($twoDigitsBits % 10);
		}
		elseif($length === 1){
			// One digit left over to read
			if($bitBuffer->available() < 4){
				throw new QRCodeDataException('not enough bits available'); // @codeCoverageIgnore
			}

			$digitBits = $bitBuffer->read(4);

			if($digitBits >= 10){
				throw new QRCodeDataException('error decoding numeric value');
			}

			$result .= $digitBits;
		}

		return $result;
	}

}
