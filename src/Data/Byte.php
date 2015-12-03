<?php
/**
 * Class Byte
 *
 * @filesource   Byte.php
 * @created      25.11.2015
 * @package      codemasher\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace codemasher\QRCode\Data;

use codemasher\QRCode\BitBuffer;
use codemasher\QRCode\QRConst;
use codemasher\QRCode\Data\QRDataBase;
use codemasher\QRCode\Data\QRDataInterface;


/**
 *
 */
class Byte extends QRDataBase implements QRDataInterface{

	/**
	 * @var
	 */
	public $mode = QRConst::MODE_BYTE;

	/**
	 * @var array
	 */
	protected $lengthBits = [8, 16, 16];

	/**
	 * @param $buffer
	 */
	public function write(BitBuffer &$buffer){
		for($i = 0; $i < $this->dataLength; $i++){
			$buffer->put(ord($this->data[$i]), 8);
		}
	}

}
