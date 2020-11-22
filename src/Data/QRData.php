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

use chillerlan\QRCode\Common\{EccLevel, Mode, Version};
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\Helpers\{BitBuffer, Polynomial};
use chillerlan\Settings\SettingsContainerInterface;

use function array_fill, array_merge, count, max, range, sprintf;

/**
 * Processes the binary data and maps it on a matrix which is then being returned
 */
class QRData{

	/**
	 * current QR Code version
	 */
	protected Version $version;

	/**
	 * ECC temp data
	 */
	protected array $ecdata;

	/**
	 * ECC temp data
	 */
	protected array $dcdata;

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
	 * the options instance
	 *
	 * @var \chillerlan\Settings\SettingsContainerInterface|\chillerlan\QRCode\QROptions
	 */
	protected SettingsContainerInterface $options;

	/**
	 * a BitBuffer instance
	 */
	protected BitBuffer $bitBuffer;

	protected EccLevel $eccLevel;

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
	public function initMatrix(int $maskPattern, bool $test = null):QRMatrix{
		return (new QRMatrix($this->version, $this->eccLevel))
			->init($maskPattern, $test)
			->mapData($this->maskECC(), $maskPattern)
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
		while($this->bitBuffer->getLength() % 8 !== 0){
			$this->bitBuffer->putBit(false);
		}

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

	/**
	 * ECC masking
	 *
	 * ISO/IEC 18004:2000 Section 8.5 ff
	 *
	 * @see http://www.thonky.com/qr-code-tutorial/error-correction-coding
	 */
	protected function maskECC():array{
		[$l1, $l2, $b1, $b2] = $this->eccLevel->getRSBlocks($this->version->getVersionNumber());

		$rsBlocks     = array_fill(0, $l1, [$b1, $b2]);
		$rsCount      = $l1 + $l2;
		$this->ecdata = array_fill(0, $rsCount, []);
		$this->dcdata = $this->ecdata;

		if($l2 > 0){
			$rsBlocks = array_merge($rsBlocks, array_fill(0, $l2, [$b1 + 1, $b2 + 1]));
		}

		$totalCodeCount = 0;
		$maxDcCount     = 0;
		$maxEcCount     = 0;
		$offset         = 0;

		$bitBuffer = $this->bitBuffer->getBuffer();

		foreach($rsBlocks as $key => $block){
			[$rsBlockTotal, $dcCount] = $block;

			$ecCount            = $rsBlockTotal - $dcCount;
			$maxDcCount         = max($maxDcCount, $dcCount);
			$maxEcCount         = max($maxEcCount, $ecCount);
			$this->dcdata[$key] = array_fill(0, $dcCount, null);

			foreach($this->dcdata[$key] as $a => $_z){
				$this->dcdata[$key][$a] = 0xff & $bitBuffer[$a + $offset];
			}

			[$num, $add] = $this->poly($key, $ecCount);

			foreach($this->ecdata[$key] as $c => $_){
				$modIndex               = $c + $add;
				$this->ecdata[$key][$c] = $modIndex >= 0 ? $num[$modIndex] : 0;
			}

			$offset         += $dcCount;
			$totalCodeCount += $rsBlockTotal;
		}

		$data  = array_fill(0, $totalCodeCount, null);
		$index = 0;

		$mask = function(array $arr, int $count) use (&$data, &$index, $rsCount):void{
			for($x = 0; $x < $count; $x++){
				for($y = 0; $y < $rsCount; $y++){
					if($x < count($arr[$y])){
						$data[$index] = $arr[$y][$x];
						$index++;
					}
				}
			}
		};

		$mask($this->dcdata, $maxDcCount);
		$mask($this->ecdata, $maxEcCount);

		return $data;
	}

	/**
	 * helper method for the polynomial operations
	 */
	protected function poly(int $key, int $count):array{
		$rsPoly  = new Polynomial;
		$modPoly = new Polynomial;

		for($i = 0; $i < $count; $i++){
			$modPoly->setNum([1, $modPoly->gexp($i)]);
			$rsPoly->multiply($modPoly->getNum());
		}

		$rsPolyCount = count($rsPoly->getNum());

		$modPoly
			->setNum($this->dcdata[$key], $rsPolyCount - 1)
			->mod($rsPoly->getNum())
		;

		$this->ecdata[$key] = array_fill(0, $rsPolyCount - 1, null);
		$num                = $modPoly->getNum();

		return [
			$num,
			count($num) - count($this->ecdata[$key]),
		];
	}

}
