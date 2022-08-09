<?php
/**
 * Class QRData
 *
 * @created      25.11.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\Common\{BitBuffer, EccLevel, MaskPattern, Mode, Version};
use chillerlan\Settings\SettingsContainerInterface;

use function sprintf;

/**
 * Processes the binary data and maps it on a matrix which is then being returned
 */
final class QRData{

	/**
	 * the options instance
	 *
	 * @var \chillerlan\Settings\SettingsContainerInterface|\chillerlan\QRCode\QROptions
	 */
	private SettingsContainerInterface $options;

	/**
	 * a BitBuffer instance
	 */
	private BitBuffer $bitBuffer;

	/**
	 * an EccLevel instance
	 */
	private EccLevel $eccLevel;

	/**
	 * current QR Code version
	 */
	private Version $version;

	/**
	 * @var \chillerlan\QRCode\Data\QRDataModeInterface[]
	 */
	private array $dataSegments = [];

	/**
	 * Max bits for the current ECC mode
	 *
	 * @var int[]
	 */
	private array $maxBitsForEcc;

	/**
	 * QRData constructor.
	 *
	 * @param \chillerlan\Settings\SettingsContainerInterface    $options
	 * @param \chillerlan\QRCode\Data\QRDataModeInterface[]|null $dataSegments
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
	 *
	 * Subsequent calls will overwrite the current state - use the QRCode::add*Segement() method instead
	 */
	public function setData(array $dataSegments):self{
		$this->dataSegments = $dataSegments;
		$this->version      = $this->getMinimumVersion();

		$this->bitBuffer->clear();
		$this->writeBitBuffer();

		return $this;
	}

	/**
	 * Sets a BitBuffer object
	 *
	 * This can be used instead of setData(), however, the version auto detection is not available in this case.
	 * The version needs match the length bits range for the data mode the data has been encoded with,
	 * additionally the bit array needs to contain enough pad bits.
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public function setBitBuffer(BitBuffer $bitBuffer):self{

		if($this->options->version === Version::AUTO){
			throw new QRCodeDataException('version auto detection is not available');
		}

		if($bitBuffer->getLength() === 0){
			throw new QRCodeDataException('the given BitBuffer is empty');
		}

		$this->dataSegments = [];
		$this->bitBuffer    = $bitBuffer;
		$this->version      = new Version($this->options->version);

		return $this;
	}

	/**
	 * returns a fresh matrix object with the data written and masked with the given $maskPattern
	 */
	public function writeMatrix(MaskPattern $maskPattern):QRMatrix{
		return (new QRMatrix($this->version, $this->eccLevel, $maskPattern))
			->initFunctionalPatterns()
			->writeCodewords($this->bitBuffer)
			->mask()
		;
	}

	/**
	 * estimates the total length of the several mode segments in order to guess the minimum version
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	private function estimateTotalBitLength():int{
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
	private function getMinimumVersion():Version{

		if($this->options->version !== Version::AUTO){
			return new Version($this->options->version);
		}

		$total = $this->estimateTotalBitLength();

		// guess the version number within the given range
		for($version = $this->options->versionMin; $version <= $this->options->versionMax; $version++){
			if($total <= $this->maxBitsForEcc[$version]){
				return new Version($version);
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
	private function writeBitBuffer():void{
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
			$this->bitBuffer->put(0, 4);
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
		$alternate = false;

		while($this->bitBuffer->getLength() <= $MAX_BITS){
			$this->bitBuffer->put($alternate ? 0b00010001 : 0b11101100, 8);

			$alternate = !$alternate;
		}

	}

}
