<?php
/**
 *
 * @filesource   EightBitByte.php
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
 * Class EightBitByte
 */
class EightBitByte extends QRDataBase implements QRDataInterface{

	/**
	 * @var
	 */
	protected $mode = QRConst::MODE_8BIT_BYTE;

	/**
	 * @param $buffer
	 */
	public function write(BitBuffer &$buffer){
		$data = $this->getData();

		$len = strlen($data);
		for($i = 0; $i < $len; $i++){
			$buffer->put(ord($data[$i]), 8);
		}
	}

	/**
	 * @return int
	 */
	public function getLength(){
		return strlen($this->getData());
	}

}