<?php
/**
 * Class QRCode
 *
 * @filesource   QRCode.php
 * @created      26.11.2015
 * @package      codemasher\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace codemasher\QRCode;

use codemasher\QRCode\Polynomial;
use codemasher\QRCode\QRConst;
use codemasher\QRCode\Data\AlphaNum;
use codemasher\QRCode\Data\Byte;
use codemasher\QRCode\Data\Kanji;
use codemasher\QRCode\Data\Number;
use codemasher\QRCode\Data\QRDataInterface;

/**
 * @link https://github.com/kazuhikoarase/qrcode-generator/tree/master/php
 */
class QRCode{

	const QR_PAD0 = 0xEC;
	const QR_PAD1 = 0x11;

	/**
	 * @var int
	 */
	public $typeNumber;

	/**
	 * @var array
	 */
	protected $modules;

	/**
	 * @var int
	 */
	public $moduleCount;

	/**
	 * @var int
	 */
	public $errorCorrectLevel;

	/**
	 * @var array
	 */
	protected $qrDataList = [];

	/**
	 * @var array
	 */
	protected $rsBlockList = [];

	/**
	 * @var
	 */
	protected $data;

	/**
	 * @var \codemasher\QRCode\Util
	 */
	protected $util;

	/**
	 * @var \codemasher\QRCode\RSBlock
	 */
	protected $rsBlock;

	/**
	 * @var \codemasher\QRCode\BitBuffer
	 */
	protected $bitBuffer;

	/**
	 * QRCode constructor.
	 *
	 * @param int $typeNumber
	 * @param int $errorCorrectLevel
	 */
	public function __construct($typeNumber = 1, $errorCorrectLevel = QRConst::ERROR_CORRECT_LEVEL_H){
		$this->util = new Util;
		$this->rsBlock = new RSBlock;
		$this->bitBuffer = new BitBuffer;

		$this->typeNumber = $typeNumber;
		$this->errorCorrectLevel = $errorCorrectLevel;
	}

	/**
	 * @param string $data
	 * @param int    $mode
	 *
	 * @return $this
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function addData($data, $mode = null){
		if($mode === null){
			$mode = $this->util->getMode($data);
		}

		switch($mode){
			case QRConst::MODE_NUMBER   : $this->qrDataList[] = new Number($data); break;
			case QRConst::MODE_ALPHANUM:$this->qrDataList[] = new AlphaNum($data); break;
			case QRConst::MODE_BYTE:$this->qrDataList[] = new Byte($data); break;
			case QRConst::MODE_KANJI    : $this->qrDataList[] = new Kanji($data); break;
			default:
				throw new QRCodeException('mode: '.$mode);
		}

		return $this;
	}

	/**
	 * @param int $row
	 * @param int $col
	 *
	 * @return bool
	 */
	public function isDark($row, $col){
		if($this->modules[$row][$col] !== null){
			return (bool)$this->modules[$row][$col];
		}
		else{
			return false;
		}
	}

	/**
	 *
	 */
	public function make(){
		$this->makeImpl(false, $this->getBestMaskPattern());

		return $this;
	}

	/**
	 * @return int
	 */
	protected function getBestMaskPattern(){
		$minLostPoint = 0;
		$pattern = 0;

		for($i = 0; $i < 8; $i++){
			$this->makeImpl(true, $i);
			$lostPoint = 0;

			// LEVEL1

			for($row = 0; $row < $this->moduleCount; $row++){
				for($col = 0; $col < $this->moduleCount; $col++){
					$sameCount = 0;
					$dark = $this->isDark($row, $col);

					for($r = -1; $r <= 1; $r++){
						if($row + $r < 0 || $this->moduleCount <= $row + $r){
							continue;
						}

						for($c = -1; $c <= 1; $c++){

							if($col + $c < 0 || $this->moduleCount <= $col + $c){
								continue;
							}

							if($r == 0 && $c == 0){
								continue;
							}

							if($dark === $this->isDark($row + $r, $col + $c)){
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

			for($row = 0; $row < $this->moduleCount - 1; $row++){
				for($col = 0; $col < $this->moduleCount - 1; $col++){
					$count = 0;

					if($this->isDark($row, $col)){
						$count++;
					}

					if($this->isDark($row + 1, $col)){
						$count++;
					}

					if($this->isDark($row, $col + 1)){
						$count++;
					}

					if($this->isDark($row + 1, $col + 1)){
						$count++;
					}

					if($count === 0 || $count === 4){
						$lostPoint += 3;
					}
				}
			}

			// LEVEL3

			for($row = 0; $row < $this->moduleCount; $row++){
				for($col = 0; $col < $this->moduleCount - 6; $col++){
					if($this->isDark($row, $col)
							&& !$this->isDark($row, $col + 1)
							&& $this->isDark($row, $col + 2)
							&& $this->isDark($row, $col + 3)
							&& $this->isDark($row, $col + 4)
							&& !$this->isDark($row, $col + 5)
							&& $this->isDark($row, $col + 6)
					){
						$lostPoint += 40;
					}
				}
			}

			for($col = 0; $col < $this->moduleCount; $col++){
				for($row = 0; $row < $this->moduleCount - 6; $row++){
					if($this->isDark($row, $col)
							&& !$this->isDark($row + 1, $col)
							&& $this->isDark($row + 2, $col)
							&& $this->isDark($row + 3, $col)
							&& $this->isDark($row + 4, $col)
							&& !$this->isDark($row + 5, $col)
							&& $this->isDark($row + 6, $col)
					){
						$lostPoint += 40;
					}
				}
			}

			// LEVEL4

			$darkCount = 0;
			for($col = 0; $col < $this->moduleCount; $col++){
				for($row = 0; $row < $this->moduleCount; $row++){
					if($this->isDark($row, $col)){
						$darkCount++;
					}
				}
			}

			$ratio = abs(100 * $darkCount / $this->moduleCount / $this->moduleCount - 50) / 5;
			$lostPoint += $ratio * 10;

			if($i === 0 || $minLostPoint > $lostPoint){
				$minLostPoint = $lostPoint;
				$pattern = $i;
			}
		}

		return $pattern;
	}

	/**
	 * @param bool $test
	 * @param int  $maskPattern
	 *
	 * @return $this
	 */
	protected function makeImpl($test, $maskPattern){
		$this->moduleCount = $this->typeNumber * 4 + 17;
		$this->modules = [];

		$nullArray = [];
		for($i = 0; $i < $this->moduleCount; $i++){
			$nullArray[] = null;
		}

		for($i = 0; $i < $this->moduleCount; $i++){
			$this->modules[] = $nullArray;
		}

		$this->setupPositionProbePattern(0, 0)
		     ->setupPositionProbePattern($this->moduleCount - 7, 0)
		     ->setupPositionProbePattern(0, $this->moduleCount - 7);


		$pos = $this->util->PATTERN_POSITION[$this->typeNumber - 1];
		$posCount = count($pos);

		for($i = 0; $i < $posCount; $i++){
			for($j = 0; $j < $posCount; $j++){
				if($this->modules[$pos[$i]][$pos[$j]] !== null){
					continue;
				}

				for($r = -2; $r <= 2; $r++){
					for($c = -2; $c <= 2; $c++){
						$this->modules[$pos[$i] + $r][$pos[$j] + $c] =
								$r === -2 || $r === 2 || $c === -2 || $c === 2 || ($r === 0 && $c === 0);
					}
				}

			}
		}

		for($r = 8; $r < $this->moduleCount - 8; $r++){
			if($this->modules[$r][6] !== null){
				continue;
			}

			$this->modules[$r][6] = $this->modules[6][$r] = $r % 2 === 0;
		}

		$data = ($this->errorCorrectLevel << 3) | $maskPattern;
		$bits = $this->util->getBCHTypeInfo($data);

		for($i = 0; $i < 15; $i++){
			$mod = !$test && (($bits >> $i) & 1) === 1;

			if($i < 6){
				$this->modules[$i][8] = $mod;
			}
			else if($i < 8){
				$this->modules[$i + 1][8] = $mod;
			}
			else{
				$this->modules[$this->moduleCount - 15 + $i][8] = $mod;
			}

			if($i < 8){
				$this->modules[8][$this->moduleCount - $i - 1] = $mod;
			}
			else if($i < 9){
				$this->modules[8][15 - $i - 1 + 1] = $mod;
			}
			else{
				$this->modules[8][15 - $i - 1] = $mod;
			}

		}

		$this->modules[$this->moduleCount - 8][8] = !$test;

		if($this->typeNumber >= 7){
			$bits = $this->util->getBCHTypeNumber($this->typeNumber);

			for($i = 0; $i < 18; $i++){
				$a = (int)floor($i / 3);
				$b = $i % 3 + $this->moduleCount - 8 - 3;
				$mod = !$test && (($bits >> $i) & 1) === 1;

				$this->modules[$a][$b] = $this->modules[$b][$a] = $mod;
			}
		}

		$this->data = $this->createData($this->typeNumber, $this->errorCorrectLevel);

		$this->mapData($maskPattern);

		return $this;
	}

	/**
	 * @param $maskPattern
	 *
	 * @return $this
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	protected function mapData($maskPattern){
		$inc = -1;
		$row = $this->moduleCount - 1;
		$bitIndex = 7;
		$byteIndex = 0;
		$dataCount = count($this->data);

		for($col = $this->moduleCount - 1; $col > 0; $col -= 2){

			if($col === 6){
				$col--;
			}

			while(true){
				for($c = 0; $c < 2; $c++){

					if($this->modules[$row][$col - $c] === null){
						$dark = false;

						if($byteIndex < $dataCount){
							$dark = (($this->data[$byteIndex] >> $bitIndex) & 1) === 1;
						}

						$_col = $col - $c;
						switch($maskPattern){
							case QRConst::MASK_PATTERN000: $mask = ($row + $_col) % 2 === 0; break;
							case QRConst::MASK_PATTERN001: $mask = $row % 2 === 0; break;
							case QRConst::MASK_PATTERN010: $mask = $_col % 3 === 0; break;
							case QRConst::MASK_PATTERN011: $mask = ($row + $_col) % 3 === 0; break;
							case QRConst::MASK_PATTERN100: $mask = (floor($row / 2) + floor($_col / 3)) % 2 === 0; break;
							case QRConst::MASK_PATTERN101: $mask = ($row * $_col) % 2 + ($row * $_col) % 3 === 0; break;
							case QRConst::MASK_PATTERN110: $mask = (($row * $_col) % 2 + ($row * $_col) % 3) % 2 === 0; break;
							case QRConst::MASK_PATTERN111: $mask = (($row * $_col) % 3 + ($row + $_col) % 2) % 2 === 0; break;
							default :
								throw new QRCodeException('mask: '.$maskPattern);
						}

						if($mask){
							$dark = !$dark;
						}

						$this->modules[$row][$col - $c] = $dark;
						$bitIndex--;

						if($bitIndex === -1){
							$byteIndex++;
							$bitIndex = 7;
						}

					}
				}

				$row += $inc;

				if($row < 0 || $this->moduleCount <= $row){
					$row -= $inc;
					$inc = -$inc;
					break;
				}
			}
		}

		return $this;
	}

	/**
	 * @param $row
	 * @param $col
	 *
	 * @return $this
	 */
	protected function setupPositionProbePattern($row, $col){

		for($r = -1; $r <= 7; $r++){
			for($c = -1; $c <= 7; $c++){

				if($row + $r <= -1 || $this->moduleCount <= $row + $r || $col + $c <= -1 || $this->moduleCount <= $col + $c){
					continue;
				}

				$this->modules[$row + $r][$col + $c] =
					(0 <= $r && $r <= 6 && ($c === 0 || $c === 6))
						|| (0 <= $c && $c <= 6 && ($r == 0 || $r === 6))
						|| (2 <= $r && $r <= 4 && 2 <= $c && $c <= 4);
			}
		}

		return $this;
	}

	/**
	 * @param $typeNumber
	 * @param $errorCorrectLevel
	 *
	 * @return array
	 * @throws \codemasher\QRCode\QRCodeException
	 * @todo: slooooow in PHP5
	 *
	 */
	protected function createData($typeNumber, $errorCorrectLevel){
		$this->bitBuffer->reset();

		$count = count($this->qrDataList);
		for($i = 0; $i < $count; $i++){
			/** @var \codemasher\QRCode\Data\QRDataInterface $data */
			$data = $this->qrDataList[$i];

			$this->bitBuffer->put($data->mode, 4);
			$this->bitBuffer->put($data->getLength(), $data->getLengthInBits($typeNumber));
			$data->write($this->bitBuffer);
		}

		$this->rsBlockList = $this->rsBlock->getRSBlocks($typeNumber, $errorCorrectLevel);
		$totalDataCount = 0;

		$count = count($this->rsBlockList);
		for($i = 0; $i < $count; $i++){
			$this->rsBlock->totalCount = $this->rsBlockList[$i][0];
			$this->rsBlock->dataCount = $this->rsBlockList[$i][1];

			$totalDataCount += $this->rsBlock->dataCount;
		}

		if($this->bitBuffer->length > $totalDataCount * 8){
			throw new QRCodeException('code length overflow. ('.$this->bitBuffer->length.'>'.($totalDataCount * 8).')');
		}

		// end code.
		if($this->bitBuffer->length + 4 <= $totalDataCount * 8){
			$this->bitBuffer->put(0, 4);
		}

		// padding
		while($this->bitBuffer->length % 8 != 0){
			$this->bitBuffer->putBit(false);
		}

		// padding
		while(true){

			if($this->bitBuffer->length >= $totalDataCount * 8){
				break;
			}
			$this->bitBuffer->put(self::QR_PAD0, 8);

			if($this->bitBuffer->length >= $totalDataCount * 8){
				break;
			}

			$this->bitBuffer->put(self::QR_PAD1, 8);
		}


		$offset = $maxDcCount = $maxEcCount = 0;
		$rsBlockCount = count($this->rsBlockList);

		$nullArray = [];
		for($i = 0; $i < $rsBlockCount; $i++){
			$nullArray[] = null;
		}

		$dcdata = $ecdata = $nullArray;
		$totalCodeCount = 0;

		for($r = 0; $r < $rsBlockCount; $r++){
			$this->rsBlock->totalCount = $this->rsBlockList[$r][0];
			$this->rsBlock->dataCount = $this->rsBlockList[$r][1];


			$dcCount = $this->rsBlock->dataCount;
			$ecCount = $this->rsBlock->totalCount - $dcCount;

			$maxDcCount = max($maxDcCount, $dcCount);
			$maxEcCount = max($maxEcCount, $ecCount);

			$_nullArray = [];
			for($i = 0; $i < $dcCount; $i++){
				$_nullArray[] = null;
			}

			$dcdata[$r] = $_nullArray;
			$dcdataCount = count($dcdata[$r]);
			for($i = 0; $i < $dcdataCount; $i++){
				$bdata = $this->bitBuffer->buffer;
				$dcdata[$r][$i] = 0xff & $bdata[$i + $offset];
			}
			$offset += $dcCount;


			$rsPoly = new Polynomial;
			$modPoly = new Polynomial;

$starttime = microtime(true);
			// 0.09s
			for($i = 0; $i < $ecCount; $i++){
				$modPoly->setNum([1, $modPoly->gexp($i)]);
				$rsPoly->multiply($modPoly->num);
			}

			$rsPolyCount = count($rsPoly->num);

			// 0.11s
			$modPoly->setNum($dcdata[$r], $rsPolyCount - 1)->mod($rsPoly->num);

echo 'QRCode::createData '.round((microtime(true)-$starttime), 5).PHP_EOL;

			$modPolyCount = count($modPoly->num);

			$_nullArray = [];
			for($i = 0; $i < $rsPolyCount - 1; $i++){
				$_nullArray[] = null;
			}

			$ecdata[$r] = $_nullArray;
			$ecdataCount = count($ecdata[$r]);

			for($i = 0; $i < $ecdataCount; $i++){
				$modIndex = $i + $modPolyCount - $ecdataCount;
				$ecdata[$r][$i] = ($modIndex >= 0) ? $modPoly->num[$modIndex] : 0;
			}

			$this->rsBlock->totalCount = $this->rsBlockList[$r][0];
			$this->rsBlock->dataCount = $this->rsBlockList[$r][1];

			$totalCodeCount += $this->rsBlock->totalCount;
		}

		$nullArray = [];
		for($i = 0; $i < $totalCodeCount; $i++){
			$nullArray[] = null;
		}

		$data = $nullArray;
		$index = 0;
		for($i = 0; $i < $maxDcCount; $i++){
			for($r = 0; $r < $rsBlockCount; $r++){
				if($i < count($dcdata[$r])){
					$data[$index++] = $dcdata[$r][$i];
				}
			}
		}

		for($i = 0; $i < $maxEcCount; $i++){
			for($r = 0; $r < $rsBlockCount; $r++){
				if($i < count($ecdata[$r])){
					$data[$index++] = $ecdata[$r][$i];
				}
			}
		}

		return $data;
	}

	/**
	 * @param $data
	 * @param $errorCorrectLevel
	 *
	 * @return \codemasher\QRCode\QRCode
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function getMinimumQRCode($data, $errorCorrectLevel = QRConst::ERROR_CORRECT_LEVEL_H){
		$mode = $this->util->getMode($data);
		$this->addData($data, $mode);

		$this->errorCorrectLevel = $errorCorrectLevel;

		/** @var \codemasher\QRCode\Data\QRDataBase $qrData */
		$qrData = $this->qrDataList[0];
		$length = $qrData->getLength();

		for($typeNumber = 1; $typeNumber <= 10; $typeNumber++){
			if($length <= $this->util->getMaxLength($typeNumber, $mode, $this->errorCorrectLevel)){
				$this->typeNumber = $typeNumber;
				break;
			}
		}

		$this->makeImpl(false, $this->getBestMaskPattern());

		return $this;
	}

	/**
	 * added $fg (foreground), $bg (background), and $bgtrans (use transparent bg) parameters
	 * also added some simple error checking on parameters
	 * updated 2015.07.27 ~ DoktorJ
	 *
	 * @param int        $size
	 * @param int        $margin
	 * @param int        $fg
	 * @param int        $bg
	 * @param bool|false $bgtrans
	 *
	 * @return resource
	 */
	public function createImage($size = 2, $margin = 2, $fg = 0x000000, $bg = 0xFFFFFF, $bgtrans = false){

		// size/margin EC
		if(!is_numeric($size)){
			$size = 2;
		}
		if(!is_numeric($margin)){
			$margin = 2;
		}
		if($size < 1){
			$size = 1;
		}
		if($margin < 0){
			$margin = 0;
		}

		$image_size = $this->moduleCount * $size + $margin * 2;

		$image = imagecreatetruecolor($image_size, $image_size);

		// fg/bg EC
		if($fg < 0 || $fg > 0xFFFFFF){
			$fg = 0x0;
		}
		if($bg < 0 || $bg > 0xFFFFFF){
			$bg = 0xFFFFFF;
		}

		// convert hexadecimal RGB to arrays for imagecolorallocate
		$fgrgb = $this->util->hex2rgb($fg);
		$bgrgb = $this->util->hex2rgb($bg);

		// replace $black and $white with $fgc and $bgc
		$fgc = imagecolorallocate($image, $fgrgb['r'], $fgrgb['g'], $fgrgb['b']);
		$bgc = imagecolorallocate($image, $bgrgb['r'], $bgrgb['g'], $bgrgb['b']);
		if($bgtrans){
			imagecolortransparent($image, $bgc);
		}

		// update $white to $bgc
		imagefilledrectangle($image, 0, 0, $image_size, $image_size, $bgc);

		for($r = 0; $r < $this->moduleCount; $r++){
			for($c = 0; $c < $this->moduleCount; $c++){
				if($this->isDark($r, $c)){

					// update $black to $fgc
					imagefilledrectangle($image,
						$margin + $c * $size,
						$margin + $r * $size,
						$margin + ($c + 1) * $size - 1,
						$margin + ($r + 1) * $size - 1,
						$fgc);
				}
			}
		}

		return $image;
	}

	/**
	 * @return string
	 */
	public function printHTML(){
		$html = '<table class="qrcode">';
		$count = $this->moduleCount;

		for($r = 0; $r < $count; $r++){
			$html .= '<tr>';

			for($c = 0; $c < $count; $c++){
				$html .= '<td class="'.($this->isDark($r, $c) ? 'dark' : 'light').'"></td>';
			}

			$html .= '</tr>';
		}

		$html .= '</table>';

		return $html;
	}

}
