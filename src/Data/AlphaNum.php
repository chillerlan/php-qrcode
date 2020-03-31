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

use function ord, sprintf;

/**
 * Alphanumeric mode: 0 to 9, A to Z, space, $ % * + - . / :
 */
final class AlphaNum extends QRDataAbstract{

	protected int $datamode = QRCode::DATA_ALPHANUM;

	protected array $lengthBits = [9, 11, 13];

	/**
	 * @inheritdoc
	 */
	protected function write(string $data):void{

		for($i = 0; $i + 1 < $this->strlen; $i += 2){
			$this->bitBuffer->put($this->getCharCode($data[$i]) * 45 + $this->getCharCode($data[$i + 1]), 11);
		}

		if($i < $this->strlen){
			$this->bitBuffer->put($this->getCharCode($data[$i]), 6);
		}

	}

	/**
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	protected function getCharCode(string $chr):int{

		if(!isset($this::CHAR_MAP_ALPHANUM[$chr])){
			throw new QRCodeDataException(sprintf('illegal char: "%s" [%d]', $chr, ord($chr)));
		}

		return $this::CHAR_MAP_ALPHANUM[$chr];
	}

}
