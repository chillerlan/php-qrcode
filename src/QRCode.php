<?php
/**
 * Class QRCode
 *
 * @filesource   QRCode.php
 * @created      26.11.2015
 * @package      chillerlan\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode;

use chillerlan\QRCode\Data\{AlphaNum, Byte, Kanji, Number};
use chillerlan\QRCode\Output\QROutputInterface;

/**
 * @link https://github.com/kazuhikoarase/qrcode-generator/tree/master/php
 * @link http://www.thonky.com/qr-code-tutorial/
 */
class QRCode{

	/**
	 * API constants
	 */
	const OUTPUT_STRING_TEXT = 'txt';
	const OUTPUT_STRING_JSON = 'json';

	const OUTPUT_MARKUP_HTML = 'html';
	const OUTPUT_MARKUP_SVG  = 'svg';
#	const OUTPUT_MARKUP_XML  = 'xml'; // anyone?

	const OUTPUT_IMAGE_PNG = 'png';
	const OUTPUT_IMAGE_JPG = 'jpg';
	const OUTPUT_IMAGE_GIF = 'gif';

	const ERROR_CORRECT_LEVEL_L = 1; // 7%.
	const ERROR_CORRECT_LEVEL_M = 0; // 15%.
	const ERROR_CORRECT_LEVEL_Q = 3; // 25%.
	const ERROR_CORRECT_LEVEL_H = 2; // 30%.

	// max bits @ ec level L:07 M:15 Q:25 H:30 %
	const TYPE_01 =  1; //  152  128  104   72
	const TYPE_02 =  2; //  272  224  176  128
	const TYPE_03 =  3; //  440  352  272  208
	const TYPE_04 =  4; //  640  512  384  288
	const TYPE_05 =  5; //  864  688  496  368
	const TYPE_06 =  6; // 1088  864  608  480
	const TYPE_07 =  7; // 1248  992  704  528
	const TYPE_08 =  8; // 1552 1232  880  688
	const TYPE_09 =  9; // 1856 1456 1056  800
	const TYPE_10 = 10; // 2192 1728 1232  976

	/**
	 * @var array
	 */
	protected $matrix = [];

	/**
	 * @var int
	 */
	protected $pixelCount = 0;

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
	 * @var \chillerlan\QRCode\BitBuffer
	 */
	protected $bitBuffer;

	/**
	 * @var \chillerlan\QRCode\Data\QRDataInterface
	 */
	protected $qrDataInterface;

	/**
	 * @var \chillerlan\QRCode\Output\QROutputInterface
	 */
	protected $qrOutputInterface;

	/**
	 * QRCode constructor.
	 *
	 * @param string                                      $data
	 * @param \chillerlan\QRCode\Output\QROutputInterface $output
	 * @param \chillerlan\QRCode\QROptions|null           $options
	 */
	public function __construct($data, QROutputInterface $output, QROptions $options = null){
		$this->qrOutputInterface = $output;
		$this->bitBuffer = new BitBuffer;
		$this->setData($data, $options);
	}

	/**
	 * @param string                            $data
	 * @param \chillerlan\QRCode\QROptions|null $options
	 *
	 * @return \chillerlan\QRCode\QRCode
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function setData(string $data, QROptions $options = null):QRCode {
		$data = trim($data);

		if(empty($data)){
			throw new QRCodeException('No data given.');
		}

		if(!$options instanceof QROptions){
			$options = new QROptions;
		}

		if(!in_array($options->errorCorrectLevel, QRConst::RSBLOCK, true)){
			throw new QRCodeException('Invalid error correct level: '.$options->errorCorrectLevel);
		}

		$this->errorCorrectLevel = $options->errorCorrectLevel;

		switch(true){
			case Util::isAlphaNum($data):
				$mode = Util::isNumber($data) ? QRConst::MODE_NUMBER : QRConst::MODE_ALPHANUM;
				break;
			case Util::isKanji($data):
				$mode = QRConst::MODE_KANJI;
				break;
			default:
				$mode = QRConst::MODE_BYTE;
				break;
		}

		// see, Scrunitizer, it is concrete! :P
		$qrDataInterface = [
			QRConst::MODE_ALPHANUM => AlphaNum::class,
			QRConst::MODE_BYTE     => Byte::class,
			QRConst::MODE_KANJI    => Kanji::class,
			QRConst::MODE_NUMBER   => Number::class,
		][$mode];

		$this->qrDataInterface = new $qrDataInterface($data);
		$this->typeNumber = intval($options->typeNumber);

		if($this->typeNumber < 1 || $this->typeNumber > 10){
			$this->typeNumber = $this->getTypeNumber($mode);
		}

		return $this;
	}

	/**
	 * @param int $mode
	 *
	 * @return int
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function getTypeNumber(int $mode):int {
		$length = $this->qrDataInterface->dataLength;

		if($this->qrDataInterface->mode === QRConst::MODE_KANJI){
			$length = floor($length / 2);
		}

		foreach(range(1, 10) as $type){
			if($length <= Util::getMaxLength($type, $mode, $this->errorCorrectLevel)){
				return $type;
			}
		}

		throw new QRCodeException('Unable to determine type number.'); // @codeCoverageIgnore
	}

	/**
	 * @return mixed
	 */
	public function output(){
		$this->qrOutputInterface->setMatrix($this->getRawData());

		return $this->qrOutputInterface->dump();
	}

	/**
	 * @return array
	 */
	public function getRawData():array {
		$this->minLostPoint = 0;
		$this->maskPattern = 0;

		for($pattern = 0; $pattern <= 7; $pattern++){
			$this->testPattern($pattern);
		}

		$this->getMatrix(false, $this->maskPattern);

		return $this->matrix;
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

				foreach([-1, 0, 1] as $rr){
					if($row + $rr < 0 || $this->pixelCount <= $row + $rr){
						continue;
					}

					foreach([-1, 0, 1] as $cr){

						if(($rr === 0 && $cr === 0) || ($col + $cr < 0 || $this->pixelCount <= $col + $cr)){
							continue;
						}

						if($this->matrix[$row + $rr][$col + $cr] === $this->matrix[$row][$col]){
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

		$range = range(0, $this->pixelCount-1);

		$this->testLevel1($range);
		$this->testLevel2(range(0, $this->pixelCount-2));
		$this->testLevel3($range, range(0, $this->pixelCount-7));
		$this->testLevel4($range);

		$this->lostPoint += (abs(100 * $this->darkCount / $this->pixelCount / $this->pixelCount - 50) / 5) * 10;

		if($pattern === 0 || $this->minLostPoint > $this->lostPoint){
			$this->minLostPoint = $this->lostPoint;
			$this->maskPattern = $pattern;
		}

	}

	/**
	 * @param bool $test
	 *
	 * @return void
	 */
	protected function setTypeNumber(bool $test){
		$bits = Util::getBCHTypeNumber($this->typeNumber);

		for($i = 0; $i < 18; $i++){
			$a = (int)floor($i / 3);
			$b = $i % 3 + $this->pixelCount - 8 - 3;

			$this->matrix[$a][$b] = $this->matrix[$b][$a] = !$test && (($bits >> $i) & 1) === 1;
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
		$bits = Util::getBCHTypeInfo(($this->errorCorrectLevel << 3) | $pattern);

		for($i = 0; $i < 15; $i++){
			$mod = !$test && (($bits >> $i) & 1) === 1;

			switch(true){
				case $i < 6: $this->matrix[$i    ][8] = $mod; break;
				case $i < 8: $this->matrix[$i + 1][8] = $mod; break;
				default:
					$this->matrix[$this->pixelCount - 15 + $i][8] = $mod;
			}

			switch(true){
				case $i < 8: $this->matrix[8][$this->pixelCount - $i - 1] = $mod; break;
				case $i < 9: $this->matrix[8][           15 + 1 - $i - 1] = $mod; break;
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
		$this->bitBuffer->clear();
		$this->bitBuffer->put($this->qrDataInterface->mode, 4);
		$this->bitBuffer->put(
			$this->qrDataInterface->mode === QRConst::MODE_KANJI
				? floor($this->qrDataInterface->dataLength / 2)
				: $this->qrDataInterface->dataLength,
			$this->qrDataInterface->getLengthInBits($this->typeNumber)
		);

		$this->qrDataInterface->write($this->bitBuffer);

		$MAX_BITS = QRConst::MAX_BITS[$this->typeNumber][$this->errorCorrectLevel];

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

			$this->bitBuffer->put(QRConst::PAD0, 8);

			if($this->bitBuffer->length >= $MAX_BITS){
				break;
			}

			$this->bitBuffer->put(QRConst::PAD1, 8);
		}

	}

	/**
	 * @return array
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function createBytes():array {
		$totalCodeCount = $maxDcCount = $maxEcCount = $offset = $index = 0;
		$rsBlocks = Util::getRSBlocks($this->typeNumber, $this->errorCorrectLevel);
		$rsBlockCount = count($rsBlocks);
		$dcdata = $ecdata = array_fill(0, $rsBlockCount, null);

		foreach($rsBlocks as $key => $value){
			$rsBlockTotal = $value[0];
			$rsBlockDataCount = $value[1];

			$maxDcCount = max($maxDcCount, $rsBlockDataCount);
			$maxEcCount = max($maxEcCount, $rsBlockTotal - $rsBlockDataCount);

			$dcdata[$key] = array_fill(0, $rsBlockDataCount, null);

			foreach($dcdata[$key] as $i => &$_dcdata){
				$bdata = $this->bitBuffer->buffer;
				$_dcdata = 0xff & $bdata[$i + $offset];
			}

			$offset += $rsBlockDataCount;

			$rsPoly = new Polynomial;
			$modPoly = new Polynomial;

			foreach(range(0, $rsBlockTotal - $rsBlockDataCount - 1) as $i){
				$modPoly->setNum([1, $modPoly->gexp($i)]);
				$rsPoly->multiply($modPoly->num);
			}

			$rsPolyCount = count($rsPoly->num);
			$modPoly->setNum($dcdata[$key], $rsPolyCount - 1)->mod($rsPoly->num);
			$ecdata[$key] = array_fill(0, $rsPolyCount - 1, null);
			$add = count($modPoly->num) - count($ecdata[$key]);

			foreach($ecdata[$key] as $i => &$_ecdata){
				$modIndex = $i + $add;
				$_ecdata = $modIndex >= 0 ? $modPoly->num[$modIndex] : 0;
			}

			$totalCodeCount += $rsBlockTotal;
		}

		$data = array_fill(0, $totalCodeCount, null);
		$rsrange = range(0, $rsBlockCount - 1);

		foreach(range(0, $maxDcCount - 1) as $i){
			foreach($rsrange as $key){
				if($i < count($dcdata[$key])){
					$data[$index++] = $dcdata[$key][$i];
				}
			}
		}

		foreach(range(0, $maxEcCount - 1) as $i){
			foreach($rsrange as $key){
				if($i < count($ecdata[$key])){
					$data[$index++] = $ecdata[$key][$i];
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
		$data = $this->createBytes();
		$inc = -1;
		$row = $this->pixelCount - 1;
		$bitIndex = 7;
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
							QRConst::MASK_PATTERN000 => $a % 2,
							QRConst::MASK_PATTERN001 => $row % 2,
							QRConst::MASK_PATTERN010 => $_col % 3,
							QRConst::MASK_PATTERN011 => $a % 3,
							QRConst::MASK_PATTERN100 => (floor($row / 2) + floor($_col / 3)) % 2,
							QRConst::MASK_PATTERN101 => $m % 2 + $m % 3,
							QRConst::MASK_PATTERN110 => ($m % 2 + $m % 3) % 2,
							QRConst::MASK_PATTERN111 => ($m % 3 + $a % 2) % 2,
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
						|| (2 <= $c && $c <= 4 &&  2 <= $r && $r <= 4);
				}
			}
		}

	}

	/**
	 * @return void
	 */
	protected function setupPositionAdjustPattern(){
		$range = QRConst::PATTERN_POSITION[$this->typeNumber - 1];

		foreach($range as $i => $posI){
			foreach($range as $j => $posJ){
				if($this->matrix[$posI][$posJ] !== null){
					continue;
				}

				for($row = -2; $row <= 2; $row++){
					for($col = -2; $col <= 2; $col++){
						$this->matrix[$posI + $row][$posJ + $col] =
							    $row === -2 || $row === 2
							||  $col === -2 || $col === 2
							|| ($row ===  0 && $col === 0);
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
		for($i = 8; $i < $this->pixelCount - 8; $i++){
			if($this->matrix[$i][6] !== null){
				continue; // @codeCoverageIgnore
			}

			$this->matrix[$i][6] = $this->matrix[6][$i] = $i % 2 === 0;
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
		$this->matrix = array_fill(0, $this->pixelCount, array_fill(0, $this->pixelCount, null));
		$this->setTypeInfo($test, $maskPattern);

		if($this->typeNumber >= 7){
			$this->setTypeNumber($test);
		}

		$this->mapData($maskPattern);
	}

}
