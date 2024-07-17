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

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\Helpers\{BitBuffer, Polynomial};
use chillerlan\Settings\SettingsContainerInterface;

use function array_fill, array_merge, count, max, mb_convert_encoding, mb_detect_encoding, range, sprintf, strlen;

/**
 * Processes the binary data and maps it on a matrix which is then being returned
 */
abstract class QRDataAbstract implements QRDataInterface{

	/**
	 * the string byte count
	 */
	protected ?int $strlen = null;

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
	 * current QR Code version
	 */
	protected int $version;

	/**
	 * ECC temp data
	 */
	protected array $ecdata;

	/**
	 * ECC temp data
	 */
	protected array $dcdata;

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
	 * QRDataInterface constructor.
	 */
	public function __construct(SettingsContainerInterface $options, ?string $data = null){
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

		$this->writeBitBuffer($data);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function initMatrix(int $maskPattern, ?bool $test = null):QRMatrix{
		return (new QRMatrix($this->version, $this->options->eccLevel))
			->init($maskPattern, $test)
			->mapData($this->maskECC(), $maskPattern)
		;
	}

	/**
	 * returns the length bits for the version breakpoints 1-9, 10-26 and 27-40
	 *
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
	 */
	protected function getLength(string $data):int{
		return strlen($data);
	}

	/**
	 * returns the minimum version number for the given string
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	protected function getMinimumVersion():int{
		$maxlength = 0;

		// guess the version number within the given range
		$dataMode = QRCode::DATA_MODES[$this->datamode];
		$eccMode  = QRCode::ECC_MODES[$this->options->eccLevel];

		foreach(range($this->options->versionMin, $this->options->versionMax) as $version){
			$maxlength = $this::MAX_LENGTH[$version][$dataMode][$eccMode];

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
	 */
	abstract protected function write(string $data):void;

	/**
	 * creates a BitBuffer and writes the string data to it
	 *
	 * @throws \chillerlan\QRCode\QRCodeException on data overflow
	 */
	protected function writeBitBuffer(string $data):void{
		$this->bitBuffer = new BitBuffer;

		$MAX_BITS = $this::MAX_BITS[$this->version][QRCode::ECC_MODES[$this->options->eccLevel]];

		$this->bitBuffer
			->put($this->datamode, 4)
			->put($this->strlen, $this->getLengthBits())
		;

		$this->write($data);

		// overflow, likely caused due to invalid version setting
		if($this->bitBuffer->getLength() > $MAX_BITS){
			throw new QRCodeDataException(sprintf('code length overflow. (%d > %d bit)', $this->bitBuffer->getLength(), $MAX_BITS));
		}

		// add terminator (ISO/IEC 18004:2000 Table 2)
		if($this->bitBuffer->getLength() + 4 <= $MAX_BITS){
			$this->bitBuffer->put(0, 4);
		}

		// padding
		while($this->bitBuffer->getLength() % 8 !== 0){
			$this->bitBuffer->putBit(false);
		}

		// padding
		while(true){

			if($this->bitBuffer->getLength() >= $MAX_BITS){
				break;
			}

			$this->bitBuffer->put(0xEC, 8);

			if($this->bitBuffer->getLength() >= $MAX_BITS){
				break;
			}

			$this->bitBuffer->put(0x11, 8);
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
		[$l1, $l2, $b1, $b2] = $this::RSBLOCKS[$this->version][QRCode::ECC_MODES[$this->options->eccLevel]];

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
