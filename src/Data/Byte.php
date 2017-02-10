<?php
/**
 * Class Byte
 *
 * @filesource   Byte.php
 * @created      25.11.2015
 * @package      chillerlan\QRCode\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\{BitBuffer, QRConst};

/**
 *
 */
class Byte extends QRDataAbstract{

	/**
	 * @var int
	 */
	public $mode = QRConst::MODE_BYTE;

	/**
	 * @var array
	 */
	protected $lengthBits = [8, 16, 16];

	/**
	 * @param \chillerlan\QRCode\BitBuffer $buffer
	 *
	 * @return void
	 */
	public function write(BitBuffer &$buffer){
		$i = 0;
		while($i < $this->dataLength){
			$buffer->put(ord($this->data[$i]), 8);
			$i++;
		}
	}

}
