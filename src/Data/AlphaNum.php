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
	public $mode = QRConst::MODE_ALPHANUM;

	/**
	 * @param $buffer
	 */
	public function write(BitBuffer &$buffer){

		$i = 0;
		$len = strlen($this->data);
		while($i + 1 < $len){
			$buffer->put($this->getCode(ord($this->data[$i])) * 45 + $this->getCode(ord($this->data[$i + 1])), 11);
			$i += 2;
		}

		if($i < $len){
			$buffer->put($this->getCode(ord($this->data[$i])), 6);
		}
	}

	/**
	 * @param $c
	 *
	 * @return int
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	protected function getCode($c){

		if(ord('0') <= $c && $c <= ord('9')){
			return $c - ord('0');
		}
		else if(ord('A') <= $c && $c <= ord('Z')){
			return $c - ord('A') + 10;
		}
		else{
			switch($c){
				case ord(' '): return 36;
				case ord('$'): return 37;
				case ord('%'): return 38;
				case ord('*'): return 39;
				case ord('+'): return 40;
				case ord('-'): return 41;
				case ord('.'): return 42;
				case ord('/'): return 43;
				case ord(':'): return 44;
				default :
					throw new QRCodeException('illegal char: '.$c);
			}
		}

	}

}
