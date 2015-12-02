<?php
/**
 *
 * @filesource   QRCode.php
 * @created      26.11.2015
 * @package      codemasher\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace codemasher\QRCode;

use codemasher\QRCode\Data\AlphaNum;
use codemasher\QRCode\Data\EightBitByte;
use codemasher\QRCode\Data\Kanji;
use codemasher\QRCode\Data\Number;
use codemasher\QRCode\Data\QRDataInterface;
use codemasher\QRCode\QRConst;

/**
 * Class QRCode
 */
class QRCode{

	const QR_PAD0 = 0xEC;
	const QR_PAD1 = 0x11;

	/**
	 * @var int
	 */
	protected $typeNumber;

	/**
	 * @var
	 */
	protected $modules;

	/**
	 * @var
	 */
	protected $moduleCount;

	/**
	 * @var int
	 */
	protected $errorCorrectLevel;

	/**
	 * @var array
	 */
	protected $qrDataList = [];

	/**
	 * @var array
	 */
	protected $rsBlockList = [];

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
	 * @return int
	 */
	public function getTypeNumber(){
		return $this->typeNumber;
	}

	/**
	 * @param int $typeNumber
	 */
	public function setTypeNumber($typeNumber){
		$this->typeNumber = $typeNumber;
	}

	/**
	 * @return int
	 */
	public function getErrorCorrectLevel(){
		return $this->errorCorrectLevel;
	}

	/**
	 * @param int $errorCorrectLevel
	 */
	public function setErrorCorrectLevel($errorCorrectLevel){
		$this->errorCorrectLevel = $errorCorrectLevel;
	}

	/**
	 * @param string $data
	 * @param int    $mode
	 *
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function addData($data, $mode = 0){
		if($mode === 0){
			$mode = $this->util->getMode($data);
		}

		switch($mode){
			case QRConst::MODE_NUMBER   :
				$this->addDataImpl(new Number($data));
				break;
			case QRConst::MODE_ALPHA_NUM:
				$this->addDataImpl(new AlphaNum($data));
				break;
			case QRConst::MODE_8BIT_BYTE:
				$this->addDataImpl(new EightBitByte($data));
				break;
			case QRConst::MODE_KANJI    :
				$this->addDataImpl(new Kanji($data));
				break;
			default :
				throw new QRCodeException('mode: '.$mode);
		}
	}

	/**
	 *
	 */
	public function clearData(){
		$this->qrDataList = [];
	}

	/**
	 * @param \codemasher\QRCode\Data\QRDataInterface $qrData
	 *
	 * @return $this
	 */
	public function addDataImpl(QRDataInterface &$qrData){
		$this->qrDataList[] = $qrData;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getDataCount(){
		return count($this->qrDataList);
	}

	/**
	 * @param int $index
	 *
	 * @return \codemasher\QRCode\Data\QRDataInterface
	 */
	public function getData($index){
		return $this->qrDataList[$index];
	}

	/**
	 * @param int $row
	 * @param int $col
	 *
	 * @return mixed
	 */
	public function isDark($row, $col){
		if($this->modules[$row][$col] !== null){
			return $this->modules[$row][$col];
		}
		else{
			return false;
		}
	}

	/**
	 * @return mixed
	 */
	public function getModuleCount(){
		return $this->moduleCount;
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
	public function getBestMaskPattern(){
		$minLostPoint = 0;
		$pattern = 0;

		for($i = 0; $i < 8; $i++){
			$this->makeImpl(true, $i);
			$lostPoint = $this->util->getLostPoint($this);

			if($i == 0 || $minLostPoint > $lostPoint){
				$minLostPoint = $lostPoint;
				$pattern = $i;
			}
		}

		return $pattern;
	}

	/**
	 * @param int $length
	 *
	 * @return array
	 */
	public function createNullArray($length){
		$nullArray = [];
		for($i = 0; $i < $length; $i++){
			$nullArray[] = null;
		}

		return $nullArray;
	}

	/**
	 * @param $test
	 * @param $maskPattern
	 *
	 * @return $this
	 */
	public function makeImpl($test, $maskPattern){
		$this->moduleCount = $this->typeNumber * 4 + 17;
		$this->modules = [];

		for($i = 0; $i < $this->moduleCount; $i++){
			$this->modules[] = $this->createNullArray($this->moduleCount);
		}

		$this->setupPositionProbePattern(0, 0)
		     ->setupPositionProbePattern($this->moduleCount - 7, 0)
		     ->setupPositionProbePattern(0, $this->moduleCount - 7)
		     ->setupPositionAdjustPattern()
		     ->setupTimingPattern()
		     ->setupTypeInfo($test, $maskPattern);

		if($this->typeNumber >= 7){
			$this->setupTypeNumber($test);
		}

		$data = $this->createData($this->typeNumber, $this->errorCorrectLevel);

		$this->mapData($data, $maskPattern);

		return $this;
	}

	/**
	 * @param $data
	 * @param $maskPattern
	 *
	 * @return $this
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function mapData(&$data, $maskPattern){
		$inc = -1;
		$row = $this->moduleCount - 1;
		$bitIndex = 7;
		$byteIndex = 0;

		for($col = $this->moduleCount - 1; $col > 0; $col -= 2){

			if($col === 6){
				$col--;
			}

			while(true){

				for($c = 0; $c < 2; $c++){

					if($this->modules[$row][$col - $c] === null){

						$dark = false;

						if($byteIndex < count($data)){
							$dark = ((($data[$byteIndex] >> $bitIndex) & 1) == 1);
						}

						$mask = $this->util->getMask($maskPattern, $row, $col - $c);

						if($mask){
							$dark = !$dark;
						}

						$this->modules[$row][$col - $c] = $dark;
						$bitIndex--;

						if($bitIndex == -1){
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
	 * @return $this
	 */
	public function setupPositionAdjustPattern(){
		$pos = $this->util->getPatternPosition($this->typeNumber);

		$posCount = count($pos);
		for($i = 0; $i < $posCount; $i++){
			for($j = 0; $j < $posCount; $j++){

				$row = $pos[$i];
				$col = $pos[$j];

				if($this->modules[$row][$col] !== null){
					continue;
				}

				for($r = -2; $r <= 2; $r++){
					for($c = -2; $c <= 2; $c++){

						if($r == -2 || $r == 2 || $c == -2 || $c == 2
							|| ($r == 0 && $c == 0)
						){
							$this->modules[$row + $r][$col + $c] = true;
						}
						else{
							$this->modules[$row + $r][$col + $c] = false;
						}
					}
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
	public function setupPositionProbePattern($row, $col){

		for($r = -1; $r <= 7; $r++){
			for($c = -1; $c <= 7; $c++){

				if($row + $r <= -1 || $this->moduleCount <= $row + $r
					|| $col + $c <= -1 || $this->moduleCount <= $col + $c
				){
					continue;
				}

				if((0 <= $r && $r <= 6 && ($c == 0 || $c == 6))
					|| (0 <= $c && $c <= 6 && ($r == 0 || $r == 6))
					|| (2 <= $r && $r <= 4 && 2 <= $c && $c <= 4)
				){
					$this->modules[$row + $r][$col + $c] = true;
				}
				else{
					$this->modules[$row + $r][$col + $c] = false;
				}
			}
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	public function setupTimingPattern(){

		for($r = 8; $r < $this->moduleCount - 8; $r++){
			if($this->modules[$r][6] !== null){
				continue;
			}
			$this->modules[$r][6] = ($r % 2 == 0);
		}

		for($c = 8; $c < $this->moduleCount - 8; $c++){
			if($this->modules[6][$c] !== null){
				continue;
			}
			$this->modules[6][$c] = ($c % 2 == 0);
		}

		return $this;
	}

	/**
	 * @param $test
	 *
	 * @return $this
	 */
	public function setupTypeNumber($test){
		$bits = $this->util->getBCHTypeNumber($this->typeNumber);

		for($i = 0; $i < 18; $i++){
			$this->modules[(int)floor($i / 3)][$i % 3 + $this->moduleCount - 8 - 3] = !$test && (($bits >> $i) & 1) === 1;
		}

		for($i = 0; $i < 18; $i++){
			$this->modules[$i % 3 + $this->moduleCount - 8 - 3][(int)floor($i / 3)] = !$test && (($bits >> $i) & 1) === 1;
		}

		return $this;
	}

	/**
	 * @param $test
	 * @param $maskPattern
	 *
	 * @return $this
	 */
	public function setupTypeInfo($test, $maskPattern){

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
		}

		for($i = 0; $i < 15; $i++){

			$mod = !$test && (($bits >> $i) & 1) === 1;

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

		return $this;
	}

	/**
	 * @param $typeNumber
	 * @param $errorCorrectLevel
	 *
	 * @return array
	 * @throws \codemasher\QRCode\QRCodeException
	 * @todo: slooooow
	 *
	 */
	public function createData($typeNumber, $errorCorrectLevel){
		$this->bitBuffer->reset();

		$count = count($this->qrDataList);
		for($i = 0; $i < $count; $i++){
			/** @var \codemasher\QRCode\Data\QRDataInterface $data */
			$data = $this->qrDataList[$i];

			$this->bitBuffer->put($data->getMode(), 4);
			$this->bitBuffer->put($data->getLength(), $data->getLengthInBits($typeNumber));
			$data->write($this->bitBuffer);
		}

		$this->rsBlockList = $this->rsBlock->getRSBlocks($typeNumber, $errorCorrectLevel);
		$totalDataCount = 0;

		$count = count($this->rsBlockList);
		for($i = 0; $i < $count; $i++){
			$this->rsBlock->setCount($this->rsBlockList[$i][0], $this->rsBlockList[$i][1]);

			$totalDataCount += $this->rsBlock->getDataCount();
		}

		if($this->bitBuffer->getLengthInBits() > $totalDataCount * 8){
			throw new QRCodeException('code length overflow. ('.$this->bitBuffer->getLengthInBits().'>'.($totalDataCount * 8).')');
		}

		// end code.
		if($this->bitBuffer->getLengthInBits() + 4 <= $totalDataCount * 8){
			$this->bitBuffer->put(0, 4);
		}

		// padding
		while($this->bitBuffer->getLengthInBits() % 8 != 0){
			$this->bitBuffer->putBit(false);
		}

		// padding
		while(true){

			if($this->bitBuffer->getLengthInBits() >= $totalDataCount * 8){
				break;
			}
			$this->bitBuffer->put(self::QR_PAD0, 8);

			if($this->bitBuffer->getLengthInBits() >= $totalDataCount * 8){
				break;
			}

			$this->bitBuffer->put(self::QR_PAD1, 8);
		}

		return $this->createBytes();
	}

	/**
	 * @return array
	 */
	public function createBytes(){
		$offset = $maxDcCount = $maxEcCount = 0;
		$dcdata = $ecdata = $this->createNullArray(count($this->rsBlockList));
		$rsBlockCount = count($this->rsBlockList);

		for($r = 0; $r < $rsBlockCount; $r++){
			$this->rsBlock->setCount($this->rsBlockList[$r][0], $this->rsBlockList[$r][1]);

			$dcCount = $this->rsBlock->getDataCount();
			$ecCount = $this->rsBlock->getTotalCount() - $dcCount;

			$maxDcCount = max($maxDcCount, $dcCount);
			$maxEcCount = max($maxEcCount, $ecCount);

			$dcdata[$r] = $this->createNullArray($dcCount);
			$dcdataCount = count($dcdata[$r]);
			for($i = 0; $i < $dcdataCount; $i++){
				$bdata = $this->bitBuffer->getBuffer();
				$dcdata[$r][$i] = 0xff & $bdata[$i + $offset];
			}
			$offset += $dcCount;

			$rsPoly = $this->util->getErrorCorrectPolynomial($ecCount);
			$rawPoly = new Polynomial($dcdata[$r], $rsPoly->getLength() - 1);

			$modPoly = $rawPoly->mod($rsPoly);
			$ecdata[$r] = $this->createNullArray($rsPoly->getLength() - 1);
			$ecdataCount = count($ecdata[$r]);
			for($i = 0; $i < $ecdataCount; $i++){
				$modIndex = $i + $modPoly->getLength() - count($ecdata[$r]);
				$ecdata[$r][$i] = ($modIndex >= 0) ? $modPoly->getNum($modIndex) : 0;
			}
		}

		$totalCodeCount = 0;
		for($i = 0; $i < $rsBlockCount; $i++){
			$this->rsBlock->setCount($this->rsBlockList[$i][0], $this->rsBlockList[$i][1]);

			$totalCodeCount += $this->rsBlock->getTotalCount();
		}

		$data = $this->createNullArray($totalCodeCount);
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
	public function getMinimumQRCode($data, $errorCorrectLevel){

		$mode = $this->util->getMode($data);

		$qr = new QRCode();
		$qr->setErrorCorrectLevel($errorCorrectLevel);
		$qr->addData($data, $mode);

		$qrData = $qr->getData(0);
		$length = $qrData->getLength();

		for($typeNumber = 1; $typeNumber <= 10; $typeNumber++){
			if($length <= $this->util->getMaxLength($typeNumber, $mode, $errorCorrectLevel)){
				$qr->setTypeNumber($typeNumber);
				break;
			}
		}

		$qr->make();

		return $qr;
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

		$image_size = $this->getModuleCount() * $size + $margin * 2;

		$image = imagecreatetruecolor($image_size, $image_size);

		// fg/bg EC
		if($fg < 0 || $fg > 0xFFFFFF){
			$fg = 0x0;
		}
		if($bg < 0 || $bg > 0xFFFFFF){
			$bg = 0xFFFFFF;
		}

		// convert hexadecimal RGB to arrays for imagecolorallocate
		$fgrgb = $this->hex2rgb($fg);
		$bgrgb = $this->hex2rgb($bg);

		// replace $black and $white with $fgc and $bgc
		$fgc = imagecolorallocate($image, $fgrgb['r'], $fgrgb['g'], $fgrgb['b']);
		$bgc = imagecolorallocate($image, $bgrgb['r'], $bgrgb['g'], $bgrgb['b']);
		if($bgtrans){
			imagecolortransparent($image, $bgc);
		}

		// update $white to $bgc
		imagefilledrectangle($image, 0, 0, $image_size, $image_size, $bgc);

		$count = $this->getModuleCount();
		for($r = 0; $r < $count; $r++){
			for($c = 0; $c < $count; $c++){
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
		$count = $this->getModuleCount();

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
