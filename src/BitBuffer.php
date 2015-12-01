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
	protected $buffer = [];

	/**
	 * @var int
	 */
	protected $length = 0;

	/**
	 * @return array
	 */
	public function getBuffer(){
		return $this->buffer;
	}

	/**
	 * @return int
	 */
	public function getLengthInBits(){
		return $this->length;
	}

	/**
	 * @return string
	 */
	public function __toString(){
		$buffer = '';

		for($i = 0; $i < $this->getLengthInBits(); $i++){
			$buffer .= $this->get($i) ? '1' : '0';
		}

		return $buffer;
	}

	/**
	 * @param $index
	 *
	 * @return bool
	 */
	public function get($index){
		return (($this->buffer[(int)floor($index / 8)] >> (7 - $index % 8))&1) === 1;
	}

	/**
	 * @param $num
	 * @param $length
	 */
	public function put($num, $length){
		for($i = 0; $i < $length; $i++){
			$this->putBit((($num >> ($length - $i - 1))&1) === 1);
		}
	}

	/**
	 * @param $bit
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
