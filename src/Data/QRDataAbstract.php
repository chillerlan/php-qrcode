<?php
/**
 * Class QRDataAbstract
 *
 * @filesource   QRDataAbstract.php
 * @created      25.11.2015
 * @package      chillerlan\QRCode\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\{QRCode, QRCodeException};
use chillerlan\QRCode\Helpers\{BitBuffer, Polynomial};
use chillerlan\Settings\SettingsContainerInterface;

use function array_fill, array_merge, count, max, mb_convert_encoding, mb_detect_encoding, range, sprintf, strlen;

/**
 * Processes the binary data and maps it on a matrix which is then being returned
 */
abstract class QRDataAbstract implements QRDataInterface{

	/**
	 * the string byte count
	 *
	 * @var int
	 */
	protected $strlen;

	/**
	 * the current data mode: Num, Alphanum, Kanji, Byte
	 *
	 * @var int
	 */
	protected $datamode;

	/**
	 * mode length bits for the version breakpoints 1-9, 10-26 and 27-40
	 *
	 * @var array
	 */
	protected $lengthBits = [0, 0, 0];

	/**
	 * current QR Code version
	 *
	 * @var int
	 */
	protected $version;

	/**
	 * the raw data that's being passed to QRMatrix::mapData()
	 *
	 * @var array
	 */
	protected $matrixdata;

	/**
	 * ECC temp data
	 *
	 * @var array
	 */
	protected $ecdata;

	/**
	 * ECC temp data
	 *
	 * @var array
	 */
	protected $dcdata;

	/**
	 * @var \chillerlan\QRCode\QROptions
	 */
	protected $options;

	/**
	 * @var \chillerlan\QRCode\Helpers\BitBuffer
	 */
	protected $bitBuffer;

	/**
	 * QRDataInterface constructor.
	 *
	 * @param \chillerlan\Settings\SettingsContainerInterface $options
	 * @param string|null                                     $data
	 */
	public function __construct(SettingsContainerInterface $options, string $data = null){
		$this->options = $options;

		if($data !== null){
			$this->setData($data);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function setData(string $data):QRDataInterface{

		if($this->datamode === QRCode::DATA_KANJI){
			$data = mb_convert_encoding($data, 'SJIS', mb_detect_encoding($data));
		}

		$this->strlen  = $this->getLength($data);
		$this->version = $this->options->version === QRCode::VERSION_AUTO
			? $this->getMinimumVersion()
			: $this->options->version;

		$this->matrixdata = $this
			->writeBitBuffer($data)
			->maskECC()
		;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function initMatrix(int $maskPattern, bool $test = null):QRMatrix{
		return (new QRMatrix($this->version, $this->options->eccLevel))
			->setFinderPattern()
			->setSeparators()
			->setAlignmentPattern()
			->setTimingPattern()
			->setVersionNumber($test)
			->setFormatInfo($maskPattern, $test)
			->setDarkModule()
			->mapData($this->matrixdata, $maskPattern)
		;
	}

	/**
	 * returns the length bits for the version breakpoints 1-9, 10-26 and 27-40
	 *
	 * @return int
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 * @codeCoverageIgnore
	 */
	protected function getLengthBits():int{

		 foreach([9, 26, 40] as $key => $breakpoint){
			 if($this->version <= $breakpoint){
				 return $this->lengthBits[$key];
			 }
		 }

		throw new QRCodeDataException(sprintf('invalid version number: %d', $this->version));
	}

	/**
	 * returns the byte count of the $data string
	 *
	 * @param string $data
	 *
	 * @return int
	 */
	protected function getLength(string $data):int{
		return strlen($data);
	}

	/**
	 * returns the minimum version number for the given string
	 *
	 * @return int
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	protected function getMinimumVersion():int{
		$maxlength = 0;

		// guess the version number within the given range
		foreach(range($this->options->versionMin, $this->options->versionMax) as $version){
			$maxlength = $this::MAX_LENGTH[$version][QRCode::DATA_MODES[$this->datamode]][QRCode::ECC_MODES[$this->options->eccLevel]];

			if($this->strlen <= $maxlength){
				return $version;
			}
		}

		throw new QRCodeDataException(sprintf('data exceeds %d characters', $maxlength));
	}

	/**
	 * writes the actual data string to the BitBuffer
	 *
	 * @see \chillerlan\QRCode\Data\QRDataAbstract::writeBitBuffer()
	 *
	 * @param string $data
	 *
	 * @return void
	 */
	abstract protected function write(string $data):void;

	/**
	 * creates a BitBuffer and writes the string data to it
	 *
	 * @param string $data
	 *
	 * @return \chillerlan\QRCode\Data\QRDataAbstract
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function writeBitBuffer(string $data):QRDataInterface{
		$this->bitBuffer = new BitBuffer;

		$MAX_BITS = $this::MAX_BITS[$this->version][QRCode::ECC_MODES[$this->options->eccLevel]];

		$this->bitBuffer
			->clear()
			->put($this->datamode, 4)
			->put($this->strlen, $this->getLengthBits())
		;

		$this->write($data);

		// there was an error writing the BitBuffer data, which is... unlikely.
		if($this->bitBuffer->length > $MAX_BITS){
			throw new QRCodeException(sprintf('code length overflow. (%d > %d bit)', $this->bitBuffer->length, $MAX_BITS)); // @codeCoverageIgnore
		}

		// end code.
		if($this->bitBuffer->length + 4 <= $MAX_BITS){
			$this->bitBuffer->put(0, 4);
		}

		// padding
		while($this->bitBuffer->length % 8 !== 0){
			$this->bitBuffer->putBit(false);
		}

		// padding
		while(true){

			if($this->bitBuffer->length >= $MAX_BITS){
				break;
			}

			$this->bitBuffer->put(0xEC, 8);

			if($this->bitBuffer->length >= $MAX_BITS){
				break;
			}

			$this->bitBuffer->put(0x11, 8);
		}

		return $this;
	}

	/**
	 * ECC masking
	 *
	 * @link http://www.thonky.com/qr-code-tutorial/error-correction-coding
	 *
	 * @return array
	 */
	protected function maskECC():array{
		[$l1, $l2, $b1, $b2] = $this::RSBLOCKS[$this->version][QRCode::ECC_MODES[$this->options->eccLevel]];

		$rsBlocks     = array_fill(0, $l1, [$b1, $b2]);
		$rsCount      = $l1 + $l2;
		$this->ecdata = array_fill(0, $rsCount, null);
		$this->dcdata = $this->ecdata;

		if($l2 > 0){
			$rsBlocks = array_merge($rsBlocks, array_fill(0, $l2, [$b1 + 1, $b2 + 1]));
		}

		$totalCodeCount = 0;
		$maxDcCount     = 0;
		$maxEcCount     = 0;
		$offset         = 0;

		foreach($rsBlocks as $key => $block){
			[$rsBlockTotal, $dcCount] = $block;

			$ecCount            = $rsBlockTotal - $dcCount;
			$maxDcCount         = max($maxDcCount, $dcCount);
			$maxEcCount         = max($maxEcCount, $ecCount);
			$this->dcdata[$key] = array_fill(0, $dcCount, null);

			foreach($this->dcdata[$key] as $a => $_z){
				$this->dcdata[$key][$a] = 0xff & $this->bitBuffer->buffer[$a + $offset];
			}

			[$num, $add] = $this->poly($key, $ecCount);

			foreach($this->ecdata[$key] as $c => $_z){
				$modIndex               = $c + $add;
				$this->ecdata[$key][$c] = $modIndex >= 0 ? $num[$modIndex] : 0;
			}

			$offset         += $dcCount;
			$totalCodeCount += $rsBlockTotal;
		}

		$data  = array_fill(0, $totalCodeCount, null);
		$index = 0;

		$mask = function($arr, $count) use (&$data, &$index, $rsCount){
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
	 * @param int $key
	 * @param int $count
	 *
	 * @return int[]
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
