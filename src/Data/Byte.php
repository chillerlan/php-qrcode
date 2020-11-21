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

use chillerlan\QRCode\Helpers\BitBuffer;
use chillerlan\QRCode\QRCode;

use function ord;

/**
 * Byte mode, ISO-8859-1 or UTF-8
 *
 * ISO/IEC 18004:2000 Section 8.3.4
 * ISO/IEC 18004:2000 Section 8.4.4
 */
final class Byte extends QRDataModeAbstract{

	protected array $lengthBits = [8, 16, 16];

	/**
	 * @inheritdoc
	 */
	public function getLengthInBits():int{
		return $this->getCharCount() * 8;
	}

	/**
	 * @inheritdoc
	 */
	public static function validateString(string $string):bool{
		return !empty($string);
	}

	/**
	 * @inheritdoc
	 */
	public function write(BitBuffer $bitBuffer, int $version):void{
		$len = $this->getCharCount();

		$bitBuffer
			->put(QRCode::DATA_BYTE, 4)
			->put($len, $this->getLengthBitsForVersion($version))
		;

		$i = 0;

		while($i < $len){
			$bitBuffer->put(ord($this->data[$i]), 8);
			$i++;
		}

	}

}
