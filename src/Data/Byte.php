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

use chillerlan\QRCode\BitBuffer;
use chillerlan\QRCode\QRConst;

/**
 *
 */
class Byte extends QRDataBase implements QRDataInterface{

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
	 */
	public function write(BitBuffer &$buffer){
		for($i = 0; $i < $this->dataLength; $i++){
			$buffer->put(ord($this->data[$i]), 8);
		}
	}

}
