<?php
/**
 * Class QRMatrix
 *
 * @created      15.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\Common\{BitBuffer, EccLevel, MaskPattern, ReedSolomonEncoder, Version};
use function array_fill, array_unshift, count, floor, max, min, range;

/**
 * Holds a numerical representation of the final QR Code;
 * maps the ECC coded binary data and applies the mask pattern
 *
 * @see http://www.thonky.com/qr-code-tutorial/format-version-information
 */
class QRMatrix{

	/** @var int */
	public const M_NULL       = 0b000000000000;
	/** @var int */
	public const M_DARKMODULE = 0b000000000001;
	/** @var int */
	public const M_DATA       = 0b000000000010;
	/** @var int */
	public const M_FINDER     = 0b000000000100;
	/** @var int */
	public const M_SEPARATOR  = 0b000000001000;
	/** @var int */
	public const M_ALIGNMENT  = 0b000000010000;
	/** @var int */
	public const M_TIMING     = 0b000000100000;
	/** @var int */
	public const M_FORMAT     = 0b000001000000;
	/** @var int */
	public const M_VERSION    = 0b000010000000;
	/** @var int */
	public const M_QUIETZONE  = 0b000100000000;
	/** @var int */
	public const M_LOGO       = 0b001000000000;
	/** @var int */
	public const M_FINDER_DOT = 0b010000000000;
	/** @var int */
	public const M_TEST       = 0b011111111111;
	/** @var int */
	public const IS_DARK      = 0b100000000000;

	/**
	 * the used mask pattern, set via QRMatrix::mask()
	 */
	protected ?MaskPattern $maskPattern = null;

	/**
	 * the current ECC level
	 */
	protected ?EccLevel $eccLevel = null;

	/**
	 * a Version instance
	 */
	protected ?Version $version = null;

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
	 * QRMatrix constructor.
	 */
	public function __construct(Version $version, EccLevel $eccLevel, MaskPattern $maskPattern){
		$this->version     = $version;
		$this->eccLevel    = $eccLevel;
		$this->maskPattern = $maskPattern;
		$this->moduleCount = $this->version->getDimension();
		$this->matrix      = array_fill(0, $this->moduleCount, array_fill(0, $this->moduleCount, $this::M_NULL));
	}

	/**
	 * shortcut to initialize the functional patterns
	 */
	public function initFunctionalPatterns():self{
		return $this
			->setFinderPattern()
			->setSeparators()
			->setAlignmentPattern()
			->setTimingPattern()
			->setDarkModule()
			->setVersionNumber()
			->setFormatInfo()
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
				$matrix[$y][$x] = ($val & $this::IS_DARK) === $this::IS_DARK;
			}
		}

		return $matrix;
	}

	/**
	 * Returns the current version number
	 */
	public function version():?Version{
		return $this->version;
	}

	/**
	 * Returns the current ECC level
	 */
	public function eccLevel():?EccLevel{
		return $this->eccLevel;
	}

	/**
	 * Returns the current mask pattern
	 */
	public function maskPattern():?MaskPattern{
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
	 * Returns the value of the module at position [$x, $y] or -1 if the coordinate is outside of the matrix
	 */
	public function get(int $x, int $y):int{

		if(!isset($this->matrix[$y][$x])){
			return -1;
		}

		return $this->matrix[$y][$x];
	}

	/**
	 * Sets the $M_TYPE value for the module at position [$x, $y]
	 *
	 *   true  => $M_TYPE | 0x800
	 *   false => $M_TYPE
	 */
	public function set(int $x, int $y, bool $value, int $M_TYPE):self{

		if(isset($this->matrix[$y][$x])){
			$this->matrix[$y][$x] = $M_TYPE | ($value ? $this::IS_DARK : 0);
		}

		return $this;
	}

	/**
	 * Flips the value of the module
	 */
	public function flip(int $x, int $y):self{

		if(isset($this->matrix[$y][$x])){
			$this->matrix[$y][$x] ^= $this::IS_DARK;
		}

		return $this;
	}

	/**
	 * Checks whether a module is of the given $M_TYPE
	 *
	 *   true => $value & $M_TYPE === $M_TYPE
	 */
	public function checkType(int $x, int $y, int $M_TYPE):bool{

		if(!isset($this->matrix[$y][$x])){
			return false;
		}

		return ($this->matrix[$y][$x] & $M_TYPE) === $M_TYPE;
	}

	/**
	 * checks whether the module at ($x, $y) is in the given array of $M_TYPES,
	 * returns true if no matches are found, otherwise false.
	 */
	public function checkTypeIn(int $x, int $y, array $M_TYPES):bool{

		foreach($M_TYPES as $type){
			if($this->checkType($x, $y, $type)){
				return true;
			}
		}

		return false;
	}

	/**
	 * checks whether the module at ($x, $y) is not in the given array of $M_TYPES,
	 * returns true if no matches are found, otherwise false.
	 */
	public function checkTypeNotIn(int $x, int $y, array $M_TYPES):bool{

		foreach($M_TYPES as $type){
			if($this->checkType($x, $y, $type)){
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks whether a module is true (dark) or false (light)
	 *
	 *   true  => $value & 0x800 === 0x800
	 *   false => $value & 0x800 === 0
	 */
	public function check(int $x, int $y):bool{
		return $this->checkType($x, $y, $this::IS_DARK);
	}

	/**
	 * Sets the "dark module", that is always on the same position 1x1px away from the bottom left finder
	 *
	 * 4 * version + 9 or moduleCount - 8
	 */
	public function setDarkModule():self{
		$this->set(8, $this->moduleCount - 8, true, $this::M_DARKMODULE);

		return $this;
	}

	/**
	 * Draws the 7x7 finder patterns in the corners top left/right and bottom left
	 *
	 * ISO/IEC 18004:2000 Section 7.3.2
	 */
	public function setFinderPattern():self{

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
	public function setSeparators():self{

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
	public function setAlignmentPattern():self{
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
	public function setTimingPattern():self{

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
	public function setVersionNumber():self{
		$bits = $this->version->getVersionPattern();

		if($bits !== null){

			for($i = 0; $i < 18; $i++){
				$a = (int)($i / 3);
				$b = $i % 3 + $this->moduleCount - 8 - 3;
				$v = (($bits >> $i) & 1) === 1;

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
	public function setFormatInfo():self{
		$bits = $this->eccLevel->getformatPattern($this->maskPattern);

		for($i = 0; $i < 15; $i++){
			$v = (($bits >> $i) & 1) === 1;

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

		return $this;
	}

	/**
	 * Draws the "quiet zone" of $size around the matrix
	 *
	 * ISO/IEC 18004:2000 Section 7.3.7
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public function setQuietZone(int $size = null):self{

		if($this->matrix[$this->moduleCount - 1][$this->moduleCount - 1] === $this::M_NULL){
			throw new QRCodeDataException('use only after writing data');
		}

		$size = $size !== null
			? max(0, min($size, floor($this->moduleCount / 2)))
			: 4;

		for($y = 0; $y < $this->moduleCount; $y++){
			for($i = 0; $i < $size; $i++){
				array_unshift($this->matrix[$y], $this::M_QUIETZONE);
				$this->matrix[$y][] = $this::M_QUIETZONE;
			}
		}

		$this->moduleCount += ($size * 2);

		$r = array_fill(0, $this->moduleCount, $this::M_QUIETZONE);

		for($i = 0; $i < $size; $i++){
			array_unshift($this->matrix, $r);
			$this->matrix[] = $r;
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
	public function setLogoSpace(int $width, int $height, int $startX = null, int $startY = null):self{

		// for logos we operate in ECC H (30%) only
		if($this->eccLevel->getLevel() !== EccLevel::H){
			throw new QRCodeDataException('ECC level "H" required to add logo space');
		}

		// if width and height happen to be exactly 0 (default value), just return - nothing to do
		if($width === 0 || $height === 0){
			return $this;
		}

		// $this->moduleCount includes the quiet zone (if created), we need the QR size here
		$length = $this->version->getDimension();

		// throw if the size is negative or exceeds the qrcode size
		if($width < 0 || $height < 0 || $width > $length || $height > $length){
			throw new QRCodeDataException('invalid logo dimensions');
		}

		// we need uneven sizes to center the logo space, adjust if needed
		if($startX === null && ($width % 2) === 0){
			$width++;
		}

		if($startY === null && ($height % 2) === 0){
			$height++;
		}

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
	 * Maps the interleaved binary $data on the matrix
	 */
	public function writeCodewords(BitBuffer $bitBuffer):self{
		$data      = (new ReedSolomonEncoder)->interleaveEcBytes($bitBuffer, $this->version, $this->eccLevel);
		$byteCount = count($data);
		$iByte     = 0;
		$iBit      = 7;
		$direction = true;

		for($i = $this->moduleCount - 1; $i > 0; $i -= 2){

			// skip vertical alignment pattern
			if($i === 6){
				$i--;
			}

			for($count = 0; $count < $this->moduleCount; $count++){
				$y = $direction ? $this->moduleCount - 1 - $count : $count;

				for($col = 0; $col < 2; $col++){
					$x = $i - $col;

					// skip functional patterns
					if($this->get($x, $y) !== $this::M_NULL){
						continue;
					}

					$v = $iByte < $byteCount && (($data[$iByte] >> $iBit--) & 1) === 1;

					$this->set($x, $y, $v, $this::M_DATA);

					if($iBit === -1){
						$iByte++;
						$iBit = 7;
					}
				}
			}

			$direction = !$direction; // switch directions
		}

		return $this;
	}

	/**
	 * Applies/reverses the mask pattern
	 *
	 * ISO/IEC 18004:2000 Section 8.8.1
	 */
	public function mask():self{
		$mask = $this->maskPattern->getMask();

		foreach($this->matrix as $y => $row){
			foreach($row as $x => $val){
				if($mask($x, $y) && ($val & $this::M_DATA) === $this::M_DATA){
					$this->flip($x, $y);
				}
			}
		}

		return $this;
	}

}
