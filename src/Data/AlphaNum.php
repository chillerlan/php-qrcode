<?php
/**
 * Class AlphaNum
 *
 * @filesource   AlphaNum.php
 * @created      25.11.2015
 * @package      chillerlan\QRCode\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\Helpers\BitBuffer;
use chillerlan\QRCode\QRCode;

use function ceil, ord, sprintf, str_split;

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
	 * @var int[]
	 */
	protected const CHAR_MAP_ALPHANUM = [
		'0' =>  0, '1' =>  1, '2' =>  2, '3' =>  3, '4' =>  4, '5' =>  5, '6' =>  6, '7' =>  7,
		'8' =>  8, '9' =>  9, 'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15,
		'G' => 16, 'H' => 17, 'I' => 18, 'J' => 19, 'K' => 20, 'L' => 21, 'M' => 22, 'N' => 23,
		'O' => 24, 'P' => 25, 'Q' => 26, 'R' => 27, 'S' => 28, 'T' => 29, 'U' => 30, 'V' => 31,
		'W' => 32, 'X' => 33, 'Y' => 34, 'Z' => 35, ' ' => 36, '$' => 37, '%' => 38, '*' => 39,
		'+' => 40, '-' => 41, '.' => 42, '/' => 43, ':' => 44,
	];

	protected array $lengthBits = [9, 11, 13];

	/**
	 * @inheritdoc
	 */
	public function getLengthInBits():int{
		return (int)ceil($this->getCharCount() * (11 / 2));
	}

	/**
	 * @inheritdoc
	 */
	public static function validateString(string $string):bool{

		foreach(str_split($string) as $chr){
			if(!isset(self::CHAR_MAP_ALPHANUM[$chr])){
				return false;
			}
		}

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function write(BitBuffer $bitBuffer, int $version):void{
		$len = $this->getCharCount();

		$bitBuffer
			->put(QRCode::DATA_ALPHANUM, 4)
			->put($len, $this->getLengthBitsForVersion($version))
		;

		// encode 2 characters in 11 bits
		for($i = 0; $i + 1 < $len; $i += 2){
			$bitBuffer->put($this->getCharCode($this->data[$i]) * 45 + $this->getCharCode($this->data[$i + 1]), 11);
		}

		// encode a remaining character in 6 bits
		if($i < $len){
			$bitBuffer->put($this->getCharCode($this->data[$i]), 6);
		}

	}

	/**
	 * get the code for the given character
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException on an illegal character occurence
	 */
	protected function getCharCode(string $chr):int{

		if(!isset(self::CHAR_MAP_ALPHANUM[$chr])){
			throw new QRCodeDataException(sprintf('illegal char: "%s" [%d]', $chr, ord($chr)));
		}

		return self::CHAR_MAP_ALPHANUM[$chr];
	}

}
