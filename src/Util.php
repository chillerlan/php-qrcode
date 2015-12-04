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
	public $MAX_LENGTH = [
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
	public $PATTERN_POSITION = [
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
	 * @var array
	 */
	protected $ERROR_CORRECT_LEVEL = [
		QRConst::ERROR_CORRECT_LEVEL_L => 0,
		QRConst::ERROR_CORRECT_LEVEL_M => 1,
		QRConst::ERROR_CORRECT_LEVEL_Q => 2,
		QRConst::ERROR_CORRECT_LEVEL_H => 3,
	];

	/**
	 * @var array
	 */
	protected $MODE = [
		QRConst::MODE_NUMBER => 0,
		QRConst::MODE_ALPHANUM => 1,
		QRConst::MODE_BYTE => 2,
		QRConst::MODE_KANJI => 3,
	];

	/**
	 * @param int $typeNumber
	 * @param int $mode
	 * @param int $ecLevel
	 *
	 * @return mixed
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function getMaxLength($typeNumber, $mode, $ecLevel){

		if(!isset($this->ERROR_CORRECT_LEVEL[$ecLevel])){
			throw new QRCodeException('$_err: '.$ecLevel);
		}

		if(!isset($this->MODE[$mode])){
			throw new QRCodeException('$_mode: '.$mode);
		}

		return $this->MAX_LENGTH[$typeNumber - 1][$this->ERROR_CORRECT_LEVEL[$ecLevel]][$this->MODE[$mode]];
	}

	/**
	 * @param $s
	 *
	 * @return int
	 */
	public function getMode($s){

		switch(true){
			case $this->isAlphaNum($s): return $this->isNumber($s) ? QRConst::MODE_NUMBER : QRConst::MODE_ALPHANUM;
			case $this->isKanji($s)   : return QRConst::MODE_KANJI;
			default:
				return QRConst::MODE_BYTE;
		}

	}

	/**
	 * @param $s
	 *
	 * @return bool
	 */
	protected function isNumber($s){

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
	 * @param $s
	 *
	 * @return bool
	 */
	protected function isAlphaNum($s){

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
	 * @param $s
	 *
	 * @return bool
	 */
	protected function isKanji($s){

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

		while($data !== 0){
			$digit++;
			$data >>= 1;
		}

		return $digit;
	}


	/**
	 * used for converting fg/bg colors (e.g. #0000ff = 0x0000FF) - added 2015.07.27 ~ DoktorJ
	 *
	 * @param int $hex
	 *
	 * @return array
	 */
	public function hex2rgb($hex = 0x0){
		return [
			'r' => floor($hex / 65536),
			'g' => floor($hex / 256) % 256,
			'b' => $hex % 256,
		];
	}

}
