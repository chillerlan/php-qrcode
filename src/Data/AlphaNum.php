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
 *
 * ISO/IEC 18004:2000 Section 8.3.3
 * ISO/IEC 18004:2000 Section 8.4.3
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
	 * get the code for the given character
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException on an illegal character occurence
	 */
	protected function getCharCode(string $chr):int{

		if(!isset($this::CHAR_MAP_ALPHANUM[$chr])){
			throw new QRCodeDataException(sprintf('illegal char: "%s" [%d]', $chr, ord($chr)));
		}

		return $this::CHAR_MAP_ALPHANUM[$chr];
	}

}
