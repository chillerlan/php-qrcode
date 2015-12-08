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

use chillerlan\QRCode\Data\QRDataInterface;

/**
 * @link https://github.com/kazuhikoarase/qrcode-generator/tree/master/php
 */
class QRCode{

	/**
	 * @var int
	 */
	public $pixelCount;

	/**
	 * @var array
	 */
	public $matrix;

	/**
	 * @var int
	 */
	protected $mode;

	/**
	 * @var int
	 */
	protected $typeNumber;

	/**
	 * @var int
	 */
	protected $errorCorrectLevel;

	/**
	 * @var \chillerlan\QRCode\Data\QRDataInterface
	 */
	protected $qrDataInterface;

	/**
	 * @var \chillerlan\QRCode\BitBuffer
	 */
	protected $bitBuffer;

	/**
	 * QRCode constructor.
	 *
	 * @param string $data
	 * @param int    $errorCorrectLevel
	 * @param int    $typeNumber
	 *
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function __construct($data = '', $errorCorrectLevel = QRConst::ERROR_CORRECT_LEVEL_M, $typeNumber = null){
		$this->bitBuffer = new BitBuffer;

		if(!empty($data)){
			$this->getQRCode($data, $errorCorrectLevel, $typeNumber);
		}

	}

	/**
	 * @param string $data
	 *
	 * @return $this
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function setData($data){
		$data = trim((string)$data);

		if(empty($data)){
			throw new QRCodeException('No data given.');
		}

		$this->mode = Util::getMode($data);

		$qrDataInterface = __NAMESPACE__.'\\Data\\'.[
			QRConst::MODE_ALPHANUM => 'AlphaNum',
			QRConst::MODE_BYTE     => 'Byte',
			QRConst::MODE_KANJI    => 'Kanji',
			QRConst::MODE_NUMBER   => 'Number',
		][$this->mode];

		$this->qrDataInterface = new $qrDataInterface($data);

		return $this;
	}

	/**
	 * @param int $errorCorrectLevel
	 *
	 * @return $this
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function setErrorCorrectLevel($errorCorrectLevel){

		if(!array_key_exists($errorCorrectLevel, QRConst::RSBLOCK)){
			throw new QRCodeException('Invalid error correct level: '.$errorCorrectLevel);
		}

		$this->errorCorrectLevel = $errorCorrectLevel;

		return $this;
	}

	/**
	 * @param int $typeNumber
	 *
	 * @return $this
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function setQRType($typeNumber){
		$this->typeNumber = intval($typeNumber);

		if($this->typeNumber < 1 || $this->typeNumber > 10){

			$length = $this->qrDataInterface->mode === QRConst::MODE_KANJI ? floor($this->qrDataInterface->dataLength / 2) : $this->qrDataInterface->dataLength;

			for($type = 1; $type <= 10; $type++){
				if($length <= Util::getMaxLength($type, $this->mode, $this->errorCorrectLevel)){
					$this->typeNumber = $type;

					return $this;
				}
			}

		}

		return $this;
	}

	/**
	 * @param string $data
	 * @param int    $errorCorrectLevel
	 * @param int    $typeNumber
	 *
	 * @return $this
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function getQRCode($data, $errorCorrectLevel = QRConst::ERROR_CORRECT_LEVEL_M, $typeNumber = null){

		$this
		     ->setData($data)
		     ->setErrorCorrectLevel($errorCorrectLevel)
		     ->setQRType($typeNumber)
		     ->getRawData()
		;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function getRawData(){

		// getLostPoint
		$minLostPoint = 0;
		$pattern = 0;

		for($i = 0; $i < 8; $i++){
			$this->getMatrix(true, $i);
			$lostPoint = 0;

			// LEVEL1
			for($row = 0; $row < $this->pixelCount; $row++){
				for($col = 0; $col < $this->pixelCount; $col++){
					$sameCount = 0;
					$dark = $this->matrix[$row][$col];

					for($r = -1; $r <= 1; $r++){

						if($row + $r < 0 || $this->pixelCount <= $row + $r){
							continue;
						}

						for($c = -1; $c <= 1; $c++){

							if(($r === 0 && $c === 0) || ($col + $c < 0 || $this->pixelCount <= $col + $c)){
								continue;
							}

							if($this->matrix[$row + $r][$col + $c] === $dark){
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
			for($row = 0; $row < $this->pixelCount - 1; $row++){
				for($col = 0; $col < $this->pixelCount - 1; $col++){
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
						$lostPoint += 3;
					}

				}
			}

			// LEVEL3
			for($row = 0; $row < $this->pixelCount; $row++){
				for($col = 0; $col < $this->pixelCount - 6; $col++){
					if(
						    $this->matrix[$row][$col    ]
						&& !$this->matrix[$row][$col + 1]
						&&  $this->matrix[$row][$col + 2]
						&&  $this->matrix[$row][$col + 3]
						&&  $this->matrix[$row][$col + 4]
						&& !$this->matrix[$row][$col + 5]
						&&  $this->matrix[$row][$col + 6]
					){
						$lostPoint += 40;
					}
				}
			}

			for($col = 0; $col < $this->pixelCount; $col++){
				for($row = 0; $row < $this->pixelCount - 6; $row++){
					if(
						    $this->matrix[$row    ][$col]
						&& !$this->matrix[$row + 1][$col]
						&&  $this->matrix[$row + 2][$col]
						&&  $this->matrix[$row + 3][$col]
						&&  $this->matrix[$row + 4][$col]
						&& !$this->matrix[$row + 5][$col]
						&&  $this->matrix[$row + 6][$col]
					){
						$lostPoint += 40;
					}
				}
			}

			// LEVEL4
			$darkCount = 0;
			for($col = 0; $col < $this->pixelCount; $col++){
				for($row = 0; $row < $this->pixelCount; $row++){
					if($this->matrix[$row][$col]){
						$darkCount++;
					}
				}
			}

			$ratio = abs(100 * $darkCount / $this->pixelCount / $this->pixelCount - 50) / 5;
			$lostPoint += $ratio * 10;

			if($i === 0 || $minLostPoint > $lostPoint){
				$minLostPoint = $lostPoint;
				$pattern = $i;
			}

		}

		$this->getMatrix(false, $pattern);

		return $this;
	}

	/**
	 * @param bool $test
	 * @param int  $pattern
	 */
	protected function setTypeInfo($test, $pattern){
		$this->setPattern();
		$bits = Util::getBCHTypeInfo(($this->errorCorrectLevel << 3) | $pattern);

		for($i = 0; $i < 15; $i++){
			$mod = !$test && (($bits >> $i) & 1) === 1;

			switch(true){
				case $i < 6:$this->matrix[$i    ][8] = $mod; break;
				case $i < 8:$this->matrix[$i + 1][8] = $mod; break;
				default:
					$this->matrix[$this->pixelCount - 15 + $i][8] = $mod;
			}

			switch(true){
				case $i < 8:$this->matrix[8][$this->pixelCount - $i - 1] = $mod; break;
				case $i < 9:$this->matrix[8][           15 + 1 - $i - 1] = $mod; break;
				default:
					$this->matrix[8][15 - $i - 1] = $mod;
			}

		}

		$this->matrix[$this->pixelCount - 8][8] = !$test;
	}

	/**
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function setPattern(){

		// setupPositionProbePattern
		foreach([[0, 0], [$this->pixelCount - 7, 0], [0, $this->pixelCount - 7]] as $grid){
			$row = $grid[0];
			$col = $grid[1];

			for($r = -1; $r <= 7; $r++){
				for($c = -1; $c <= 7; $c++){

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

		// setupPositionAdjustPattern
		$PATTERN_POSITION = QRConst::PATTERN_POSITION; // PHP5 compat
		$pos = $PATTERN_POSITION[$this->typeNumber - 1];
		foreach($pos as $i => $posI){
			foreach($pos as $j => $posJ){

				if($this->matrix[$posI][$posJ] !== null){
					continue;
				}

				for($row = -2; $row <= 2; $row++){
					for($col = -2; $col <= 2; $col++){
						$this->matrix[$posI + $row][$posJ + $col] =
							   $row === -2 || $row === 2
							|| $col === -2 || $col === 2
							||($row ===  0 && $col === 0);
					}
				}

			}
		}

		// setupTimingPattern
		for($i = 8; $i < $this->pixelCount - 8; $i++){

			if($this->matrix[$i][6] !== null){
				continue;
			}

			$this->matrix[$i][6] = $this->matrix[6][$i] = $i % 2 === 0;
		}

	}

	/**
	 * @param bool $test
	 *
	 * @return $this
	 */
	protected function setTypeNumber($test){
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
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function getMatrix($test, $pattern){
		if($this->typeNumber < 1 || $this->typeNumber > 10){
			throw new QRCodeException('Invalid type number '.$this->typeNumber);
		}

		$this->pixelCount = $this->typeNumber * 4 + 17;
		$this->matrix = array_fill(0, $this->pixelCount, array_fill(0, $this->pixelCount, null));
		$this->setTypeInfo($test, $pattern);

		if($this->typeNumber >= 7){
			$this->setTypeNumber($test);
		}

		$this->mapData($pattern);
	}

	/**
	 * @param int $pattern
	 *
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function mapData($pattern){
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
				for($c = 0; $c < 2; $c++){
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
	 * @return $this
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function createData(){

		if(!isset($this->errorCorrectLevel)){
			throw new QRCodeException('Invalid error correct level '.$this->errorCorrectLevel);
		}

		$this->bitBuffer->clear();

		$MAX_BITS = QRConst::MAX_BITS; // php5 compat
		$MAX_BITS = $MAX_BITS[$this->typeNumber][$this->errorCorrectLevel];

		$this->bitBuffer->put($this->qrDataInterface->mode, 4);
		$this->bitBuffer->put(
			$this->qrDataInterface->mode === QRConst::MODE_KANJI ? floor($this->qrDataInterface->dataLength / 2) : $this->qrDataInterface->dataLength,
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
	protected function createBytes(){
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

			for($i = 0; $i < $rsBlockTotal - $rsBlockDataCount; $i++){
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

		for($i = 0; $i < $maxDcCount; $i++){
			for($key = 0; $key < $rsBlockCount; $key++){
				if($i < count($dcdata[$key])){
					$data[$index++] = $dcdata[$key][$i];
				}
			}
		}

		for($i = 0; $i < $maxEcCount; $i++){
			for($key = 0; $key < $rsBlockCount; $key++){
				if($i < count($ecdata[$key])){
					$data[$index++] = $ecdata[$key][$i];
				}
			}
		}

		return $data;
	}

}
