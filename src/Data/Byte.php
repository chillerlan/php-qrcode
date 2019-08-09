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

use chillerlan\QRCode\QRCode;

use function ord;

/**
 * Byte mode, ISO-8859-1 or UTF-8
 */
class Byte extends QRDataAbstract{

	protected int $datamode = QRCode::DATA_BYTE;

	protected array $lengthBits = [8, 16, 16];

	/**
	 * @inheritdoc
	 */
	protected function write(string $data):void{
		$i = 0;

		while($i < $this->strlen){
			$this->bitBuffer->put(ord($data[$i]), 8);
			$i++;
		}

	}

}
