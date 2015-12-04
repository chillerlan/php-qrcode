<?php
/**
 *
 * @filesource   BitBuffer.php
 * @created      25.11.2015
 * @package      codemasher\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace codemasher\QRCode;

/**
 * Class BitBuffer
 */
class BitBuffer{

	/**
	 * @var array
	 */
	public $buffer = [];

	/**
	 * @var int
	 */
	public $length = 0;

	/**
	 * @return string
	 */
/*	public function __toString(){
		$buffer = '';

		for($i = 0; $i < $this->length; $i++){
			$buffer .= (string)(int)(($this->buffer[(int)floor($i / 8)] >> (7 - $i % 8))&1) === 1;
		}

		return $buffer;
	}
*/
	/**
	 * @param $num
	 * @param $length
	 *
	 * @return $this
	 */
	public function put($num, $length){

		for($i = 0; $i < $length; $i++){
			$this->putBit((($num >> ($length - $i - 1))&1) === 1);
		}

		return $this;
	}

	/**
	 * @param $bit
	 *
	 * @return $this
	 */
	public function putBit($bit){
		$bufIndex = floor($this->length / 8);

		if(count($this->buffer) <= $bufIndex){
			$this->buffer[] = 0;
		}

		if($bit){
			$this->buffer[(int)$bufIndex] |= (0x80 >> ($this->length % 8));
		}

		$this->length++;

		return $this;
	}

}
