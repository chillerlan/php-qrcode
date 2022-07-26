<?php
/**
 * Class Kanji
 *
 * @created      25.11.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\Common\{BitBuffer, Mode};

use function chr, implode, mb_convert_encoding, mb_detect_encoding, mb_internal_encoding, mb_strlen, ord, sprintf, strlen;

/**
 * Kanji mode: double-byte characters from the Shift JIS character set
 *
 * ISO/IEC 18004:2000 Section 8.3.5
 * ISO/IEC 18004:2000 Section 8.4.5
 */
final class Kanji extends QRDataModeAbstract{

	/**
	 * @inheritDoc
	 */
	protected static int $datamode = Mode::KANJI;

	/**
	 *
	 */
	public function __construct(string $data){
		parent::__construct($data);

		$this->data = mb_convert_encoding($this->data, 'SJIS', mb_detect_encoding($this->data));
	}

	/**
	 * @inheritDoc
	 */
	protected function getCharCount():int{
		return mb_strlen($this->data, 'SJIS');
	}

	/**
	 * @inheritDoc
	 */
	public function getLengthInBits():int{
		return $this->getCharCount() * 13;
	}

	/**
	 * checks if a string qualifies as Kanji
	 */
	public static function validateString(string $string):bool{
		$i   = 0;
		$len = strlen($string);

		if($len < 2){
			return false;
		}

		while($i + 1 < $len){
			$c = ((0xff & ord($string[$i])) << 8) | (0xff & ord($string[$i + 1]));

			if(!($c >= 0x8140 && $c <= 0x9ffc) && !($c >= 0xe040 && $c <= 0xebbf)){
				return false;
			}

			$i += 2;
		}

		return $i >= $len;
	}

	/**
	 * @inheritDoc
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException on an illegal character occurence
	 */
	public function write(BitBuffer $bitBuffer, int $versionNumber):void{

		$bitBuffer
			->put($this::$datamode, 4)
			->put($this->getCharCount(), $this::getLengthBits($versionNumber))
		;

		$len = strlen($this->data);

		for($i = 0; $i + 1 < $len; $i += 2){
			$c = ((0xff & ord($this->data[$i])) << 8) | (0xff & ord($this->data[$i + 1]));

			if($c >= 0x8140 && $c <= 0x9ffC){
				$c -= 0x8140;
			}
			elseif($c >= 0xe040 && $c <= 0xebbf){
				$c -= 0xc140;
			}
			else{
				throw new QRCodeDataException(sprintf('illegal char at %d [%d]', $i + 1, $c));
			}

			$bitBuffer->put(((($c >> 8) & 0xff) * 0xc0) + ($c & 0xff), 13);
		}

		if($i < $len){
			throw new QRCodeDataException(sprintf('illegal char at %d', $i + 1));
		}

	}

	/**
	 * @inheritDoc
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public static function decodeSegment(BitBuffer $bitBuffer, int $versionNumber):string{
		$length = $bitBuffer->read(self::getLengthBits($versionNumber));

		if($bitBuffer->available() < $length * 13){
			throw new QRCodeDataException('not enough bits available');  // @codeCoverageIgnore
		}

		$buffer = [];
		$offset = 0;

		while($length > 0){
			// Each 13 bits encodes a 2-byte character
			$twoBytes          = $bitBuffer->read(13);
			$assembledTwoBytes = ((int)($twoBytes / 0x0c0) << 8) | ($twoBytes % 0x0c0);

			$assembledTwoBytes += ($assembledTwoBytes < 0x01f00)
				? 0x08140  // In the 0x8140 to 0x9FFC range
				: 0x0c140; // In the 0xE040 to 0xEBBF range

			$buffer[$offset]     = chr(0xff & ($assembledTwoBytes >> 8));
			$buffer[$offset + 1] = chr(0xff & $assembledTwoBytes);
			$offset              += 2;
			$length--;
		}

		return mb_convert_encoding(implode($buffer), mb_internal_encoding(), 'SJIS');
	}

}
