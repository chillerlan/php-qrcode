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

use chillerlan\QRCode\QRCode;

use function ord, sprintf, str_split, substr;

/**
 * Numeric mode: decimal digits 0 to 9
 *
 * ISO/IEC 18004:2000 Section 8.3.2
 * ISO/IEC 18004:2000 Section 8.4.2
 */
final class Number extends QRDataAbstract{

	protected int $datamode = QRCode::DATA_NUMBER;

	protected array $lengthBits = [10, 12, 14];

	/**
	 * @inheritdoc
	 */
	protected function write(string $data):void{
		$i = 0;

		while($i + 2 < $this->strlen){
			$this->bitBuffer->put($this->parseInt(substr($data, $i, 3)), 10);
			$i += 3;
		}

		if($i < $this->strlen){

			if($this->strlen - $i === 1){
				$this->bitBuffer->put($this->parseInt(substr($data, $i, $i + 1)), 4);
			}
			elseif($this->strlen - $i === 2){
				$this->bitBuffer->put($this->parseInt(substr($data, $i, $i + 2)), 7);
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

			if(!isset($this::CHAR_MAP_NUMBER[$chr])){
				throw new QRCodeDataException(sprintf('illegal char: "%s" [%d]', $chr, $c));
			}

			$c   = $c - 48; // ord('0')
			$num = $num * 10 + $c;
		}

		return $num;
	}

}
