<?php
/**
 * Class Kanji
 *
 * @filesource   Kanji.php
 * @created      25.11.2015
 * @package      codemasher\QRCode\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace codemasher\QRCode\Data;

use codemasher\QRCode\BitBuffer;
use codemasher\QRCode\QRCodeException;
use codemasher\QRCode\QRConst;

/**
 *
 */
class Kanji extends QRDataBase implements QRDataInterface{

	/**
	 * @var
	 */
	public $mode = QRConst::MODE_KANJI;

	/**
	 * @var array
	 */
	protected $lengthBits = [8, 10, 12];

	/**
	 * @param $buffer
	 *
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function write(BitBuffer &$buffer){

		$i = 0;
		while($i + 1 < $this->dataLength){
			$c = ((0xff&ord($this->data[$i])) << 8)|(0xff&ord($this->data[$i + 1]));

			if(0x8140 <= $c && $c <= 0x9FFC){
				$c -= 0x8140;
			}
			else if(0xE040 <= $c && $c <= 0xEBBF){
				$c -= 0xC140;
			}
			else{
				throw new QRCodeException('illegal char at '.($i + 1).' ('.$c.')');
			}

			$c = (($c >> 8)&0xff) * 0xC0 + ($c&0xff);
			$buffer->put($c, 13);
			$i += 2;
		}

		if($i < $this->dataLength){
			throw new QRCodeException('illegal char at '.($i + 1));
		}

	}

}
