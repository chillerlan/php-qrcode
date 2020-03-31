<?php
/**
 * Class BitBuffer
 *
 * @filesource   BitBuffer.php
 * @created      25.11.2015
 * @package      chillerlan\QRCode\Helpers
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Helpers;

use function count, floor;

final class BitBuffer{

	/** @var int[] */
	public array $buffer = [];

	public int $length = 0;

	/**
	 *
	 */
	public function clear():BitBuffer{
		$this->buffer = [];
		$this->length = 0;

		return $this;
	}

	/**
	 *
	 */
	public function put(int $num, int $length):BitBuffer{

		for($i = 0; $i < $length; $i++){
			$this->putBit((($num >> ($length - $i - 1)) & 1) === 1);
		}

		return $this;
	}

	/**
	 *
	 */
	public function putBit(bool $bit):BitBuffer{
		$bufIndex = floor($this->length / 8);

		if(count($this->buffer) <= $bufIndex){
			$this->buffer[] = 0;
		}

		if($bit === true){
			$this->buffer[(int)$bufIndex] |= (0x80 >> ($this->length % 8));
		}

		$this->length++;

		return $this;
	}

}
