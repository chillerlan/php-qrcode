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

/**
 *
 */
class AlphaNum extends QRDataAbstract{

	const CHAR_MAP = [
		36 => ' ',
		37 => '$',
		38 => '%',
		39 => '*',
		40 => '+',
		41 => '-',
		42 => '.',
		43 => '/',
		44 => ':',
	];

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
			$buffer->put($this->getCharCode($this->data[$i]) * 45 + $this->getCharCode($this->data[$i + 1]), 11);
			$i += 2;
		}

		if($i < $this->dataLength){
			$buffer->put($this->getCharCode($this->data[$i]), 6);
		}

	}

	/**
	 * @param string $c
	 *
	 * @return int
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	private static function getCharCode($c){
		$c = ord($c);

		switch(true){
			case ord('0') <= $c && $c <= ord('9'): return $c - ord('0');
			case ord('A') <= $c && $c <= ord('Z'): return $c - ord('A') + 10;
			default:
				foreach(self::CHAR_MAP as $i => $char){
					if(ord($char) === $c){
						return $i;
					}
				}
		}

		throw new QRCodeDataException('illegal char: '.$c);
	}

}
