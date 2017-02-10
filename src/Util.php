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

/**
 *
 */
class Util{

	/**
	 * @param string $string
	 *
	 * @return bool
	 */
	public static function isNumber(string $string):bool {
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
	public static function isAlphaNum(string $string):bool {
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
	public static function isKanji(string $string):bool {

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
	public static function getBCHTypeInfo(int $data):int {
		return (($data << 10)|self::getBCHT($data, 10, QRConst::G15))^QRConst::G15_MASK;
	}

	/**
	 * @param int $data
	 *
	 * @return int
	 */
	public static function getBCHTypeNumber(int $data):int{
		return ($data << 12)|self::getBCHT($data, 12, QRConst::G18);
	}

	/**
	 * @param int $data
	 * @param int $bits
	 * @param int $mask
	 *
	 * @return int
	 */
	protected static function getBCHT(int $data, int $bits, int $mask):int {
		$digit = $data << $bits;

		while(self::getBCHDigit($digit) - self::getBCHDigit($mask) >= 0){
			$digit ^= ($mask << (self::getBCHDigit($digit) - self::getBCHDigit($mask)));
		}

		return $digit;
	}

	/**
	 * @param int $data
	 *
	 * @return int
	 */
	public static function getBCHDigit(int $data):int {
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
	public static function getRSBlocks(int $typeNumber, int $errorCorrectLevel):array {

		if(!array_key_exists($errorCorrectLevel, QRConst::RSBLOCK)){
			throw new QRCodeException('$typeNumber: '.$typeNumber.' / $errorCorrectLevel: '.$errorCorrectLevel);
		}

		$rsBlock = QRConst::BLOCK_TABLE[($typeNumber - 1) * 4 + QRConst::RSBLOCK[$errorCorrectLevel]];
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
	public static function getMaxLength(int $typeNumber, int $mode, int $errorCorrectLevel):int {

		if(!array_key_exists($errorCorrectLevel, QRConst::RSBLOCK)){
			throw new QRCodeException('Invalid error correct level: '.$errorCorrectLevel);
		}

		if(!array_key_exists($mode, QRConst::MODE)){
			throw new QRCodeException('Invalid mode: '.$mode);
		}

		return QRConst::MAX_LENGTH[$typeNumber - 1][QRConst::RSBLOCK[$errorCorrectLevel]][QRConst::MODE[$mode]];
	}

}
