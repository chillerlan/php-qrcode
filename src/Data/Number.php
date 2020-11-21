<?php
/**
 * Class Number
 *
 * @filesource   Number.php
 * @created      26.11.2015
 * @package      chillerlan\QRCode\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\Helpers\BitBuffer;
use chillerlan\QRCode\QRCode;

use function ceil, ord, sprintf, str_split, substr;

/**
 * Numeric mode: decimal digits 0 to 9
 *
 * ISO/IEC 18004:2000 Section 8.3.2
 * ISO/IEC 18004:2000 Section 8.4.2
 */
final class Number extends QRDataModeAbstract{

	/**
	 * @var int[]
	 */
	protected const CHAR_MAP_NUMBER = [
		'0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
	];

	protected array $lengthBits = [10, 12, 14];

	/**
	 * @inheritdoc
	 */
	public function getLengthInBits():int{
		return (int)ceil($this->getCharCount() * (10 / 3));
	}

	/**
	 * @inheritdoc
	 */
	public static function validateString(string $string):bool{

		foreach(str_split($string) as $chr){
			if(!isset(self::CHAR_MAP_NUMBER[$chr])){
				return false;
			}
		}

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function write(BitBuffer $bitBuffer, int $version):void{
		$len = $this->getCharCount();

		$bitBuffer
			->put(QRCode::DATA_NUMBER, 4)
			->put($len, $this->getLengthBitsForVersion($version))
		;

		$i = 0;

		// encode numeric triplets in 10 bits
		while($i + 2 < $len){
			$bitBuffer->put($this->parseInt(substr($this->data, $i, 3)), 10);
			$i += 3;
		}

		if($i < $len){

			// encode 2 remaining numbers in 7 bits
			if($len - $i === 2){
				$bitBuffer->put($this->parseInt(substr($this->data, $i, 2)), 7);
			}
			// encode one remaining number in 4 bits
			elseif($len - $i === 1){
				$bitBuffer->put($this->parseInt(substr($this->data, $i, 1)), 4);
			}

		}

	}

	/**
	 * get the code for the given numeric string
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException on an illegal character occurence
	 */
	protected function parseInt(string $string):int{
		$num = 0;

		foreach(str_split($string) as $chr){
			$c = ord($chr);

			if(!isset(self::CHAR_MAP_NUMBER[$chr])){
				throw new QRCodeDataException(sprintf('illegal char: "%s" [%d]', $chr, $c));
			}

			$c   = $c - 48; // ord('0')
			$num = $num * 10 + $c;
		}

		return $num;
	}

}
