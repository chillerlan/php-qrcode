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

use chillerlan\QRCode\QRCode;

/**
 * Numeric mode: decimal digits 0 through 9
 */
class Number extends QRDataAbstract{

	/**
	 * @var int
	 */
	protected $datamode = QRCode::DATA_NUMBER;

	/**
	 * @var array
	 */
	protected $lengthBits = [10, 12, 14];

	/**
	 * @inheritdoc
	 */
	protected function write(string $data){
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
	 * @param string $string
	 *
	 * @return int
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	protected function parseInt(string $string):int {
		$num = 0;

		$len = strlen($string);
		for($i = 0; $i < $len; $i++){
			$c = ord($string[$i]);
			$ord0 = ord('0');

			if($ord0 <= $c && $c <= ord('9')){
				$c = $c - $ord0;
			}
			else{
				throw new QRCodeDataException('illegal char: '.$c);
			}

			$num = $num * 10 + $c;
		}

		return $num;
	}

}
