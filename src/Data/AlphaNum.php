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

use chillerlan\QRCode\{BitBuffer, QRConst};

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
	 *
	 * @return void
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
	 * @param string $chr
	 *
	 * @return int
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	private static function getCharCode(string $chr):int {
		$chr = ord($chr);

		switch(true){
			case ord('0') <= $chr && $chr <= ord('9'): return $chr - ord('0');
			case ord('A') <= $chr && $chr <= ord('Z'): return $chr - ord('A') + 10;
			default:
				foreach(self::CHAR_MAP as $i => $c){
					if(ord($c) === $chr){
						return $i;
					}
				}
		}

		throw new QRCodeDataException('illegal char: '.$chr);
	}

}
