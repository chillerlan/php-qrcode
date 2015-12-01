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
use codemasher\QRCode\QRConst;

/**
 * Class QRCode
 */
class QRCode{

	const QR_PAD0 = 0xEC;
	const QR_PAD1 = 0x11;

	protected $typeNumber;

	protected $modules;

	protected $moduleCount;

	protected $errorCorrectLevel;

	protected $qrDataList;

	protected $util;

	protected $rs_block;

	public function __construct(){
		$this->typeNumber = 1;
		$this->errorCorrectLevel = QRConst::ERROR_CORRECT_LEVEL_H;
		$this->qrDataList = [];
		$this->util = new Util;
		$this->rs_block = new RSBlock;
	}

	public function getTypeNumber(){
		return $this->typeNumber;
	}

	public function setTypeNumber($typeNumber){
		$this->typeNumber = $typeNumber;
	}

	public function getErrorCorrectLevel(){
		return $this->errorCorrectLevel;
	}

	public function setErrorCorrectLevel($errorCorrectLevel){
		$this->errorCorrectLevel = $errorCorrectLevel;
	}

	public function addData($data, $mode = 0){

		if($mode == 0){
			$mode = $this->util->getMode($data);
		}

		switch($mode){

			case QRConst::MODE_NUMBER :
				$this->addDataImpl(new Number($data));
				break;

			case QRConst::MODE_ALPHA_NUM :
				$this->addDataImpl(new AlphaNum($data));
				break;

			case QRConst::MODE_8BIT_BYTE :
				$this->addDataImpl(new EightBitByte($data));
				break;

			case QRConst::MODE_KANJI :
				$this->addDataImpl(new Kanji($data));
				break;

			default :
				trigger_error("mode:$mode", E_USER_ERROR);
		}
	}

	public function clearData(){
		$this->qrDataList = [];
	}

	public function addDataImpl(&$qrData){
		$this->qrDataList[] = $qrData;
	}

	public function getDataCount(){
		return count($this->qrDataList);
	}

	public function getData($index){
		return $this->qrDataList[$index];
	}

	public function isDark($row, $col){
		if($this->modules[$row][$col] !== null){
			return $this->modules[$row][$col];
		}
		else{
			return false;
		}
	}

	public function getModuleCount(){
		return $this->moduleCount;
	}

	// used for converting fg/bg colors (e.g. #0000ff = 0x0000FF)
	// added 2015.07.27 ~ DoktorJ
	public function hex2rgb($hex = 0x0){
		return [
			'r' => floor($hex / 65536),
			'g' => floor($hex / 256) % 256,
			'b' => $hex % 256,
		];
	}

	public function make(){
		$this->makeImpl(false, $this->getBestMaskPattern());
	}

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

	public function createNullArray($length){
		$nullArray = [];
		for($i = 0; $i < $length; $i++){
			$nullArray[] = null;
		}
		return $nullArray;
	}

	public function makeImpl($test, $maskPattern){

		$this->moduleCount = $this->typeNumber * 4 + 17;

		$this->modules = [];
		for($i = 0; $i < $this->moduleCount; $i++){
			$this->modules[] = $this->createNullArray($this->moduleCount);
		}

		$this->setupPositionProbePattern(0, 0);
		$this->setupPositionProbePattern($this->moduleCount - 7, 0);
		$this->setupPositionProbePattern(0, $this->moduleCount - 7);

		$this->setupPositionAdjustPattern();
		$this->setupTimingPattern();

		$this->setupTypeInfo($test, $maskPattern);

		if($this->typeNumber >= 7){
			$this->setupTypeNumber($test);
		}

		$dataArray = $this->qrDataList;

		$data = $this->createData($this->typeNumber, $this->errorCorrectLevel, $dataArray);

		$this->mapData($data, $maskPattern);
	}

	public function mapData(&$data, $maskPattern){

		$inc = -1;
		$row = $this->moduleCount - 1;
		$bitIndex = 7;
		$byteIndex = 0;

		for($col = $this->moduleCount - 1; $col > 0; $col -= 2){

			if($col == 6){
				$col--;
			}

			while(true){

				for($c = 0; $c < 2; $c++){

					if($this->modules[$row][$col - $c] === null){

						$dark = false;

						if($byteIndex < count($data)){
							$dark = ((($data[$byteIndex] >> $bitIndex)&1) == 1);
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
	}

	public function setupPositionAdjustPattern(){

		$pos = $this->util->getPatternPosition($this->typeNumber);

		for($i = 0; $i < count($pos); $i++){

			for($j = 0; $j < count($pos); $j++){

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
	}

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
	}

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
	}

	public function setupTypeNumber($test){

		$bits = $this->util->getBCHTypeNumber($this->typeNumber);

		for($i = 0; $i < 18; $i++){
			$mod = (!$test && (($bits >> $i)&1) == 1);
			$this->modules[(int)floor($i / 3)][$i % 3 + $this->moduleCount - 8 - 3] = $mod;
		}

		for($i = 0; $i < 18; $i++){
			$mod = (!$test && (($bits >> $i)&1) == 1);
			$this->modules[$i % 3 + $this->moduleCount - 8 - 3][(int)floor($i / 3)] = $mod;
		}
	}

	public function setupTypeInfo($test, $maskPattern){

		$data = ($this->errorCorrectLevel << 3)|$maskPattern;
		$bits = $this->util->getBCHTypeInfo($data);

		for($i = 0; $i < 15; $i++){

			$mod = (!$test && (($bits >> $i)&1) == 1);

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

			$mod = (!$test && (($bits >> $i)&1) == 1);

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
	}

	public function createData($typeNumber, $errorCorrectLevel, $dataArray){

		$rsBlocks = $this->rs_block->getRSBlocks($typeNumber, $errorCorrectLevel);

		$buffer = new BitBuffer;

		for($i = 0; $i < count($dataArray); $i++){
			$data = $dataArray[$i];
			$buffer->put($data->getMode(), 4);
			$buffer->put($data->getLength(), $data->getLengthInBits($typeNumber));
			$data->write($buffer);
		}

		$totalDataCount = 0;
		for($i = 0; $i < count($rsBlocks); $i++){
			$totalDataCount += $rsBlocks[$i]->getDataCount();
		}

		if($buffer->getLengthInBits() > $totalDataCount * 8){
			trigger_error("code length overflow. ("
				.$buffer->getLengthInBits()
				.">"
				.$totalDataCount * 8
				.")", E_USER_ERROR);
		}

		// end code.
		if($buffer->getLengthInBits() + 4 <= $totalDataCount * 8){
			$buffer->put(0, 4);
		}

		// padding
		while($buffer->getLengthInBits() % 8 != 0){
			$buffer->putBit(false);
		}

		// padding
		while(true){

			if($buffer->getLengthInBits() >= $totalDataCount * 8){
				break;
			}
			$buffer->put(self::QR_PAD0, 8);

			if($buffer->getLengthInBits() >= $totalDataCount * 8){
				break;
			}
			$buffer->put(self::QR_PAD1, 8);
		}

		return $this->createBytes($buffer, $rsBlocks);
	}

	public function createBytes(&$buffer, &$rsBlocks){

		$offset = 0;

		$maxDcCount = 0;
		$maxEcCount = 0;

		$dcdata = $this->createNullArray(count($rsBlocks));
		$ecdata = $this->createNullArray(count($rsBlocks));

		for($r = 0; $r < count($rsBlocks); $r++){

			$dcCount = $rsBlocks[$r]->getDataCount();
			$ecCount = $rsBlocks[$r]->getTotalCount() - $dcCount;

			$maxDcCount = max($maxDcCount, $dcCount);
			$maxEcCount = max($maxEcCount, $ecCount);

			$dcdata[$r] = $this->createNullArray($dcCount);
			for($i = 0; $i < count($dcdata[$r]); $i++){
				$bdata = $buffer->getBuffer();
				$dcdata[$r][$i] = 0xff&$bdata[$i + $offset];
			}
			$offset += $dcCount;

			$rsPoly = $this->util->getErrorCorrectPolynomial($ecCount);
			$rawPoly = new Polynomial($dcdata[$r], $rsPoly->getLength() - 1);

			$modPoly = $rawPoly->mod($rsPoly);
			$ecdata[$r] = $this->createNullArray($rsPoly->getLength() - 1);
			for($i = 0; $i < count($ecdata[$r]); $i++){
				$modIndex = $i + $modPoly->getLength() - count($ecdata[$r]);
				$ecdata[$r][$i] = ($modIndex >= 0) ? $modPoly->get($modIndex) : 0;
			}
		}

		$totalCodeCount = 0;
		for($i = 0; $i < count($rsBlocks); $i++){
			$totalCodeCount += $rsBlocks[$i]->getTotalCount();
		}

		$data = $this->createNullArray($totalCodeCount);

		$index = 0;

		for($i = 0; $i < $maxDcCount; $i++){
			for($r = 0; $r < count($rsBlocks); $r++){
				if($i < count($dcdata[$r])){
					$data[$index++] = $dcdata[$r][$i];
				}
			}
		}

		for($i = 0; $i < $maxEcCount; $i++){
			for($r = 0; $r < count($rsBlocks); $r++){
				if($i < count($ecdata[$r])){
					$data[$index++] = $ecdata[$r][$i];
				}
			}
		}

		return $data;
	}

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

	// added $fg (foreground), $bg (background), and $bgtrans (use transparent bg) parameters
	// also added some simple error checking on parameters
	// updated 2015.07.27 ~ DoktorJ
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

		for($r = 0; $r < $this->getModuleCount(); $r++){
			for($c = 0; $c < $this->getModuleCount(); $c++){
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

	public function printHTML($size = "2px"){

		$style = "border-style:none;border-collapse:collapse;margin:0px;padding:0px;";

		print("<table style='$style'>");

		for($r = 0; $r < $this->getModuleCount(); $r++){

			print("<tr style='$style'>");

			for($c = 0; $c < $this->getModuleCount(); $c++){
				$color = $this->isDark($r, $c) ? "#000000" : "#ffffff";
				print("<td style='$style;width:$size;height:$size;background-color:$color'></td>");
			}

			print("</tr>");
		}

		print("</table>");
	}
}
