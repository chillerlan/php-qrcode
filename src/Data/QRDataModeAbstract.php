<?php
/**
 * Class QRDataModeAbstract
 *
 * @filesource   QRDataModeAbstract.php
 * @created      19.11.2020
 * @package      chillerlan\QRCode\Data
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\Helpers\BitBuffer;

/**
 */
abstract class QRDataModeAbstract implements QRDataModeInterface{

	/**
	 * the current data mode: Num, Alphanum, Kanji, Byte
	 */
	protected int $datamode;

	/**
	 * mode length bits for the version breakpoints 1-9, 10-26 and 27-40
	 *
	 * ISO/IEC 18004:2000 Table 3 - Number of bits in Character Count Indicator
	 */
	protected array $lengthBits = [0, 0, 0];

	/**
	 * The data to write
	 */
	protected string $data;

	/**
	 * a BitBuffer instance
	 */
	protected BitBuffer $bitBuffer;

	/**
	 * QRDataModeAbstract constructor.
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public function __construct(BitBuffer $bitBuffer, string $data){
		// do we need this here? we check during write anyways.
#		if(!static::validateString($data)){
#			throw new QRCodeDataException('invalid data string');
#		}

		$this->bitBuffer = $bitBuffer;
		$this->data      = $data;
	}

	/**
	 * returns the character count of the $data string
	 */
	protected function getLength():int{
		return strlen($this->data);
	}

	/**
	 * @inheritDoc
	 */
	public function getLengthBits(int $k):int{
		return $this->lengthBits[$k] ?? 0;
	}

	/**
	 * returns the length bits for the version breakpoints 1-9, 10-26 and 27-40
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 * @codeCoverageIgnore
	 */
	protected function getLengthBitsForVersion(int $version):int{

		foreach([9, 26, 40] as $key => $breakpoint){
			if($version <= $breakpoint){
				return $this->getLengthBits($key);
			}
		}

		throw new QRCodeDataException(sprintf('invalid version number: %d', $version));
	}

	/**
	 *
	 */
	protected function writeSegmentHeader(int $version):void{

		$this->bitBuffer
			->put($this->datamode, 4)
			->put($this->getLength(), $this->getLengthBitsForVersion($version))
		;

	}

}
