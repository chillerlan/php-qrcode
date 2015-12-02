<?php
/**
 *
 * @filesource   AlphaNum.php
 * @created      25.11.2015
 * @package      codemasher\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace codemasher\QRCode\Data;

use codemasher\QRCode\BitBuffer;
use codemasher\QRCode\Data;
use codemasher\QRCode\Data\QRDataBase;
use codemasher\QRCode\Data\QRDataInterface;
use codemasher\QRCode\QRCodeException;
use codemasher\QRCode\QRConst;

/**
 * Class AlphaNum
 */
class AlphaNum extends QRDataBase implements QRDataInterface{

	/**
	 * @var
	 */
	protected $mode = QRConst::MODE_ALPHA_NUM;

	/**
	 * @param $buffer
	 */
	public function write(BitBuffer &$buffer){
		$data = $this->getData();

		$i = 0;
		$len = strlen($data);
		while($i + 1 < $len){
			$buffer->put($this->getCode(ord($data[$i])) * 45 + $this->getCode(ord($data[$i + 1])), 11);
			$i += 2;
		}

		if($i < $len){
			$buffer->put($this->getCode(ord($data[$i])), 6);
		}
	}

	/**
	 * @return int
	 */
	public function getLength(){
		return strlen($this->getData());
	}

	/**
	 * @param $c
	 *
	 * @return int
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	protected function getCode($c){

		if($this->util->toCharCode('0') <= $c && $c <= $this->util->toCharCode('9')){
			return $c - $this->util->toCharCode('0');
		}
		else if($this->util->toCharCode('A') <= $c && $c <= $this->util->toCharCode('Z')){
			return $c - $this->util->toCharCode('A') + 10;
		}
		else{
			switch($c){
				case $this->util->toCharCode(' '): return 36;
				case $this->util->toCharCode('$'): return 37;
				case $this->util->toCharCode('%'): return 38;
				case $this->util->toCharCode('*'): return 39;
				case $this->util->toCharCode('+'): return 40;
				case $this->util->toCharCode('-'): return 41;
				case $this->util->toCharCode('.'): return 42;
				case $this->util->toCharCode('/'): return 43;
				case $this->util->toCharCode(':'): return 44;
				default :
					throw new QRCodeException('illegal char: '.$c);
			}
		}

	}

}
