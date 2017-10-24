<?php
/**
 * Class Util
 *
 * @filesource   Util.php
 * @created      25.11.2015
 * @package      chillerlan\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode;

use chillerlan\QRCode\Data\QRDataInterface;

/**
 *
 */
class Util{

	const MAX_LENGTH = [
		[[ 41,  25,  17,  10], [ 34,  20,  14,   8], [ 27,  16,  11,   7], [ 17,  10,   7,   4]],
		[[ 77,  47,  32,  20], [ 63,  38,  26,  16], [ 48,  29,  20,  12], [ 34,  20,  14,   8]],
		[[127,  77,  53,  32], [101,  61,  42,  26], [ 77,  47,  32,  20], [ 58,  35,  24,  15]],
		[[187, 114,  78,  48], [149,  90,  62,  38], [111,  67,  46,  28], [ 82,  50,  34,  21]],
		[[255, 154, 106,  65], [202, 122,  84,  52], [144,  87,  60,  37], [106,  64,  44,  27]],
		[[322, 195, 134,  82], [255, 154, 106,  65], [178, 108,  74,  45], [139,  84,  58,  36]],
		[[370, 224, 154,  95], [293, 178, 122,  75], [207, 125,  86,  53], [154,  93,  64,  39]],
		[[461, 279, 192, 118], [365, 221, 152,  93], [259, 157, 108,  66], [202, 122,  84,  52]],
		[[552, 335, 230, 141], [432, 262, 180, 111], [312, 189, 130,  80], [235, 143,  98,  60]],
		[[652, 395, 271, 167], [513, 311, 213, 131], [364, 221, 151,  93], [288, 174, 119,  74]],
	];

	const BLOCK_TABLE = [
		// 1
		[1, 26, 19], // L
		[1, 26, 16], // M
		[1, 26, 13], // Q
		[1, 26,  9], // H
		// 2
		[1, 44, 34],
		[1, 44, 28],
		[1, 44, 22],
		[1, 44, 16],
		// 3
		[1, 70, 55],
		[1, 70, 44],
		[2, 35, 17],
		[2, 35, 13],
		// 4
		[1, 100, 80],
		[2,  50, 32],
		[2,  50, 24],
		[4,  25,  9],
		// 5
		[1, 134, 108],
		[2,  67,  43],
		[2,  33,  15, 2, 34, 16],
		[2,  33,  11, 2, 34, 12],
		// 6
		[2, 86, 68],
		[4, 43, 27],
		[4, 43, 19],
		[4, 43, 15],
		// 7
		[2, 98, 78],
		[4, 49, 31],
		[2, 32, 14, 4, 33, 15],
		[4, 39, 13, 1, 40, 14],
		// 8
		[2, 121, 97],
		[2,  60, 38, 2, 61, 39],
		[4,  40, 18, 2, 41, 19],
		[4,  40, 14, 2, 41, 15],
		// 9
		[2, 146, 116],
		[3,  58,  36, 2, 59, 37],
		[4,  36,  16, 4, 37, 17],
		[4,  36,  12, 4, 37, 13],
		// 10
		[2, 86, 68, 2, 87, 69],
		[4, 69, 43, 1, 70, 44],
		[6, 43, 19, 2, 44, 20],
		[6, 43, 15, 2, 44, 16],
	];

	/**
	 * @param string $string
	 *
	 * @return bool
	 */
	public function isNumber(string $string):bool {
		$len = strlen($string);

		for($i = 0; $i < $len; $i++){
			$chr = ord($string[$i]);

			if(!(ord('0') <= $chr && $chr <= ord('9'))){
				return false;
			}
		}

		return true;
	}

	/**
	 * @param string $string
	 *
	 * @return bool
	 */
	public function isAlphaNum(string $string):bool {
		$len = strlen($string);

		for($i = 0; $i < $len; $i++){
			$chr = ord($string[$i]);

			if(
				  !(ord('0') <= $chr && $chr <= ord('9'))
			   && !(ord('A') <= $chr && $chr <= ord('Z'))
			   && strpos(' $%*+-./:', $string[$i]) === false
			){
				return false;
			}
		}

		return true;
	}

	/**
	 * @param string $string
	 *
	 * @return bool
	 */
	public function isKanji(string $string):bool {

		if(empty($string)){
			return false;
		}

		$i = 0;
		$length = strlen($string);

		while($i + 1 < $length){
			$c = ((0xff&ord($string[$i])) << 8)|(0xff&ord($string[$i + 1]));

			if(!($c >= 0x8140 && $c <= 0x9FFC) && !($c >= 0xE040 && $c <= 0xEBBF)){
				return false;
			}

			$i += 2;
		}

		return !($i < $length);
	}

	/**
	 * @param int $data
	 *
	 * @return int
	 */
	public function getBCHTypeInfo(int $data):int {
		$G15_MASK = (1 << 14)|(1 << 12)|(1 << 10)|(1 << 4)|(1 << 1);
		$G15      = (1 << 10)|(1 << 8)|(1 << 5)|(1 << 4)|(1 << 2)|(1 << 1)|(1 << 0);

		return (($data << 10)|$this->getBCHT($data, 10, $G15))^$G15_MASK;
	}

	/**
	 * @param int $data
	 *
	 * @return int
	 */
	public function getBCHTypeNumber(int $data):int{
		$G18 = (1 << 12)|(1 << 11)|(1 << 10)|(1 << 9)|(1 << 8)|(1 << 5)|(1 << 2)|(1 << 0);

		return ($data << 12)|$this->getBCHT($data, 12, $G18);
	}

	/**
	 * @param int $data
	 * @param int $bits
	 * @param int $mask
	 *
	 * @return int
	 */
	protected function getBCHT(int $data, int $bits, int $mask):int {
		$digit = $data << $bits;

		while($this->getBCHDigit($digit) - $this->getBCHDigit($mask) >= 0){
			$digit ^= ($mask << ($this->getBCHDigit($digit) - $this->getBCHDigit($mask)));
		}

		return $digit;
	}

	/**
	 * @param int $data
	 *
	 * @return int
	 */
	public function getBCHDigit(int $data):int {
		$digit = 0;

		while($data !== 0){
			$digit++;
			$data >>= 1;
		}

		return $digit;
	}

	/**
	 * @param int $typeNumber
	 * @param int $errorCorrectLevel
	 *
	 * @return array
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function getRSBlocks(int $typeNumber, int $errorCorrectLevel):array {

		if(!array_key_exists($errorCorrectLevel, QRCode::RSBLOCK)){
			throw new QRCodeException('$typeNumber: '.$typeNumber.' / $errorCorrectLevel: '.$errorCorrectLevel);
		}

		$rsBlock = self::BLOCK_TABLE[($typeNumber - 1) * 4 + QRCode::RSBLOCK[$errorCorrectLevel]];
		$list = [];
		$length = count($rsBlock) / 3;

		for($i = 0; $i < $length; $i++){
			for($j = 0; $j < $rsBlock[$i * 3 + 0]; $j++){
				$list[] = [$rsBlock[$i * 3 + 1], $rsBlock[$i * 3 + 2]];
			}
		}

		return $list;
	}

	/**
	 * @param int $typeNumber
	 * @param int $mode
	 * @param int $errorCorrectLevel
	 *
	 * @return int
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function getMaxLength(int $typeNumber, int $mode, int $errorCorrectLevel):int {

		if(!array_key_exists($errorCorrectLevel, QRCode::RSBLOCK)){
			throw new QRCodeException('Invalid error correct level: '.$errorCorrectLevel);
		}

		if(!array_key_exists($mode, QRDataInterface::MODE)){
			throw new QRCodeException('Invalid mode: '.$mode);
		}

		return self::MAX_LENGTH[$typeNumber - 1][QRCode::RSBLOCK[$errorCorrectLevel]][QRDataInterface::MODE[$mode]];
	}

}
