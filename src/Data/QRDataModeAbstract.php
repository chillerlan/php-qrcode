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
	 * QRDataModeAbstract constructor.
	 */
	public function __construct(string $data){
		$this->data      = $data;
	}

	/**
	 * returns the character count of the $data string
	 */
	protected function getCharCount():int{
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

}
