<?php
/**
 * Class QRData
 *
 * @filesource   QRData.php
 * @created      25.11.2015
 * @package      chillerlan\QRCode\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\Common\{BitBuffer, EccLevel, Mode, ReedSolomonEncoder, Version};
use chillerlan\QRCode\QRCode;
use chillerlan\Settings\SettingsContainerInterface;

use function range, sprintf;

/**
 * Processes the binary data and maps it on a matrix which is then being returned
 */
final class QRData{

	/**
	 * the options instance
	 *
	 * @var \chillerlan\Settings\SettingsContainerInterface|\chillerlan\QRCode\QROptions
	 */
	protected SettingsContainerInterface $options;

	/**
	 * a BitBuffer instance
	 */
	protected BitBuffer $bitBuffer;

	/**
	 * an EccLevel instance
	 */
	protected EccLevel $eccLevel;

	/**
	 * current QR Code version
	 */
	protected Version $version;

	/**
	 * @var \chillerlan\QRCode\Data\QRDataModeInterface[]
	 */
	protected array $dataSegments = [];

	/**
	 * Max bits for the current ECC mode
	 *
	 * @var int[]
	 */
	protected array $maxBitsForEcc;

	/**
	 * QRData constructor.
	 *
	 * @param \chillerlan\Settings\SettingsContainerInterface $options
	 * @param array|null                                      $dataSegments
	 */
	public function __construct(SettingsContainerInterface $options, array $dataSegments = null){
		$this->options       = $options;
		$this->bitBuffer     = new BitBuffer;
		$this->eccLevel      = new EccLevel($this->options->eccLevel);
		$this->maxBitsForEcc = $this->eccLevel->getMaxBits();

		if(!empty($dataSegments)){
			$this->setData($dataSegments);
		}

	}

	/**
	 * Sets the data string (internally called by the constructor)
	 */
	public function setData(array $dataSegments):QRData{

		foreach($dataSegments as $segment){
			[$class, $data] = $segment;

			$this->dataSegments[] = new $class($data);
		}

		$version = $this->options->version === QRCode::VERSION_AUTO
			? $this->getMinimumVersion()
			: $this->options->version;

		$this->version = new Version($version);

		$this->writeBitBuffer();

		return $this;
	}

	/**
	 * returns a fresh matrix object with the data written for the given $maskPattern
	 */
	public function writeMatrix(int $maskPattern, bool $test = null):QRMatrix{
		$data = (new ReedSolomonEncoder)->interleaveEcBytes($this->bitBuffer, $this->version, $this->eccLevel);

		return (new QRMatrix($this->version, $this->eccLevel))
			->init($maskPattern, $test)
			->mapData($data)
			->mask($maskPattern)
		;
	}

	/**
	 * estimates the total length of the several mode segments in order to guess the minimum version
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	protected function estimateTotalBitLength():int{
		$length = 0;
		$margin = 0;

		foreach($this->dataSegments as $segment){
			// data length in bits of the current segment +4 bits for each mode descriptor
			$length += ($segment->getLengthInBits() + Mode::getLengthBitsForMode($segment->getDataMode())[0] + 4);

			if(!$segment instanceof ECI){
				// mode length bits margin to the next breakpoint
				$margin += ($segment instanceof Byte ? 8 : 2);
			}
		}

		foreach([9, 26, 40] as $breakpoint){

			// length bits for the first breakpoint have already been added
			if($breakpoint > 9){
				$length += $margin;
			}

			if($length < $this->maxBitsForEcc[$breakpoint]){
				return $length;
			}
		}

		throw new QRCodeDataException(sprintf('estimated data exceeds %d bits', $length));
	}

	/**
	 * returns the minimum version number for the given string
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	protected function getMinimumVersion():int{
		$total = $this->estimateTotalBitLength();

		// guess the version number within the given range
		foreach(range($this->options->versionMin, $this->options->versionMax) as $version){

			if($total <= $this->maxBitsForEcc[$version]){
				return $version;
			}
		}

		// it's almost impossible to run into this one as $this::estimateTotalBitLength() would throw first
		throw new QRCodeDataException('failed to guess minimum version'); // @codeCoverageIgnore
	}

	/**
	 * creates a BitBuffer and writes the string data to it
	 *
	 * @throws \chillerlan\QRCode\QRCodeException on data overflow
	 */
	protected function writeBitBuffer():void{
		$version  = $this->version->getVersionNumber();
		$MAX_BITS = $this->maxBitsForEcc[$version];

		foreach($this->dataSegments as $segment){
			$segment->write($this->bitBuffer, $version);
		}

		// overflow, likely caused due to invalid version setting
		if($this->bitBuffer->getLength() > $MAX_BITS){
			throw new QRCodeDataException(
				sprintf('code length overflow. (%d > %d bit)', $this->bitBuffer->getLength(), $MAX_BITS)
			);
		}

		// add terminator (ISO/IEC 18004:2000 Table 2)
		if($this->bitBuffer->getLength() + 4 <= $MAX_BITS){
			$this->bitBuffer->put(0b0000, 4);
		}

		// Padding: ISO/IEC 18004:2000 8.4.9 Bit stream to codeword conversion

		// if the final codeword is not exactly 8 bits in length, it shall be made 8 bits long
		// by the addition of padding bits with binary value 0
		while($this->bitBuffer->getLength() % 8 !== 0){
			$this->bitBuffer->putBit(false);
		}

		// The message bit stream shall then be extended to fill the data capacity of the symbol
		// corresponding to the Version and Error Correction Level, by the addition of the Pad
		// Codewords 11101100 and 00010001 alternately.
		while(true){

			if($this->bitBuffer->getLength() >= $MAX_BITS){
				break;
			}

			$this->bitBuffer->put(0b11101100, 8);

			if($this->bitBuffer->getLength() >= $MAX_BITS){
				break;
			}

			$this->bitBuffer->put(0b00010001, 8);
		}

	}

}
