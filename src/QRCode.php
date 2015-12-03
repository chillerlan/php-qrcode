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
			case QRConst::MODE_NUMBER  : $this->qrDataList[] = new Number($data); break;
			case QRConst::MODE_ALPHANUM: $this->qrDataList[] = new AlphaNum($data); break;
			case QRConst::MODE_BYTE    : $this->qrDataList[] = new Byte($data); break;
			case QRConst::MODE_KANJI   : $this->qrDataList[] = new Kanji($data); break;
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
		$length = $qrData->mode === QRConst::MODE_KANJI ? floor($qrData->dataLength / 2) : $qrData->dataLength;

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
	 * @param bool $test
	 * @param int  $maskPattern
	 *
	 * @return $this
	 */
	protected function makeImpl($test, $maskPattern){
		$this->moduleCount = $this->typeNumber * 4 + 17;
		$this->modules = [];

		$nullArray = array_fill(0, $this->moduleCount, null);
		for($i = 0; $i < $this->moduleCount; $i++){
			$this->modules[] = $nullArray;
		}

		$this->setupPositionProbePattern(0, 0)
		     ->setupPositionProbePattern($this->moduleCount - 7, 0)
		     ->setupPositionProbePattern(0, $this->moduleCount - 7)
		     ->setupPositionAdjustPattern()
		     ->setupTimingPattern();

		$data = ($this->errorCorrectLevel << 3) | $maskPattern;
		$bits = $this->util->getBCHTypeInfo($data);

		for($i = 0; $i < 15; $i++){
			$mod = !$test && (($bits >> $i) & 1) === 1;

			switch(true){
				case $i < 6: $this->modules[$i][8] = $mod; break;
				case $i < 8: $this->modules[$i + 1][8] = $mod; break;
				default:
					$this->modules[$this->moduleCount - 15 + $i][8] = $mod;
			}

			switch(true){
				case $i < 8: $this->modules[8][$this->moduleCount - $i - 1] = $mod; break;
				case $i < 9: $this->modules[8][15 - $i - 1 + 1] = $mod; break;
				default:
					$this->modules[8][15 - $i - 1] = $mod;
			}

		}

		$this->modules[$this->moduleCount - 8][8] = !$test;

		if($this->typeNumber >= 7){
			$bits = $this->util->getBCHTypeNumber($this->typeNumber);

			for($i = 0; $i < 18; $i++){
				$a = (int)floor($i / 3);
				$b = $i % 3 + $this->moduleCount - 8 - 3;

				$this->modules[$a][$b] = $this->modules[$b][$a] = !$test && (($bits >> $i) & 1) === 1;
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
		$range = range(-1, 7);

		foreach($range as $r){
			foreach($range as $c){

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
	 * @return $this
	 */
	protected function setupPositionAdjustPattern(){
		$pos = $this->util->PATTERN_POSITION[$this->typeNumber - 1];

		foreach($pos as $i => $posI){
			foreach($pos as $j => $posJ){
				if($this->modules[$posI][$posJ] !== null){
					continue;
				}

				for($row = -2; $row <= 2; $row++){
					for($col = -2; $col <= 2; $col++){
						$this->modules[$posI + $row][$posJ + $col] =
							(bool)$row === -2 || $row === 2 || $col === -2 || $col === 2 || ($row === 0 && $col === 0);
					}
				}

			}
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	protected function setupTimingPattern(){

		for($i = 8; $i < $this->moduleCount - 8; $i++){
			if($this->modules[$i][6] !== null){
				continue;
			}

			$this->modules[$i][6] = $this->modules[6][$i] = $i % 2 === 0;
		}

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
	 * @param $typeNumber
	 * @param $errorCorrectLevel
	 *
	 * @return array
	 * @throws \codemasher\QRCode\QRCodeException
	 * @todo: slooooow in PHP5
	 *
	 */
	protected function createData($typeNumber, $errorCorrectLevel){
		$this->bitBuffer->buffer = [];
		$this->bitBuffer->length = 0;

		$this->rsBlockList = $this->rsBlock->getRSBlocks($typeNumber, $errorCorrectLevel);

		$totalDataCount = $totalCodeCount = $offset = $maxDcCount = $maxEcCount = 0;

		/** @var \codemasher\QRCode\Data\QRDataInterface $data */
		foreach($this->qrDataList as &$data){
			$len = $data->mode === QRConst::MODE_KANJI ? floor($data->dataLength / 2) : $data->dataLength;

			$this->bitBuffer->put($data->mode, 4);
			$this->bitBuffer->put($len, $data->getLengthInBits($typeNumber));
			$data->write($this->bitBuffer);
		}

		foreach($this->rsBlockList as &$data){
			$this->rsBlock->totalCount = $data[0];
			$this->rsBlock->dataCount = $data[1];

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
		while($this->bitBuffer->length % 8 !== 0){
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

		$rsBlockCount = count($this->rsBlockList);
		$dcdata = $ecdata = array_fill(0, $rsBlockCount, null);

		foreach($this->rsBlockList as $r => &$_rsData){
			$this->rsBlock->totalCount = $_rsData[0];
			$this->rsBlock->dataCount = $_rsData[1];

			$maxDcCount = max($maxDcCount, $this->rsBlock->dataCount);
			$maxEcCount = max($maxEcCount, $this->rsBlock->totalCount - $this->rsBlock->dataCount);

			$dcdata[$r] = array_fill(0, $this->rsBlock->dataCount, null);

			$rsPoly = new Polynomial;
			$modPoly = new Polynomial;

			foreach($dcdata[$r] as $i => &$_dcdata){
				$bdata = $this->bitBuffer->buffer;
				$_dcdata = 0xff & $bdata[$i + $offset];
			}

			$offset += $this->rsBlock->dataCount;

			// PHP5: 0.09s
			for($i = 0; $i < $this->rsBlock->totalCount - $this->rsBlock->dataCount; $i++){
				$modPoly->setNum([1, $modPoly->gexp($i)]);
				$rsPoly->multiply($modPoly->num);
			}

			// PHP5: 0.11s
			$rsPolyCount = count($rsPoly->num);
			$modPoly->setNum($dcdata[$r], $rsPolyCount - 1)->mod($rsPoly->num);
			$ecdata[$r] = array_fill(0, $rsPolyCount - 1, null);
			$add = count($modPoly->num) - count($ecdata[$r]);

			foreach($ecdata[$r] as $i => &$_ecdata){
				$modIndex = $i + $add;
				$_ecdata = $modIndex >= 0 ? $modPoly->num[$modIndex] : 0;
			}

			$totalCodeCount += $this->rsBlock->totalCount;
		}

		$data = array_fill(0, $totalCodeCount, null);
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

		for($row = 0; $row < $this->moduleCount; $row++){
			$html .= '<tr>';

			for($col = 0; $col < $this->moduleCount; $col++){
				$html .= '<td class="'.($this->isDark($row, $col) ? 'dark' : 'light').'"></td>';
			}

			$html .= '</tr>';
		}

		$html .= '</table>';

		return $html;
	}

}
