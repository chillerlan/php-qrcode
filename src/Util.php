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
	 * @param string $s
	 *
	 * @return bool
	 */
	public static function isNumber($s){
		$len = strlen($s);
		$i = 0;

		while($i < $len){
			$c = ord($s[$i]);

			if(!(ord('0') <= $c && $c <= ord('9'))){
				return false;
			}
			
			$i++;
		}

		return true;
	}

	/**
	 * @param string $s
	 *
	 * @return bool
	 */
	public static function isAlphaNum($s){
		$len = strlen($s);
		$i = 0;

		while($i < $len){
			$c = ord($s[$i]);

			if(!(ord('0') <= $c && $c <= ord('9')) && !(ord('A') <= $c && $c <= ord('Z')) && strpos(' $%*+-./:', $s[$i]) === false){
				return false;
			}

			$i++;
		}

		return true;
	}

	/**
	 * @param string $s
	 *
	 * @return bool
	 */
	public static function isKanji($s){

		if(empty($s)){
			return false;
		}

		$i = 0;
		$len = strlen($s);
		while($i + 1 < $len){
			$c = ((0xff&ord($s[$i])) << 8)|(0xff&ord($s[$i + 1]));

			if(!($c >= 0x8140 && $c <= 0x9FFC) && !($c >= 0xE040 && $c <= 0xEBBF)){
				return false;
			}

			$i += 2;
		}

		return !($i < $len);
	}

	/**
	 * @param int $data
	 *
	 * @return int
	 */
	public static function getBCHTypeInfo($data){
		return (($data << 10)|self::getBCHT($data, 10, QRConst::G15))^QRConst::G15_MASK;
	}

	/**
	 * @param int $data
	 *
	 * @return int
	 */
	public static function getBCHTypeNumber($data){
		return ($data << 12)|self::getBCHT($data, 12, QRConst::G18);
	}

	/**
	 * @param int $data
	 * @param int $bits
	 * @param int $mask
	 *
	 * @return int
	 */
	protected static function getBCHT($data, $bits, $mask){
		$d = $data << $bits;

		while(self::getBCHDigit($d) - self::getBCHDigit($mask) >= 0){
			$d ^= ($mask << (self::getBCHDigit($d) - self::getBCHDigit($mask)));
		}

		return $d;
	}

	/**
	 * @param int $data
	 *
	 * @return int
	 */
	public static function getBCHDigit($data){
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
	public static function getRSBlocks($typeNumber, $errorCorrectLevel){

		if(!array_key_exists($errorCorrectLevel, QRConst::RSBLOCK)){
			throw new QRCodeException('$typeNumber: '.$typeNumber.' / $errorCorrectLevel: '.$errorCorrectLevel);
		}

		$rsBlock = QRConst::BLOCK_TABLE[($typeNumber - 1) * 4 + QRConst::RSBLOCK[$errorCorrectLevel]];
		$list = [];
		$length = count($rsBlock) / 3;
		$i = $j = 0;

		while($i < $length){
			while($j < $rsBlock[$i * 3 + 0]){
				$list[] = [$rsBlock[$i * 3 + 1], $rsBlock[$i * 3 + 2]];
				$j++;
			}
			$i++;
		}

		return $list;
	}

	/**
	 * @param int $typeNumber
	 * @param int $mode
	 * @param int $ecLevel
	 *
	 * @return int
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public static function getMaxLength($typeNumber, $mode, $ecLevel){

		if(!array_key_exists($ecLevel, QRConst::RSBLOCK)){
			throw new QRCodeException('Invalid error correct level: '.$ecLevel);
		}

		if(!array_key_exists($mode, QRConst::MODE)){
			throw new QRCodeException('Invalid mode: '.$mode);
		}

		return QRConst::MAX_LENGTH[$typeNumber - 1][QRConst::RSBLOCK[$ecLevel]][QRConst::MODE[$mode]];
	}


}
