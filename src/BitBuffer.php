<?php
/**
 * Class BitBuffer
 *
 * @filesource   BitBuffer.php
 * @created      25.11.2015
 * @package      chillerlan\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode;

/**
 *
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
	 *
	 */
	public function clear(){
		$this->buffer = [];
		$this->length = 0;
	}

	/**
	 * @param int $num
	 * @param int $length
	 */
	public function put($num, $length){

		$i = 0;
		while($i < $length){
			$this->putBit(($num >> ($length - $i - 1))&1 === 1); $i++;
		}

	}

	/**
	 * @param bool $bit
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
	}

}
