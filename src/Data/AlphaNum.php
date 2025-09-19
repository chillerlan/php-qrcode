<?php
/**
 * Class AlphaNum
 *
 * @created      25.11.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\Common\{BitBuffer, Mode};
use function ceil, intdiv, preg_match, strpos;

/**
 * Alphanumeric mode: 0 to 9, A to Z, space, $ % * + - . / :
 *
 * ISO/IEC 18004:2000 Section 8.3.3
 * ISO/IEC 18004:2000 Section 8.4.3
 */
final class AlphaNum extends QRDataModeAbstract{

	/**
	 * ISO/IEC 18004:2000 Table 5
	 *
	 * @var string
	 */
	private const CHAR_MAP = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:';

	/**
	 * @inheritDoc
	 */
	public const DATAMODE = Mode::ALPHANUM;

	/**
	 * @inheritDoc
	 */
	public function getLengthInBits():int{
		return (int)ceil($this->getCharCount() * (11 / 2));
	}

	/**
	 * @inheritDoc
	 */
	public static function validateString(string $string):bool{
		return (bool)preg_match('/^[A-Z\d %$*+\-.:\/]+$/', $string);
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

		// encode 2 characters in 11 bits
		for($i = 0; ($i + 1) < $len; $i += 2){
			$bitBuffer->put(
				($this->ord($this->data[$i]) * 45 + $this->ord($this->data[($i + 1)])),
				11,
			);
		}

		// encode a remaining character in 6 bits
		if($i < $len){
			$bitBuffer->put($this->ord($this->data[$i]), 6);
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public static function decodeSegment(BitBuffer $bitBuffer, int $versionNumber):string{
		$length = $bitBuffer->read(self::getLengthBits($versionNumber));
		$result = '';
		// Read two characters at a time
		while($length > 1){

			if($bitBuffer->available() < 11){
				throw new QRCodeDataException('not enough bits available'); // @codeCoverageIgnore
			}

			$nextTwoCharsBits  = $bitBuffer->read(11);
			$result           .= self::chr(intdiv($nextTwoCharsBits, 45));
			$result           .= self::chr($nextTwoCharsBits % 45);
			$length           -= 2;
		}

		if($length === 1){
			// special case: one character left
			if($bitBuffer->available() < 6){
				throw new QRCodeDataException('not enough bits available'); // @codeCoverageIgnore
			}

			$result .= self::chr($bitBuffer->read(6));
		}

		return $result;
	}

	/**
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	private function ord(string $chr):int{
		/** @phan-suppress-next-line PhanParamSuspiciousOrder */
		$ord = strpos(self::CHAR_MAP, $chr);

		if($ord === false){
			throw new QRCodeDataException('invalid character'); // @codeCoverageIgnore
		}

		return $ord;
	}

	/**
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	private static function chr(int $ord):string{

		if($ord < 0 || $ord > 44){
			throw new QRCodeDataException('invalid character code'); // @codeCoverageIgnore
		}

		return self::CHAR_MAP[$ord];
	}

}
