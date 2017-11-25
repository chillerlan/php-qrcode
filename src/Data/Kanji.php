<?php
/**
 * Class Kanji
 *
 * @filesource   Kanji.php
 * @created      25.11.2015
 * @package      chillerlan\QRCode\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\QRCode;

/**
 * Kanji mode: double-byte characters from the Shift JIS character set
 */
class Kanji extends QRDataAbstract{

	/**
	 * @inheritdoc
	 */
	protected $datamode = QRCode::DATA_KANJI;

	/**
	 * @inheritdoc
	 */
	protected $lengthBits = [8, 10, 12];

	/**
	 * @inheritdoc
	 */
	protected function getLength(string $data):int{
		return mb_strlen($data, 'SJIS');
	}

	/**
	 * @param string $data
	 *
	 * @return void
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	protected function write(string $data){
		$len = strlen($data);

		for($i = 0; $i + 1 < $len; $i += 2){
			$c = ((0xff & ord($data[$i])) << 8) | (0xff & ord($data[$i + 1]));

			if(0x8140 <= $c && $c <= 0x9FFC){
				$c -= 0x8140;
			}
			elseif(0xE040 <= $c && $c <= 0xEBBF){
				$c -= 0xC140;
			}
			else{
				throw new QRCodeDataException('illegal char at '.($i + 1).' ['.$c.']');
			}

			$this->bitBuffer->put((($c >> 8) & 0xff) * 0xC0 + ($c & 0xff), 13);

		}

		if($i < $len){
			throw new QRCodeDataException('illegal char at '.($i + 1));
		}

	}

}
