<?php
/**
 *
 * @filesource   Number.php
 * @created      26.11.2015
 * @package      codemasher\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace codemasher\QRCode\Data;

use codemasher\QRCode\BitBuffer;
use codemasher\QRCode\Data\QRDataBase;
use codemasher\QRCode\Data\QRDataInterface;
use codemasher\QRCode\QRCodeException;
use codemasher\QRCode\QRConst;

/**
 * Class Number
 */
class Number extends QRDataBase implements QRDataInterface{

	/**
	 * @var int
	 */
	public $mode = QRConst::MODE_NUMBER;

	/**
	 * @param $buffer
	 *
	 * @return $this
	 */
	public function write(BitBuffer &$buffer){

		$i = 0;
		$len = strlen($this->data);
		while($i + 2 < $len){
			$num = $this->parseInt(substr($this->data, $i, 3));
			$buffer->put($num, 10);
			$i += 3;
		}

		if($i < $len){

			if($len - $i === 1){
				$num = $this->parseInt(substr($this->data, $i, $i + 1));
				$buffer->put($num, 4);
			}
			else if($len - $i === 2){
				$num = $this->parseInt(substr($this->data, $i, $i + 2));
				$buffer->put($num, 7);
			}

		}

		return $this;
	}

	/**
	 * @param string $string
	 *
	 * @return int
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	protected function parseInt($string){
		$num = 0;

		$len = strlen($string);
		for($i = 0; $i < $len; $i++){
			$c = ord($string[$i]);

			$ord0 = ord('0');
			if(ord('0') <= $c && $c <= ord('9')){
				$c = $c - $ord0;
			}
			else{
				throw new QRCodeException('illegal char: '.$c);
			}

			$num = $num * 10 + $c;
		}

		return $num;
	}

}
