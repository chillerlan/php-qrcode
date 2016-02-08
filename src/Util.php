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
		for($i = 0; $i < $len; $i++){
			$c = ord($s[$i]);

			if(!(ord('0') <= $c && $c <= ord('9'))){
				return false;
			}
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
		for($i = 0; $i < $len; $i++){
			$c = ord($s[$i]);

			if(!(ord('0') <= $c && $c <= ord('9')) && !(ord('A') <= $c && $c <= ord('Z')) && strpos(' $%*+-./:', $s[$i]) === false){
				return false;
			}
		}

		return true;
	}

	/**
	 * @param string $s
	 *
	 * @return bool
	 */
	public static function isKanji($s){

		$i = 0;
		$len = strlen($s);
		while($i + 1 < $len){
			$c = ((0xff&ord($s[$i])) << 8)|(0xff&ord($s[$i + 1]));

			if(!(0x8140 <= $c && $c <= 0x9FFC) && !(0xE040 <= $c && $c <= 0xEBBF)){
				return false;
			}

			$i += 2;
		}

		if($i < $len){
			return false;
		}

		return true;
	}

}
