<?php
/**
 * Class AlphaNum
 *
 * @filesource   AlphaNum.php
 * @created      25.11.2015
 * @package      chillerlan\QRCode\Data
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
class AlphaNum extends QRDataBase implements QRDataInterface{

	/**
	 * @var int
	 */
	public $mode = QRConst::MODE_ALPHANUM;

	/**
	 * @var array
	 */
	protected $lengthBits = [9, 11, 13];

	/**
	 * @param \chillerlan\QRCode\BitBuffer $buffer
	 */
	public function write(BitBuffer &$buffer){
		$i = 0;

		while($i + 1 < $this->dataLength){
			$buffer->put(Util::getCharCode($this->data[$i]) * 45 + Util::getCharCode($this->data[$i + 1]), 11);
			$i += 2;
		}

		if($i < $this->dataLength){
			$buffer->put(Util::getCharCode($this->data[$i]), 6);
		}

	}

}
