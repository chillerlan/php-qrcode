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

use chillerlan\QRCode\QRCode;

/**
 * Alphanumeric mode: 0 to 9, A to Z, space, $ % * + - . / :
 */
class AlphaNum extends QRDataAbstract{

	const CHAR_MAP = [
		'0', '1', '2', '3', '4', '5', '6', '7',
		'8', '9', 'A', 'B', 'C', 'D', 'E', 'F',
		'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N',
		'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
		'W', 'X', 'Y', 'Z', ' ', '$', '%', '*',
		'+', '-', '.', '/', ':',
	];

	/**
	 * @inheritdoc
	 */
	protected $datamode = QRCode::DATA_ALPHANUM;

	/**
	 * @inheritdoc
	 */
	protected $lengthBits = [9, 11, 13];

	/**
	 * @inheritdoc
	 */
	protected function write(string $data){

		for($i = 0; $i + 1 < $this->strlen; $i += 2){
			$this->bitBuffer->put($this->getCharCode($data[$i]) * 45 + $this->getCharCode($data[$i + 1]), 11);
		}

		if($i < $this->strlen){
			$this->bitBuffer->put($this->getCharCode($data[$i]), 6);
		}

	}

	/**
	 * @param string $chr
	 *
	 * @return int
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	protected function getCharCode(string $chr):int {
		$i = array_search($chr, $this::CHAR_MAP);

		if($i !== false){
			return $i;
		}

		throw new QRCodeDataException('illegal char: "'.$chr.'" ['.ord($chr).']');
	}

}
