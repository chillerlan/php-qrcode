<?php
/**
 * Class QRMatrix
 *
 * @filesource   QRMatrix.php
 * @created      15.11.2017
 * @package      chillerlan\QRCode\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\Common\{EccLevel, Version};
use chillerlan\QRCode\QRCode;
use Closure;

use function array_fill, array_push, array_unshift, count, floor, max, min, range;

/**
 * Holds a numerical representation of the final QR Code;
 * maps the ECC coded binary data and applies the mask pattern
 *
 * @see http://www.thonky.com/qr-code-tutorial/format-version-information
 */
final class QRMatrix{

	/** @var int */
	public const M_NULL       = 0x00;
	/** @var int */
	public const M_DARKMODULE = 0x02;
	/** @var int */
	public const M_DATA       = 0x04;
	/** @var int */
	public const M_FINDER     = 0x06;
	/** @var int */
	public const M_SEPARATOR  = 0x08;
	/** @var int */
	public const M_ALIGNMENT  = 0x0a;
	/** @var int */
	public const M_TIMING     = 0x0c;
	/** @var int */
	public const M_FORMAT     = 0x0e;
	/** @var int */
	public const M_VERSION    = 0x10;
	/** @var int */
	public const M_QUIETZONE  = 0x12;
	/** @var int */
	public const M_LOGO       = 0x14;
	/** @var int */
	public const M_FINDER_DOT = 0x16;
	/** @var int */
	public const M_TEST       = 0xff;

	/**
	 * the used mask pattern, set via QRMatrix::mapData()
	 */
	protected int $maskPattern = QRCode::MASK_PATTERN_AUTO;

	/**
	 * the size (side length) of the matrix, including quiet zone (if created)
	 */
	protected int $moduleCount;

	/**
	 * the actual matrix data array
	 *
	 * @var int[][]
	 */
	protected array $matrix;

	/**
	 * the current ECC level
	 */
	protected EccLevel $eccLevel;

	/**
	 * a Version instance
	 */
	protected Version $version;

	/**
	 * QRMatrix constructor.
	 */
	public function __construct(Version $version, EccLevel $eccLevel){
		$this->version     = $version;
		$this->eccLevel    = $eccLevel;
		$this->moduleCount = $this->version->getDimension();
		$this->matrix      = array_fill(0, $this->moduleCount, array_fill(0, $this->moduleCount, $this::M_NULL));
	}

	/**
	 * shortcut to initialize the matrix
	 */
	public function init(int $maskPattern, bool $test = null):QRMatrix{
		return $this
			->setFinderPattern()
			->setSeparators()
			->setAlignmentPattern()
			->setTimingPattern()
			->setVersionNumber($test)
			->setFormatInfo($maskPattern, $test)
			->setDarkModule()
		;
	}

	/**
	 * Returns the data matrix, returns a pure boolean representation if $boolean is set to true
	 *
	 * @return int[][]|bool[][]
	 */
	public function matrix(bool $boolean = false):array{

		if(!$boolean){
			return $this->matrix;
		}

		$matrix = [];

		foreach($this->matrix as $y => $row){
			$matrix[$y] = [];

			foreach($row as $x => $val){
				$matrix[$y][$x] = ($val >> 8) > 0;
			}
		}

		return $matrix;
	}

	/**
	 * Returns the current version number
	 */
	public function version():Version{
		return $this->version;
	}

	/**
	 * Returns the current ECC level
	 */
	public function eccLevel():EccLevel{
		return $this->eccLevel;
	}

	/**
	 * Returns the current mask pattern
	 */
	public function maskPattern():int{
		return $this->maskPattern;
	}

	/**
	 * Returns the absoulute size of the matrix, including quiet zone (after setting it).
	 *
	 * size = version * 4 + 17 [ + 2 * quietzone size]
	 */
	public function size():int{
		return $this->moduleCount;
	}

	/**
	 * Returns the value of the module at position [$x, $y]
	 */
	public function get(int $x, int $y):int{
		return $this->matrix[$y][$x];
	}

	/**
	 * Sets the $M_TYPE value for the module at position [$x, $y]
	 *
	 *   true  => $M_TYPE << 8
	 *   false => $M_TYPE
	 */
	public function set(int $x, int $y, bool $value, int $M_TYPE):QRMatrix{
		$this->matrix[$y][$x] = $M_TYPE << ($value ? 8 : 0);

		return $this;
	}

	/**
	 * Checks whether a module is true (dark) or false (light)
	 *
	 *   true  => $value >> 8 === $M_TYPE
	 *            $value >> 8 > 0
	 *
	 *   false => $value === $M_TYPE
	 *            $value >> 8 === 0
	 */
	public function check(int $x, int $y):bool{
		return ($this->matrix[$y][$x] >> 8) > 0;
	}


	/**
	 * Sets the "dark module", that is always on the same position 1x1px away from the bottom left finder
	 */
	public function setDarkModule():QRMatrix{
		$this->set(8, 4 * $this->version->getVersionNumber() + 9, true, $this::M_DARKMODULE);

		return $this;
	}

	/**
	 * Draws the 7x7 finder patterns in the corners top left/right and bottom left
	 *
	 * ISO/IEC 18004:2000 Section 7.3.2
	 */
	public function setFinderPattern():QRMatrix{

		$pos = [
			[0, 0], // top left
			[$this->moduleCount - 7, 0], // bottom left
			[0, $this->moduleCount - 7], // top right
		];

		foreach($pos as $c){
			for($y = 0; $y < 7; $y++){
				for($x = 0; $x < 7; $x++){
					// outer (dark) 7*7 square
					if($x === 0 || $x === 6 || $y === 0 || $y === 6){
						$this->set($c[0] + $y, $c[1] + $x, true, $this::M_FINDER);
					}
					// inner (light) 5*5 square
					elseif($x === 1 || $x === 5 || $y === 1 || $y === 5){
						$this->set($c[0] + $y, $c[1] + $x, false, $this::M_FINDER);
					}
					// 3*3 dot
					else{
						$this->set($c[0] + $y, $c[1] + $x, true, $this::M_FINDER_DOT);
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Draws the separator lines around the finder patterns
	 *
	 * ISO/IEC 18004:2000 Section 7.3.3
	 */
	public function setSeparators():QRMatrix{

		$h = [
			[7, 0],
			[$this->moduleCount - 8, 0],
			[7, $this->moduleCount - 8],
		];

		$v = [
			[7, 7],
			[$this->moduleCount - 1, 7],
			[7, $this->moduleCount - 8],
		];

		for($c = 0; $c < 3; $c++){
			for($i = 0; $i < 8; $i++){
				$this->set($h[$c][0]     , $h[$c][1] + $i, false, $this::M_SEPARATOR);
				$this->set($v[$c][0] - $i, $v[$c][1]     , false, $this::M_SEPARATOR);
			}
		}

		return $this;
	}


	/**
	 * Draws the 5x5 alignment patterns
	 *
	 * ISO/IEC 18004:2000 Section 7.3.5
	 */
	public function setAlignmentPattern():QRMatrix{
		$alignmentPattern = $this->version->getAlignmentPattern();

		foreach($alignmentPattern as $y){
			foreach($alignmentPattern as $x){

				// skip existing patterns
				if($this->matrix[$y][$x] !== $this::M_NULL){
					continue;
				}

				for($ry = -2; $ry <= 2; $ry++){
					for($rx = -2; $rx <= 2; $rx++){
						$v = ($ry === 0 && $rx === 0) || $ry === 2 || $ry === -2 || $rx === 2 || $rx === -2;

						$this->set($x + $rx, $y + $ry, $v, $this::M_ALIGNMENT);
					}
				}

			}
		}

		return $this;
	}


	/**
	 * Draws the timing pattern (h/v checkered line between the finder patterns)
	 *
	 * ISO/IEC 18004:2000 Section 7.3.4
	 */
	public function setTimingPattern():QRMatrix{

		foreach(range(8, $this->moduleCount - 8 - 1) as $i){

			if($this->matrix[6][$i] !== $this::M_NULL || $this->matrix[$i][6] !== $this::M_NULL){
				continue;
			}

			$v = $i % 2 === 0;

			$this->set($i, 6, $v, $this::M_TIMING); // h
			$this->set(6, $i, $v, $this::M_TIMING); // v
		}

		return $this;
	}

	/**
	 * Draws the version information, 2x 3x6 pixel
	 *
	 * ISO/IEC 18004:2000 Section 8.10
	 */
	public function setVersionNumber(bool $test = null):QRMatrix{
		$bits = $this->version->getVersionPattern();

		if($bits !== null){

			for($i = 0; $i < 18; $i++){
				$a = (int)floor($i / 3);
				$b = $i % 3 + $this->moduleCount - 8 - 3;
				$v = !$test && (($bits >> $i) & 1) === 1;

				$this->set($b, $a, $v, $this::M_VERSION); // ne
				$this->set($a, $b, $v, $this::M_VERSION); // sw
			}

		}

		return $this;
	}

	/**
	 * Draws the format info along the finder patterns
	 *
	 * ISO/IEC 18004:2000 Section 8.9
	 */
	public function setFormatInfo(int $maskPattern, bool $test = null):QRMatrix{
		$bits = $this->eccLevel->getformatPattern($maskPattern);

		for($i = 0; $i < 15; $i++){
			$v = !$test && (($bits >> $i) & 1) === 1;

			if($i < 6){
				$this->set(8, $i, $v, $this::M_FORMAT);
			}
			elseif($i < 8){
				$this->set(8, $i + 1, $v, $this::M_FORMAT);
			}
			else{
				$this->set(8, $this->moduleCount - 15 + $i, $v, $this::M_FORMAT);
			}

			if($i < 8){
				$this->set($this->moduleCount - $i - 1, 8, $v, $this::M_FORMAT);
			}
			elseif($i < 9){
				$this->set(15 - $i, 8, $v, $this::M_FORMAT);
			}
			else{
				$this->set(15 - $i - 1, 8, $v, $this::M_FORMAT);
			}

		}

		$this->set(8, $this->moduleCount - 8, !$test, $this::M_FORMAT);

		return $this;
	}

	/**
	 * Draws the "quiet zone" of $size around the matrix
	 *
	 * ISO/IEC 18004:2000 Section 7.3.7
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public function setQuietZone(int $size = null):QRMatrix{

		if($this->matrix[$this->moduleCount - 1][$this->moduleCount - 1] === $this::M_NULL){
			throw new QRCodeDataException('use only after writing data');
		}

		$size = $size !== null
			? max(0, min($size, floor($this->moduleCount / 2)))
			: 4;

		for($y = 0; $y < $this->moduleCount; $y++){
			for($i = 0; $i < $size; $i++){
				array_unshift($this->matrix[$y], $this::M_QUIETZONE);
				array_push($this->matrix[$y], $this::M_QUIETZONE);
			}
		}

		$this->moduleCount += ($size * 2);

		$r = array_fill(0, $this->moduleCount, $this::M_QUIETZONE);

		for($i = 0; $i < $size; $i++){
			array_unshift($this->matrix, $r);
			array_push($this->matrix, $r);
		}

		return $this;
	}

	/**
	 * Clears a space of $width * $height in order to add a logo or text.
	 *
	 * Additionally, the logo space can be positioned within the QR Code - respecting the main functional patterns -
	 * using $startX and $startY. If either of these are null, the logo space will be centered in that direction.
	 * ECC level "H" (30%) is required.
	 *
	 * Please note that adding a logo space minimizes the error correction capacity of the QR Code and
	 * created images may become unreadable, especially when printed with a chance to receive damage.
	 * Please test thoroughly before using this feature in production.
	 *
	 * This method should be called from within an output module (after the matrix has been filled with data).
	 * Note that there is no restiction on how many times this method could be called on the same matrix instance.
	 *
	 * @link https://github.com/chillerlan/php-qrcode/issues/52
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public function setLogoSpace(int $width, int $height, int $startX = null, int $startY = null):QRMatrix{

		// for logos we operate in ECC H (30%) only
		if($this->eccLevel->getLevel() !== EccLevel::H){
			throw new QRCodeDataException('ECC level "H" required to add logo space');
		}

		// we need uneven sizes, adjust if needed
		if(($width % 2) === 0){
			$width++;
		}

		if(($height % 2) === 0){
			$height++;
		}

		// $this->moduleCount includes the quiet zone (if created), we need the QR size here
		$length = $this->version->getDimension();

		// throw if the logo space exceeds the maximum error correction capacity
		if($width * $height > floor($length * $length * 0.2)){
			throw new QRCodeDataException('logo space exceeds the maximum error correction capacity');
		}

		// quiet zone size
		$qz    = ($this->moduleCount - $length) / 2;
		// skip quiet zone and the first 9 rows/columns (finder-, mode-, version- and timing patterns)
		$start = $qz + 9;
		// skip quiet zone
		$end   = $this->moduleCount - $qz;

		// determine start coordinates
		$startX = ($startX !== null ? $startX : ($length - $width) / 2) + $qz;
		$startY = ($startY !== null ? $startY : ($length - $height) / 2) + $qz;

		// clear the space
		foreach($this->matrix as $y => $row){
			foreach($row as $x => $val){
				// out of bounds, skip
				if($x < $start || $y < $start ||$x >= $end || $y >= $end){
					continue;
				}
				// a match
				if($x >= $startX && $x < ($startX + $width) && $y >= $startY && $y < ($startY + $height)){
					$this->set($x, $y, false, $this::M_LOGO);
				}
			}
		}

		return $this;
	}

	/**
	 * Maps the binary $data array from QRData::maskECC() on the matrix,
	 * masking the data using $maskPattern (ISO/IEC 18004:2000 Section 8.8)
	 *
	 * @see \chillerlan\QRCode\Data\QRData::maskECC()
	 *
	 * @param int[] $data
	 * @param int   $maskPattern
	 *
	 * @return \chillerlan\QRCode\Data\QRMatrix
	 */
	public function mapData(array $data, int $maskPattern):QRMatrix{
		$this->maskPattern = $maskPattern;
		$byteCount         = count($data);
		$y                 = $this->moduleCount - 1;
		$inc               = -1;
		$byteIndex         = 0;
		$bitIndex          = 7;
		$mask              = $this->getMask($this->maskPattern);

		for($i = $y; $i > 0; $i -= 2){

			if($i === 6){
				$i--;
			}

			while(true){
				for($c = 0; $c < 2; $c++){
					$x = $i - $c;

					if($this->matrix[$y][$x] === $this::M_NULL){
						$v = false;

						if($byteIndex < $byteCount){
							$v = (($data[$byteIndex] >> $bitIndex) & 1) === 1;
						}

						if($mask($x, $y) === 0){
							$v = !$v;
						}

						$this->matrix[$y][$x] = $this::M_DATA << ($v ? 8 : 0);
						$bitIndex--;

						if($bitIndex === -1){
							$byteIndex++;
							$bitIndex = 7;
						}

					}
				}

				$y += $inc;

				if($y < 0 || $this->moduleCount <= $y){
					$y   -=  $inc;
					$inc  = -$inc;

					break;
				}

			}
		}

		return $this;
	}

	/**
	 * ISO/IEC 18004:2000 Section 8.8.1
	 *
	 * Note that some versions of the QR code standard have had errors in the section about mask patterns.
	 * The information below has been corrected. (https://www.thonky.com/qr-code-tutorial/mask-patterns)
	 *
	 * @see \chillerlan\QRCode\QRMatrix::mapData()
	 *
	 * @internal
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	protected function getMask(int $maskPattern):Closure{

		if((0b111 & $maskPattern) !== $maskPattern){
			throw new QRCodeDataException('invalid mask pattern'); // @codeCoverageIgnore
		}

		return [
			0b000 => fn($x, $y):int => ($x + $y) % 2,
			0b001 => fn($x, $y):int => $y % 2,
			0b010 => fn($x, $y):int => $x % 3,
			0b011 => fn($x, $y):int => ($x + $y) % 3,
			0b100 => fn($x, $y):int => ((int)($y / 2) + (int)($x / 3)) % 2,
			0b101 => fn($x, $y):int => (($x * $y) % 2) + (($x * $y) % 3),
			0b110 => fn($x, $y):int => ((($x * $y) % 2) + (($x * $y) % 3)) % 2,
			0b111 => fn($x, $y):int => ((($x * $y) % 3) + (($x + $y) % 2)) % 2,
		][$maskPattern];
	}

}
