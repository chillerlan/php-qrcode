<?php
/**
 * Class QRDataGenerator
 *
 * @filesource   QRDataGenerator.php
 * @created      24.10.2017
 * @package      chillerlan\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode;

use chillerlan\QRCode\Data\{
	AlphaNum, Byte, Kanji, Number, QRDataInterface
};

/**
 * const ALL THE THINGS! ~John Carmack
 */
class QRDataGenerator{

	const RSBLOCK = [
		QRCode::ERROR_CORRECT_LEVEL_L => 0,
		QRCode::ERROR_CORRECT_LEVEL_M => 1,
		QRCode::ERROR_CORRECT_LEVEL_Q => 2,
		QRCode::ERROR_CORRECT_LEVEL_H => 3,
	];

	const DATA_INTERFACES = [
		QRDataInterface::MODE_ALPHANUM => AlphaNum::class,
		QRDataInterface::MODE_BYTE     => Byte::class,
		QRDataInterface::MODE_KANJI    => Kanji::class,
		QRDataInterface::MODE_NUMBER   => Number::class,
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

	const PATTERN_POSITION = [
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
		[6, 28, 50, 72,  94],
		[6, 26, 50, 74,  98],
		[6, 30, 54, 78, 102],
		[6, 28, 54, 80, 106],
		[6, 32, 58, 84, 110],
		[6, 30, 58, 86, 114],
		[6, 34, 62, 90, 118],
		[6, 26, 50, 74,  98, 122],
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

	const MAX_BITS = [
		QRCode::TYPE_01 => [128,  152,   72,  104],
		QRCode::TYPE_02 => [224,  272,  128,  176],
		QRCode::TYPE_03 => [352,  440,  208,  272],
		QRCode::TYPE_04 => [512,  640,  288,  384],
		QRCode::TYPE_05 => [688,  864,  368,  496],
		QRCode::TYPE_06 => [864,  1088, 480,  608],
		QRCode::TYPE_07 => [992,  1248, 528,  704],
		QRCode::TYPE_08 => [1232, 1552, 688,  880],
		QRCode::TYPE_09 => [1456, 1856, 800, 1056],
		QRCode::TYPE_10 => [1728, 2192, 976, 1232],
	];

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

	/**
	 * @var int
	 */
	protected $typeNumber;

	/**
	 * @var int
	 */
	protected $errorCorrectLevel;

	/**
	 * @var int
	 */
	protected $lostPoint;

	/**
	 * @var int
	 */
	protected $darkCount;

	/**
	 * @var float
	 */
	protected $minLostPoint;

	/**
	 * @var int
	 */
	protected $maskPattern;

	/**
	 * @var array
	 */
	protected $matrix = [];

	/**
	 * @var int
	 */
	protected $pixelCount = 0;

	/**
	 * @var \chillerlan\QRCode\Data\QRDataInterface
	 */
	protected $qrDataInterface;

	/**
	 * @var \chillerlan\QRCode\BitBuffer
	 */
	protected $bitBuffer;

	/**
	 * QRDataGenerator constructor.
	 *
	 * @param string $data
	 * @param int    $typeNumber
	 * @param int    $errorCorrectLevel
	 *
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function __construct(string $data, int $typeNumber, int $errorCorrectLevel){
		$this->typeNumber        = $typeNumber;
		$this->errorCorrectLevel = $errorCorrectLevel;
		$this->bitBuffer         = new BitBuffer;

		switch(true){
			case $this->isAlphaNum($data):
				$mode = $this->isNumber($data)
					? QRDataInterface::MODE_NUMBER
					: QRDataInterface::MODE_ALPHANUM;
				break;
			case $this->isKanji($data):
				$mode = QRDataInterface::MODE_KANJI;
				break;
			default:
				$mode = QRDataInterface::MODE_BYTE;
				break;
		}

		$qrDataInterface       = self::DATA_INTERFACES[$mode];
		$this->qrDataInterface = new $qrDataInterface($data);

		if($this->typeNumber < 1 || $this->typeNumber > 10){
			$this->typeNumber = $this->getTypeNumber($this->qrDataInterface, $mode);
		}

	}

	/**
	 * @return array
	 */
	public function getRawData():array{
		$this->minLostPoint = 0;
		$this->maskPattern  = 0;

		for($pattern = 0; $pattern <= 7; $pattern++){
			$this->testPattern($pattern);
		}

		$this->getMatrix(false, $this->maskPattern);

		return $this->matrix;
	}

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

		$i      = 0;
		$len = strlen($string);

		while($i + 1 < $len){
			$c = ((0xff&ord($string[$i])) << 8)|(0xff&ord($string[$i + 1]));

			if(!($c >= 0x8140 && $c <= 0x9FFC) && !($c >= 0xE040 && $c <= 0xEBBF)){
				return false;
			}

			$i += 2;
		}

		return !($i < $len);
	}

	/**
	 * @param \chillerlan\QRCode\Data\QRDataInterface $qrDataInterface
	 * @param int                                     $mode
	 *
	 * @return int
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function getTypeNumber(QRDataInterface $qrDataInterface, int $mode):int {
		$length = $qrDataInterface->dataLength;

		if($qrDataInterface->mode === QRDataInterface::MODE_KANJI){
			$length = floor($length / 2);
		}

		$maxTypenumber = QRCode::TYPE_10;

		$range = range(1, $maxTypenumber);
		foreach($range as $type){

			if($length <= $this->getMaxLength($type, $mode, $this->errorCorrectLevel)){
				$maxTypenumber = $type;
				break;
			}

		}

		return $maxTypenumber;
	}

	/**
	 * @param int $typeNumber
	 * @param int $mode
	 * @param int $errorCorrectLevel
	 *
	 * @return int
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function getMaxLength(int $typeNumber, int $mode, int $errorCorrectLevel):int {

		if(!array_key_exists($errorCorrectLevel, self::RSBLOCK)){
			throw new QRCodeException('Invalid error correct level: '.$errorCorrectLevel);
		}

		if(!array_key_exists($mode, QRDataInterface::MODE)){
			throw new QRCodeException('Invalid mode: '.$mode);
		}

		return self::MAX_LENGTH[$typeNumber - 1][self::RSBLOCK[$errorCorrectLevel]][QRDataInterface::MODE[$mode]];
	}

	/**
	 * @param int $data
	 *
	 * @return int
	 */
	protected function getBCHTypeInfo(int $data):int {
		$G15_MASK = (1 << 14)|(1 << 12)|(1 << 10)|(1 << 4)|(1 << 1);
		$G15      = (1 << 10)|(1 << 8)|(1 << 5)|(1 << 4)|(1 << 2)|(1 << 1)|(1 << 0);

		return (($data << 10)|$this->getBCHT($data, 10, $G15))^$G15_MASK;
	}

	/**
	 * @param int $data
	 *
	 * @return int
	 */
	protected function getBCHTypeNumber(int $data):int{
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
	protected function getBCHDigit(int $data):int {
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
	protected function getRSBlocks(int $typeNumber, int $errorCorrectLevel):array {

		if(!array_key_exists($errorCorrectLevel, self::RSBLOCK)){
			throw new QRCodeException('$typeNumber: '.$typeNumber.' / $errorCorrectLevel: '.$errorCorrectLevel);
		}

		$rsBlock = self::BLOCK_TABLE[($typeNumber - 1) * 4 + self::RSBLOCK[$errorCorrectLevel]];
		$list    = [];
		$length  = count($rsBlock) / 3;

		for($i = 0; $i < $length; $i++){
			for($j = 0; $j < $rsBlock[$i * 3 + 0]; $j++){
				$list[] = [$rsBlock[$i * 3 + 1], $rsBlock[$i * 3 + 2]];
			}
		}

		return $list;
	}

	/**
	 * @param array $range
	 *
	 * @return void
	 */
	protected function testLevel1(array $range){

		foreach($range as $row){
			foreach($range as $col){
				$sameCount = 0;

				foreach([-1, 0, 1] as $rowRange){

					if($row + $rowRange < 0 || $this->pixelCount <= $row + $rowRange){
						continue;
					}

					foreach([-1, 0, 1] as $colRange){

						if(($rowRange === 0 && $colRange === 0) || ($col + $colRange < 0 || $this->pixelCount <= $col + $colRange)){
							continue;
						}

						if($this->matrix[$row + $rowRange][$col + $colRange] === $this->matrix[$row][$col]){
							$sameCount++;
						}

					}
				}

				if($sameCount > 5){
					$this->lostPoint += (3 + $sameCount - 5);
				}

			}
		}

	}

	/**
	 * @param array $range
	 *
	 * @return void
	 */
	protected function testLevel2(array $range){

		foreach($range as $row){
			foreach($range as $col){
				$count = 0;

				if(
					   $this->matrix[$row    ][$col    ]
					|| $this->matrix[$row    ][$col + 1]
					|| $this->matrix[$row + 1][$col    ]
					|| $this->matrix[$row + 1][$col + 1]
				){
					$count++;
				}

				if($count === 0 || $count === 4){
					$this->lostPoint += 3;
				}

			}
		}

	}

	/**
	 * @param array $range1
	 * @param array $range2
	 *
	 * @return void
	 */
	protected function testLevel3(array $range1, array $range2){

		foreach($range1 as $row){
			foreach($range2 as $col){

				if(
					    $this->matrix[$row][$col    ]
					&& !$this->matrix[$row][$col + 1]
					&&  $this->matrix[$row][$col + 2]
					&&  $this->matrix[$row][$col + 3]
					&&  $this->matrix[$row][$col + 4]
					&& !$this->matrix[$row][$col + 5]
					&&  $this->matrix[$row][$col + 6]
				){
					$this->lostPoint += 40;
				}

			}
		}

		foreach($range1 as $col){
			foreach($range2 as $row){

				if(
					    $this->matrix[$row    ][$col]
					&& !$this->matrix[$row + 1][$col]
					&&  $this->matrix[$row + 2][$col]
					&&  $this->matrix[$row + 3][$col]
					&&  $this->matrix[$row + 4][$col]
					&& !$this->matrix[$row + 5][$col]
					&&  $this->matrix[$row + 6][$col]
				){
					$this->lostPoint += 40;
				}

			}
		}

	}

	/**
	 * @param array $range
	 *
	 * @return void
	 */
	protected function testLevel4(array $range){

		foreach($range as $col){
			foreach($range as $row){
				if($this->matrix[$row][$col]){
					$this->darkCount++;
				}
			}
		}

	}

	/**
	 * @param int $pattern
	 *
	 * @return void
	 */
	protected function testPattern(int $pattern){
		$this->getMatrix(true, $pattern);
		$this->lostPoint = 0;
		$this->darkCount = 0;

		$range = range(0, $this->pixelCount - 1);

		$this->testLevel1($range);
		$this->testLevel2(range(0, $this->pixelCount - 2));
		$this->testLevel3($range, range(0, $this->pixelCount - 7));
		$this->testLevel4($range);

		$this->lostPoint += (abs(100 * $this->darkCount / $this->pixelCount / $this->pixelCount - 50) / 5) * 10;

		if($pattern === 0 || $this->minLostPoint > $this->lostPoint){
			$this->minLostPoint = $this->lostPoint;
			$this->maskPattern  = $pattern;
		}

	}

	/**
	 * @param bool $test
	 *
	 * @return void
	 */
	protected function setTypeNumber(bool $test){
		$bits = $this->getBCHTypeNumber($this->typeNumber);

		$range = range(0, 17);
		foreach($range as $i){
			$v = !$test && (($bits >> $i) & 1) === 1;

			$a = (int)floor($i / 3);
			$b = $i % 3 + $this->pixelCount - 8 - 3;

			$this->matrix[$a][$b] = $v;
			$this->matrix[$b][$a] = $v;
		}

	}

	/**
	 * @param bool $test
	 * @param int  $pattern
	 *
	 * @return void
	 */
	protected function setTypeInfo(bool $test, int $pattern){
		$this->setPattern();
		$bits = $this->getBCHTypeInfo(($this->errorCorrectLevel << 3) | $pattern);

		$range = range(0, 14);
		foreach($range as $i){
			$mod = !$test && (($bits >> $i) & 1) === 1;

			switch(true){
				case $i < 6:
					$this->matrix[$i][8] = $mod;
					break;
				case $i < 8:
					$this->matrix[$i + 1][8] = $mod;
					break;
				default:
					$this->matrix[$this->pixelCount - 15 + $i][8] = $mod;
			}

			switch(true){
				case $i < 8:
					$this->matrix[8][$this->pixelCount - $i - 1] = $mod;
					break;
				case $i < 9:
					$this->matrix[8][15 + 1 - $i - 1] = $mod;
					break;
				default:
					$this->matrix[8][15 - $i - 1] = $mod;
			}

		}

		$this->matrix[$this->pixelCount - 8][8] = !$test;
	}

	/**
	 * @return void
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function createData(){
		$MAX_BITS = self::MAX_BITS[$this->typeNumber][$this->errorCorrectLevel];
		$PAD0     = 0xEC;
		$PAD1     = 0x11;

		$this->bitBuffer
			->clear()
			->put($this->qrDataInterface->mode, 4)
			->put(
			$this->qrDataInterface->mode === QRDataInterface::MODE_KANJI
					? floor($this->qrDataInterface->dataLength / 2)
					: $this->qrDataInterface->dataLength,
				$this->qrDataInterface->getLengthInBits($this->typeNumber)
			);

		$this->qrDataInterface->write($this->bitBuffer);

		if($this->bitBuffer->length > $MAX_BITS){
			throw new QRCodeException('code length overflow. ('.$this->bitBuffer->length.' > '.$MAX_BITS.'bit)');
		}

		// end code.
		if($this->bitBuffer->length + 4 <= $MAX_BITS){
			$this->bitBuffer->put(0, 4);
		}

		// padding
		while($this->bitBuffer->length % 8 !== 0){
			$this->bitBuffer->putBit(false);
		}

		// padding
		while(true){

			if($this->bitBuffer->length >= $MAX_BITS){
				break;
			}

			$this->bitBuffer->put($PAD0, 8);

			if($this->bitBuffer->length >= $MAX_BITS){
				break;
			}

			$this->bitBuffer->put($PAD1, 8);
		}

	}

	/**
	 * @return array
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function createBytes():array{
		$rsBlocks       = $this->getRSBlocks($this->typeNumber, $this->errorCorrectLevel);
		$rsBlockCount   = count($rsBlocks);
		$ecdata         = array_fill(0, $rsBlockCount, null);
		$dcdata         = $ecdata;
		$totalCodeCount = 0;
		$maxDcCount     = 0;
		$maxEcCount     = 0;
		$offset         = 0;
		$index          = 0;

		foreach($rsBlocks as $key => $block){
			$rsBlockTotal     = $block[0];
			$rsBlockDataCount = $block[1];
			$maxDcCount       = max($maxDcCount, $rsBlockDataCount);
			$maxEcCount       = max($maxEcCount, $rsBlockTotal - $rsBlockDataCount);
			$dcdata[$key]     = array_fill(0, $rsBlockDataCount, null);

			foreach($dcdata[$key] as $a => &$_dcdata){
				$bdata   = $this->bitBuffer->buffer;
				$_dcdata = 0xff & $bdata[$a + $offset];
			}

			$offset += $rsBlockDataCount;

			$rsPoly  = new Polynomial;
			$modPoly = new Polynomial;

			$blockrange = range(0, $rsBlockTotal - $rsBlockDataCount - 1);

			foreach($blockrange as $b){
				$modPoly->setNum([1, $modPoly->gexp($b)]);
				$rsPoly->multiply($modPoly->num);
			}

			$rsPolyCount = count($rsPoly->num);

			$modPoly->setNum($dcdata[$key], $rsPolyCount - 1)->mod($rsPoly->num);

			$ecdata[$key] = array_fill(0, $rsPolyCount - 1, null);
			$add          = count($modPoly->num) - count($ecdata[$key]);

			foreach($ecdata[$key] as $c => &$_ecdata){
				$modIndex = $c + $add;
				$_ecdata  = $modIndex >= 0 ? $modPoly->num[$modIndex] : 0;
			}

			$totalCodeCount += $rsBlockTotal;
		}

		$data    = array_fill(0, $totalCodeCount, null);
		$rsrange = range(0, $rsBlockCount - 1);
		$dcrange = range(0, $maxDcCount - 1);
		$ecrange = range(0, $maxEcCount - 1);

		foreach($dcrange as $x){
			foreach($rsrange as $j){
				if($x < count($dcdata[$j])){
					$data[$index++] = $dcdata[$j][$x];
				}
			}
		}

		foreach($ecrange as $y){
			foreach($rsrange as $k){
				if($y < count($ecdata[$k])){
					$data[$index++] = $ecdata[$k][$y];
				}
			}
		}

		return $data;
	}

	/**
	 * @param int $pattern
	 *
	 * @return void
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function mapData(int $pattern){
		$this->createData();

		$data      = $this->createBytes();
		$inc       = -1;
		$row       = $this->pixelCount - 1;
		$bitIndex  = 7;
		$byteIndex = 0;
		$dataCount = count($data);

		for($col = $this->pixelCount - 1; $col > 0; $col -= 2){

			if($col === 6){
				$col--;
			}

			while(true){
				foreach([0, 1] as $c){
					$_col = $col - $c;

					if($this->matrix[$row][$_col] === null){
						$dark = false;

						if($byteIndex < $dataCount){
							$dark = (($data[$byteIndex] >> $bitIndex) & 1) === 1;
						}

						$a = $row + $_col;
						$m = $row * $_col;

						$MASK_PATTERN = [
							0 => $a % 2,
							1 => $row % 2,
							2 => $_col % 3,
							3 => $a % 3,
							4 => (floor($row / 2) + floor($_col / 3)) % 2,
							5 => $m % 2 + $m % 3,
							6 => ($m % 2 + $m % 3) % 2,
							7 => ($m % 3 + $a % 2) % 2,
						][$pattern];

						if($MASK_PATTERN === 0){
							$dark = !$dark;
						}

						$this->matrix[$row][$_col] = $dark;

						$bitIndex--;

						if($bitIndex === -1){
							$byteIndex++;
							$bitIndex = 7;
						}

					}
				}

				$row += $inc;

				if($row < 0 || $this->pixelCount <= $row){
					$row -= $inc;
					$inc = -$inc;

					break;
				}

			}
		}

	}

	/**
	 * @return void
	 */
	protected function setupPositionProbePattern(){
		$range = range(-1, 7);

		foreach([[0, 0], [$this->pixelCount - 7, 0], [0, $this->pixelCount - 7]] as $grid){
			$row = $grid[0];
			$col = $grid[1];

			foreach($range as $r){
				foreach($range as $c){

					if($row + $r <= -1 || $this->pixelCount <= $row + $r || $col + $c <= -1 || $this->pixelCount <= $col + $c){
						continue;
					}

					$this->matrix[$row + $r][$col + $c] =
						   (0 <= $r && $r <= 6 && ($c === 0 || $c === 6))
						|| (0 <= $c && $c <= 6 && ($r === 0 || $r === 6))
						|| (2 <= $c && $c <= 4 && 2 <= $r && $r <= 4);
				}
			}
		}

	}

	/**
	 * @return void
	 */
	protected function setupPositionAdjustPattern(){
		$range = self::PATTERN_POSITION[$this->typeNumber - 1];

		foreach($range as $i => $posI){
			foreach($range as $j => $posJ){

				if($this->matrix[$posI][$posJ] !== null){
					continue;
				}

				for($row = -2; $row <= 2; $row++){
					for($col = -2; $col <= 2; $col++){
						$this->matrix[$posI + $row][$posJ + $col] =
							    $row === -2 || $row === 2
							||  $col === -2
							||  $col === 2
							|| ($row === 0 && $col === 0);
					}
				}

			}
		}

	}

	/**
	 * @return void
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function setPattern(){
		$this->setupPositionProbePattern();
		$this->setupPositionAdjustPattern();

		// setupTimingPattern
		$range = range(8, $this->pixelCount - 8 - 1);
		foreach($range as $i){

			if($this->matrix[$i][6] !== null){
				continue; // @codeCoverageIgnore
			}

			$v = $i % 2 === 0;

			$this->matrix[$i][6] = $v;
			$this->matrix[6][$i] = $v;
		}

	}

	/**
	 * @param bool $test
	 * @param int  $maskPattern
	 *
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function getMatrix(bool $test, int $maskPattern){
		$this->pixelCount = $this->typeNumber * 4 + 17;
		$this->matrix     = array_fill(0, $this->pixelCount, array_fill(0, $this->pixelCount, null));

		$this->setTypeInfo($test, $maskPattern);

		if($this->typeNumber >= 7){
			$this->setTypeNumber($test);
		}

		$this->mapData($maskPattern);
	}

}
