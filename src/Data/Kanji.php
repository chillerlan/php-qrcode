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

use chillerlan\QRCode\Helpers\BitBuffer;
use chillerlan\QRCode\Common\Mode;

use function mb_convert_encoding, mb_detect_encoding, mb_strlen, ord, sprintf, strlen;

/**
 * Kanji mode: double-byte characters from the Shift JIS character set
 *
 * ISO/IEC 18004:2000 Section 8.3.5
 * ISO/IEC 18004:2000 Section 8.4.5
 */
final class Kanji extends QRDataModeAbstract{

	protected int $datamode = Mode::DATA_KANJI;

	public function __construct(string $data){
		parent::__construct($data);

		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->data = mb_convert_encoding($this->data, 'SJIS', mb_detect_encoding($this->data));
	}

	/**
	 * @inheritdoc
	 */
	protected function getCharCount():int{
		return mb_strlen($this->data, 'SJIS');
	}

	/**
	 * @inheritdoc
	 */
	public function getLengthInBits():int{
		return $this->getCharCount() * 13;
	}

	/**
	 * checks if a string qualifies as Kanji
	 */
	public static function validateString(string $string):bool{
		$i   = 0;
		$len = strlen($string);

		while($i + 1 < $len){
			$c = ((0xff & ord($string[$i])) << 8) | (0xff & ord($string[$i + 1]));

			if(!($c >= 0x8140 && $c <= 0x9FFC) && !($c >= 0xE040 && $c <= 0xEBBF)){
				return false;
			}

			$i += 2;
		}

		return $i >= $len;
	}

	/**
	 * @inheritdoc
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException on an illegal character occurence
	 */
	public function write(BitBuffer $bitBuffer, int $version):void{

		$bitBuffer
			->put($this->datamode, 4)
			->put($this->getCharCount(), Mode::getLengthBitsForVersion($this->datamode, $version))
		;

		$len = strlen($this->data);

		for($i = 0; $i + 1 < $len; $i += 2){
			$c = ((0xff & ord($this->data[$i])) << 8) | (0xff & ord($this->data[$i + 1]));

			if($c >= 0x8140 && $c <= 0x9FFC){
				$c -= 0x8140;
			}
			elseif($c >= 0xE040 && $c <= 0xEBBF){
				$c -= 0xC140;
			}
			else{
				throw new QRCodeDataException(sprintf('illegal char at %d [%d]', $i + 1, $c));
			}

			$bitBuffer->put(((($c >> 8) & 0xff) * 0xC0) + ($c & 0xff), 13);
		}

		if($i < $len){
			throw new QRCodeDataException(sprintf('illegal char at %d', $i + 1));
		}

	}

}
