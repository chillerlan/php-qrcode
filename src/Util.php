<?php
/**
 *
 * @filesource   Util.php
 * @created      25.11.2015
 * @package      codemasher\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace codemasher\QRCode;

use codemasher\QRCode\Math;
use codemasher\QRCode\Polynomial;
use codemasher\QRCode\QRCode;
use codemasher\QRCode\QRConst;
use codemasher\QRCode\QRCodeException;

/**
 * Class Util
 */
class Util{

	const QR_G15 = (1 << 10)|(1 << 8)|(1 << 5)|(1 << 4)|(1 << 2)|(1 << 1)|(1 << 0);
	const QR_G18 = (1 << 12)|(1 << 11)|(1 << 10)|(1 << 9)|(1 << 8)|(1 << 5)|(1 << 2)|(1 << 0);
	const QR_G15_MASK = (1 << 14)|(1 << 12)|(1 << 10)|(1 << 4)|(1 << 1);

	/**
	 * @var array
	 */
	protected $QR_MAX_LENGTH = [
		[[41, 25, 17, 10], [34, 20, 14, 8], [27, 16, 11, 7], [17, 10, 7, 4]],
		[[77, 47, 32, 20], [63, 38, 26, 16], [48, 29, 20, 12], [34, 20, 14, 8]],
		[[127, 77, 53, 32], [101, 61, 42, 26], [77, 47, 32, 20], [58, 35, 24, 15]],
		[[187, 114, 78, 48], [149, 90, 62, 38], [111, 67, 46, 28], [82, 50, 34, 21]],
		[[255, 154, 106, 65], [202, 122, 84, 52], [144, 87, 60, 37], [106, 64, 44, 27]],
		[[322, 195, 134, 82], [255, 154, 106, 65], [178, 108, 74, 45], [139, 84, 58, 36]],
		[[370, 224, 154, 95], [293, 178, 122, 75], [207, 125, 86, 53], [154, 93, 64, 39]],
		[[461, 279, 192, 118], [365, 221, 152, 93], [259, 157, 108, 66], [202, 122, 84, 52]],
		[[552, 335, 230, 141], [432, 262, 180, 111], [312, 189, 130, 80], [235, 143, 98, 60]],
		[[652, 395, 271, 167], [513, 311, 213, 131], [364, 221, 151, 93], [288, 174, 119, 74]],
	];

	/**
	 * @var array
	 */
	protected $QR_PATTERN_POSITION_TABLE = [
		[],
		[6, 18],
		[6, 22],
		[6, 26],
		[6, 30],
		[6, 34],
		[6, 22, 38],
		[6, 24, 42],
		[6, 26, 46],
		[6, 28, 50],
		[6, 30, 54],
		[6, 32, 58],
		[6, 34, 62],
		[6, 26, 46, 66],
		[6, 26, 48, 70],
		[6, 26, 50, 74],
		[6, 30, 54, 78],
		[6, 30, 56, 82],
		[6, 30, 58, 86],
		[6, 34, 62, 90],
		[6, 28, 50, 72, 94],
		[6, 26, 50, 74, 98],
		[6, 30, 54, 78, 102],
		[6, 28, 54, 80, 106],
		[6, 32, 58, 84, 110],
		[6, 30, 58, 86, 114],
		[6, 34, 62, 90, 118],
		[6, 26, 50, 74, 98, 122],
		[6, 30, 54, 78, 102, 126],
		[6, 26, 52, 78, 104, 130],
		[6, 30, 56, 82, 108, 134],
		[6, 34, 60, 86, 112, 138],
		[6, 30, 58, 86, 114, 142],
		[6, 34, 62, 90, 118, 146],
		[6, 30, 54, 78, 102, 126, 150],
		[6, 24, 50, 76, 102, 128, 154],
		[6, 28, 54, 80, 106, 132, 158],
		[6, 32, 58, 84, 110, 136, 162],
		[6, 26, 54, 82, 110, 138, 166],
		[6, 30, 58, 86, 114, 142, 170],
	];

	/**
	 * @var \codemasher\QRCode\Math
	 */
	protected $math;

	/**
	 * Util constructor.
	 */
	public function __construct(){
		$this->math = new Math;
	}

	/**
	 * @param int $typeNumber
	 *
	 * @return int
	 */
	public function getPatternPosition($typeNumber){
		return $this->QR_PATTERN_POSITION_TABLE[$typeNumber - 1];
	}

	/**
	 * @param int $typeNumber
	 * @param int $mode
	 * @param int $errorCorrectLevel
	 *
	 * @return mixed
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function getMaxLength($typeNumber, $mode, $errorCorrectLevel){
		$_type = $typeNumber - 1;

		switch($errorCorrectLevel){
			case QRConst::ERROR_CORRECT_LEVEL_L:
				$_err = 0;
				break;
			case QRConst::ERROR_CORRECT_LEVEL_M:
				$_err = 1;
				break;
			case QRConst::ERROR_CORRECT_LEVEL_Q:
				$_err = 2;
				break;
			case QRConst::ERROR_CORRECT_LEVEL_H:
				$_err = 3;
				break;
			default:
				throw new QRCodeException('$_err: '.$errorCorrectLevel);
		}

		switch($mode){
			case QRConst::MODE_NUMBER:
				$_mode = 0;
				break;
			case QRConst::MODE_ALPHA_NUM:
				$_mode = 1;
				break;
			case QRConst::MODE_8BIT_BYTE:
				$_mode = 2;
				break;
			case QRConst::MODE_KANJI:
				$_mode = 3;
				break;
			default :
				throw new QRCodeException('$_mode: '.$mode);
		}

		return $this->QR_MAX_LENGTH[$_type][$_err][$_mode];
	}

	/**
	 * @param $errorCorrectLength
	 *
	 * @return \codemasher\QRCode\Polynomial
	 */
	public function getErrorCorrectPolynomial($errorCorrectLength){
		$a = new Polynomial([1]);

		for($i = 0; $i < $errorCorrectLength; $i++){
			$a = $a->multiply(new Polynomial([1, $this->math->gexp($i)]));
		}

		return $a;
	}

	/**
	 * @param $maskPattern
	 * @param $i
	 * @param $j
	 *
	 * @return bool
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function getMask($maskPattern, $i, $j){

		switch($maskPattern){
			case QRConst::MASK_PATTERN000:
				return ($i + $j) % 2 === 0;
			case QRConst::MASK_PATTERN001:
				return $i % 2 === 0;
			case QRConst::MASK_PATTERN010:
				return $j % 3 === 0;
			case QRConst::MASK_PATTERN011:
				return ($i + $j) % 3 === 0;
			case QRConst::MASK_PATTERN100:
				return (floor($i / 2) + floor($j / 3)) % 2 === 0;
			case QRConst::MASK_PATTERN101:
				return ($i * $j) % 2 + ($i * $j) % 3 === 0;
			case QRConst::MASK_PATTERN110:
				return (($i * $j) % 2 + ($i * $j) % 3) % 2 === 0;
			case QRConst::MASK_PATTERN111:
				return (($i * $j) % 3 + ($i + $j) % 2) % 2 === 0;
			default :
				throw new QRCodeException('mask: '.$maskPattern);
		}
	}

	/**
	 * @param \codemasher\QRCode\QRCode $qrCode
	 *
	 * @return float|int
	 */
	public function getLostPoint(QRCode $qrCode){
		$moduleCount = $qrCode->getModuleCount();

		$lostPoint = 0;

		// LEVEL1

		for($row = 0; $row < $moduleCount; $row++){
			for($col = 0; $col < $moduleCount; $col++){
				$sameCount = 0;
				$dark = $qrCode->isDark($row, $col);

				for($r = -1; $r <= 1; $r++){
					if($row + $r < 0 || $moduleCount <= $row + $r){
						continue;
					}

					for($c = -1; $c <= 1; $c++){

						if($col + $c < 0 || $moduleCount <= $col + $c){
							continue;
						}

						if($r == 0 && $c == 0){
							continue;
						}

						if($dark == $qrCode->isDark($row + $r, $col + $c)){
							$sameCount++;
						}
					}
				}

				if($sameCount > 5){
					$lostPoint += (3 + $sameCount - 5);
				}
			}
		}

		// LEVEL2

		for($row = 0; $row < $moduleCount - 1; $row++){
			for($col = 0; $col < $moduleCount - 1; $col++){
				$count = 0;

				if($qrCode->isDark($row, $col)){
					$count++;
				}

				if($qrCode->isDark($row + 1, $col)){
					$count++;
				}

				if($qrCode->isDark($row, $col + 1)){
					$count++;
				}

				if($qrCode->isDark($row + 1, $col + 1)){
					$count++;
				}

				if($count === 0 || $count === 4){
					$lostPoint += 3;
				}
			}
		}

		// LEVEL3

		for($row = 0; $row < $moduleCount; $row++){
			for($col = 0; $col < $moduleCount - 6; $col++){
				if($qrCode->isDark($row, $col)
					&& !$qrCode->isDark($row, $col + 1)
					&& $qrCode->isDark($row, $col + 2)
					&& $qrCode->isDark($row, $col + 3)
					&& $qrCode->isDark($row, $col + 4)
					&& !$qrCode->isDark($row, $col + 5)
					&& $qrCode->isDark($row, $col + 6)
				){
					$lostPoint += 40;
				}
			}
		}

		for($col = 0; $col < $moduleCount; $col++){
			for($row = 0; $row < $moduleCount - 6; $row++){
				if($qrCode->isDark($row, $col)
					&& !$qrCode->isDark($row + 1, $col)
					&& $qrCode->isDark($row + 2, $col)
					&& $qrCode->isDark($row + 3, $col)
					&& $qrCode->isDark($row + 4, $col)
					&& !$qrCode->isDark($row + 5, $col)
					&& $qrCode->isDark($row + 6, $col)
				){
					$lostPoint += 40;
				}
			}
		}

		// LEVEL4

		$darkCount = 0;
		for($col = 0; $col < $moduleCount; $col++){
			for($row = 0; $row < $moduleCount; $row++){
				if($qrCode->isDark($row, $col)){
					$darkCount++;
				}
			}
		}

		$ratio = abs(100 * $darkCount / $moduleCount / $moduleCount - 50) / 5;
		$lostPoint += $ratio * 10;

		return $lostPoint;
	}

	/**
	 * @param $s
	 *
	 * @return int
	 */
	public function getMode($s){
		if($this->isAlphaNum($s)){
			if($this->isNumber($s)){
				return QRConst::MODE_NUMBER;
			}
			return QRConst::MODE_ALPHA_NUM;
		}
		else if($this->isKanji($s)){
			return QRConst::MODE_KANJI;
		}
		else{
			return QRConst::MODE_8BIT_BYTE;
		}
	}

	/**
	 * @param $s
	 *
	 * @return bool
	 */
	public function isNumber($s){
		for($i = 0; $i < strlen($s); $i++){
			$c = ord($s[$i]);

			if(!($this->toCharCode('0') <= $c && $c <= $this->toCharCode('9'))){
				return false;
			}
		}

		return true;
	}

	/**
	 * @param $s
	 *
	 * @return bool
	 */
	public function isAlphaNum($s){
		for($i = 0; $i < strlen($s); $i++){
			$c = ord($s[$i]);

			if(!($this->toCharCode('0') <= $c && $c <= $this->toCharCode('9'))
				&& !($this->toCharCode('A') <= $c && $c <= $this->toCharCode('Z'))
				&& strpos(' $%*+-./:', $s[$i]) === false
			){
				return false;
			}
		}

		return true;
	}

	/**
	 * @param $s
	 *
	 * @return bool
	 */
	public function isKanji($s){
		$data = $s;
		$i = 0;

		while($i + 1 < strlen($data)){
			$c = ((0xff&ord($data[$i])) << 8)|(0xff&ord($data[$i + 1]));

			if(!(0x8140 <= $c && $c <= 0x9FFC) && !(0xE040 <= $c && $c <= 0xEBBF)){
				return false;
			}

			$i += 2;
		}

		if($i < strlen($data)){
			return false;
		}

		return true;
	}

	/**
	 * @param $s
	 *
	 * @return int
	 */
	public function toCharCode($s){
		return ord($s[0]);
	}

	/**
	 * @param $data
	 *
	 * @return int
	 */
	public function getBCHTypeInfo($data){
		$d = $data << 10;

		while($this->getBCHDigit($d) - $this->getBCHDigit(self::QR_G15) >= 0){
			$d ^= (self::QR_G15 << ($this->getBCHDigit($d) - $this->getBCHDigit(self::QR_G15)));
		}

		return (($data << 10)|$d)^self::QR_G15_MASK;
	}

	/**
	 * @param $data
	 *
	 * @return int
	 */
	public function getBCHTypeNumber($data){
		$d = $data << 12;

		while($this->getBCHDigit($d) - $this->getBCHDigit(self::QR_G18) >= 0){
			$d ^= (self::QR_G18 << ($this->getBCHDigit($d) - $this->getBCHDigit(self::QR_G18)));
		}

		return ($data << 12)|$d;
	}

	/**
	 * @param $data
	 *
	 * @return int
	 */
	public function getBCHDigit($data){
		$digit = 0;

		while($data != 0){
			$digit++;
			$data >>= 1;
		}

		return $digit;
	}

}
