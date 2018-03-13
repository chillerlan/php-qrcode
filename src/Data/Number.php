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

	const CHAR_MAP = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

	/**
	 * @inheritdoc
	 */
	protected $datamode = QRCode::DATA_NUMBER;

	/**
	 * @inheritdoc
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
			// @codeCoverageIgnoreStart
			elseif($this->strlen - $i === 2){
				$this->bitBuffer->put($this->parseInt(substr($data, $i, $i + 2)), 7);
			}
			// @codeCoverageIgnoreEnd

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

			if(!in_array($string[$i], $this::CHAR_MAP, true)){
				throw new QRCodeDataException('illegal char: "'.$string[$i].'" ['.$c.']');
			}

			$c = $c - ord('0');

			$num = $num * 10 + $c;
		}

		return $num;
	}

}
