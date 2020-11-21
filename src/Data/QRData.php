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

use chillerlan\QRCode\Common\{Mode, Version};
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\Helpers\{BitBuffer, Polynomial};
use chillerlan\Settings\SettingsContainerInterface;

use function array_fill, array_merge, count, max, range, sprintf;

/**
 * Processes the binary data and maps it on a matrix which is then being returned
 */
class QRData{

	/**
	 * @see http://www.thonky.com/qr-code-tutorial/error-correction-table
	 *
	 * @var int [][][]
	 */
	const RSBLOCKS = [
		1  => [[ 1,  0,  26,  19], [ 1,  0, 26, 16], [ 1,  0, 26, 13], [ 1,  0, 26,  9]],
		2  => [[ 1,  0,  44,  34], [ 1,  0, 44, 28], [ 1,  0, 44, 22], [ 1,  0, 44, 16]],
		3  => [[ 1,  0,  70,  55], [ 1,  0, 70, 44], [ 2,  0, 35, 17], [ 2,  0, 35, 13]],
		4  => [[ 1,  0, 100,  80], [ 2,  0, 50, 32], [ 2,  0, 50, 24], [ 4,  0, 25,  9]],
		5  => [[ 1,  0, 134, 108], [ 2,  0, 67, 43], [ 2,  2, 33, 15], [ 2,  2, 33, 11]],
		6  => [[ 2,  0,  86,  68], [ 4,  0, 43, 27], [ 4,  0, 43, 19], [ 4,  0, 43, 15]],
		7  => [[ 2,  0,  98,  78], [ 4,  0, 49, 31], [ 2,  4, 32, 14], [ 4,  1, 39, 13]],
		8  => [[ 2,  0, 121,  97], [ 2,  2, 60, 38], [ 4,  2, 40, 18], [ 4,  2, 40, 14]],
		9  => [[ 2,  0, 146, 116], [ 3,  2, 58, 36], [ 4,  4, 36, 16], [ 4,  4, 36, 12]],
		10 => [[ 2,  2,  86,  68], [ 4,  1, 69, 43], [ 6,  2, 43, 19], [ 6,  2, 43, 15]],
		11 => [[ 4,  0, 101,  81], [ 1,  4, 80, 50], [ 4,  4, 50, 22], [ 3,  8, 36, 12]],
		12 => [[ 2,  2, 116,  92], [ 6,  2, 58, 36], [ 4,  6, 46, 20], [ 7,  4, 42, 14]],
		13 => [[ 4,  0, 133, 107], [ 8,  1, 59, 37], [ 8,  4, 44, 20], [12,  4, 33, 11]],
		14 => [[ 3,  1, 145, 115], [ 4,  5, 64, 40], [11,  5, 36, 16], [11,  5, 36, 12]],
		15 => [[ 5,  1, 109,  87], [ 5,  5, 65, 41], [ 5,  7, 54, 24], [11,  7, 36, 12]],
		16 => [[ 5,  1, 122,  98], [ 7,  3, 73, 45], [15,  2, 43, 19], [ 3, 13, 45, 15]],
		17 => [[ 1,  5, 135, 107], [10,  1, 74, 46], [ 1, 15, 50, 22], [ 2, 17, 42, 14]],
		18 => [[ 5,  1, 150, 120], [ 9,  4, 69, 43], [17,  1, 50, 22], [ 2, 19, 42, 14]],
		19 => [[ 3,  4, 141, 113], [ 3, 11, 70, 44], [17,  4, 47, 21], [ 9, 16, 39, 13]],
		20 => [[ 3,  5, 135, 107], [ 3, 13, 67, 41], [15,  5, 54, 24], [15, 10, 43, 15]],
		21 => [[ 4,  4, 144, 116], [17,  0, 68, 42], [17,  6, 50, 22], [19,  6, 46, 16]],
		22 => [[ 2,  7, 139, 111], [17,  0, 74, 46], [ 7, 16, 54, 24], [34,  0, 37, 13]],
		23 => [[ 4,  5, 151, 121], [ 4, 14, 75, 47], [11, 14, 54, 24], [16, 14, 45, 15]],
		24 => [[ 6,  4, 147, 117], [ 6, 14, 73, 45], [11, 16, 54, 24], [30,  2, 46, 16]],
		25 => [[ 8,  4, 132, 106], [ 8, 13, 75, 47], [ 7, 22, 54, 24], [22, 13, 45, 15]],
		26 => [[10,  2, 142, 114], [19,  4, 74, 46], [28,  6, 50, 22], [33,  4, 46, 16]],
		27 => [[ 8,  4, 152, 122], [22,  3, 73, 45], [ 8, 26, 53, 23], [12, 28, 45, 15]],
		28 => [[ 3, 10, 147, 117], [ 3, 23, 73, 45], [ 4, 31, 54, 24], [11, 31, 45, 15]],
		29 => [[ 7,  7, 146, 116], [21,  7, 73, 45], [ 1, 37, 53, 23], [19, 26, 45, 15]],
		30 => [[ 5, 10, 145, 115], [19, 10, 75, 47], [15, 25, 54, 24], [23, 25, 45, 15]],
		31 => [[13,  3, 145, 115], [ 2, 29, 74, 46], [42,  1, 54, 24], [23, 28, 45, 15]],
		32 => [[17,  0, 145, 115], [10, 23, 74, 46], [10, 35, 54, 24], [19, 35, 45, 15]],
		33 => [[17,  1, 145, 115], [14, 21, 74, 46], [29, 19, 54, 24], [11, 46, 45, 15]],
		34 => [[13,  6, 145, 115], [14, 23, 74, 46], [44,  7, 54, 24], [59,  1, 46, 16]],
		35 => [[12,  7, 151, 121], [12, 26, 75, 47], [39, 14, 54, 24], [22, 41, 45, 15]],
		36 => [[ 6, 14, 151, 121], [ 6, 34, 75, 47], [46, 10, 54, 24], [ 2, 64, 45, 15]],
		37 => [[17,  4, 152, 122], [29, 14, 74, 46], [49, 10, 54, 24], [24, 46, 45, 15]],
		38 => [[ 4, 18, 152, 122], [13, 32, 74, 46], [48, 14, 54, 24], [42, 32, 45, 15]],
		39 => [[20,  4, 147, 117], [40,  7, 75, 47], [43, 22, 54, 24], [10, 67, 45, 15]],
		40 => [[19,  6, 148, 118], [18, 31, 75, 47], [34, 34, 54, 24], [20, 61, 45, 15]],
	];

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

	/**
	 * QRData constructor.
	 *
	 * @param \chillerlan\Settings\SettingsContainerInterface $options
	 * @param array|null                                      $dataSegments
	 */
	public function __construct(SettingsContainerInterface $options, array $dataSegments = null){
		$this->options       = $options;
		$this->bitBuffer     = new BitBuffer;
		$this->maxBitsForEcc = Version::getMaxBitsForEcc($this->options->eccLevel);

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
		return (new QRMatrix($this->version, $this->options->eccLevel))
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
		[$l1, $l2, $b1, $b2] = $this::RSBLOCKS[$this->version->getVersionNumber()][QRCode::ECC_MODES[$this->options->eccLevel]];

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
