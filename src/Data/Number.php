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
	 * @var
	 */
	protected $mode = QRConst::MODE_NUMBER;

	/**
	 * @var \codemasher\QRCode\Util
	 */
	protected $util;

	/**
	 * @param $buffer
	 */
	public function write(BitBuffer &$buffer){
		$data = $this->getData();

		$i = 0;
		$len = strlen($data);
		while($i + 2 < $len){
			$num = $this->parseInt(substr($data, $i, 3));
			$buffer->put($num, 10);
			$i += 3;
		}

		if($i < $len){

			if($len - $i === 1){
				$num = $this->parseInt(substr($data, $i, $i + 1));
				$buffer->put($num, 4);
			}
			else if($len - $i === 2){
				$num = $this->parseInt(substr($data, $i, $i + 2));
				$buffer->put($num, 7);
			}
		}
	}

	/**
	 * @return int
	 */
	public function getLength(){
		return strlen($this->getData());
	}

	/**
	 * @param $s
	 *
	 * @return int
	 */
	protected function parseInt($s){
		$num = 0;

		for($i = 0; $i < strlen($s); $i++){
			$num = $num * 10 + $this->parseIntAt(ord($s[$i]));
		}

		return $num;
	}

	/**
	 * @param $c
	 *
	 * @return mixed
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	protected function parseIntAt($c){
		if($this->util->toCharCode('0') <= $c && $c <= $this->util->toCharCode('9')){
			return $c - $this->util->toCharCode('0');
		}

		throw new QRCodeException('illegal char: '.$c);
	}

}
