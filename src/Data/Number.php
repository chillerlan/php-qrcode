<?php
/**
 * Class Number
 *
 * @filesource   Number.php
 * @created      26.11.2015
 * @package      QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\BitBuffer;
use chillerlan\QRCode\QRConst;
use chillerlan\QRCode\Util;

/**
 *
 */
class Number extends QRDataBase implements QRDataInterface{

	/**
	 * @var int
	 */
	public $mode = QRConst::MODE_NUMBER;

	/**
	 * @var array
	 */
	protected $lengthBits = [10, 12, 14];

	/**
	 * @param \chillerlan\QRCode\BitBuffer $buffer
	 */
	public function write(BitBuffer &$buffer){
		$i = 0;

		while($i + 2 < $this->dataLength){
			$buffer->put(Util::parseInt(substr($this->data, $i, 3)), 10);
			$i += 3;
		}

		if($i < $this->dataLength){

			if($this->dataLength - $i === 1){
				$buffer->put(Util::parseInt(substr($this->data, $i, $i + 1)), 4);
			}
			else if($this->dataLength - $i === 2){
				$buffer->put(Util::parseInt(substr($this->data, $i, $i + 2)), 7);
			}

		}

	}

}
