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

use chillerlan\QRCode\QRCode;

/**
 * @link http://www.thonky.com/qr-code-tutorial/format-version-information
 */
class QRMatrix{

	const M_NULL       = 0x00;
	const M_DARKMODULE = 0x02;
	const M_DATA       = 0x04;
	const M_FINDER     = 0x06;
	const M_SEPARATOR  = 0x08;
	const M_ALIGNMENT  = 0x0a;
	const M_TIMING     = 0x0c;
	const M_FORMAT     = 0x0e;
	const M_VERSION    = 0x10;
	const M_QUIETZONE  = 0x12;
	const M_LOGO       = 0x14; // @todo

	const M_TEST       = 0xff;

	/**
	 * @link http://www.thonky.com/qr-code-tutorial/alignment-pattern-locations
	 */
	const alignmentPattern = [ null, // start at 1
		[],
		[6, 18],
		[6, 22],
		[6, 26],
		[6, 30],
		[6, 34],
		[6, 22, 38],
		[6, 24, 42],
		[6, 26, 46],
		[6, 28, 50],
		[6, 30, 54],
		[6, 32, 58],
		[6, 34, 62],
		[6, 26, 46, 66],
		[6, 26, 48, 70],
		[6, 26, 50, 74],
		[6, 30, 54, 78],
		[6, 30, 56, 82],
		[6, 30, 58, 86],
		[6, 34, 62, 90],
		[6, 28, 50, 72,  94],
		[6, 26, 50, 74,  98],
		[6, 30, 54, 78, 102],
		[6, 28, 54, 80, 106],
		[6, 32, 58, 84, 110],
		[6, 30, 58, 86, 114],
		[6, 34, 62, 90, 118],
		[6, 26, 50, 74,  98, 122],
		[6, 30, 54, 78, 102, 126],
		[6, 26, 52, 78, 104, 130],
		[6, 30, 56, 82, 108, 134],
		[6, 34, 60, 86, 112, 138],
		[6, 30, 58, 86, 114, 142],
		[6, 34, 62, 90, 118, 146],
		[6, 30, 54, 78, 102, 126, 150],
		[6, 24, 50, 76, 102, 128, 154],
		[6, 28, 54, 80, 106, 132, 158],
		[6, 32, 58, 84, 110, 136, 162],
		[6, 26, 54, 82, 110, 138, 166],
		[6, 30, 58, 86, 114, 142, 170],
	];

	/**
	 * @link http://www.thonky.com/qr-code-tutorial/format-version-tables
	 */
	const versionPattern = [
		// 1-based version index
		null,
		// no version pattern for QR Codes < 7
		null   , null   , null   , null   , null   , null   , 0x07c94, 0x085bc, 0x09a99, 0x0a4d3,
		0x0bbf6, 0x0c762, 0x0d847, 0x0e60d, 0x0f928, 0x10b78, 0x1145d, 0x12a17, 0x13532, 0x149a6,
		0x15683, 0x168c9, 0x177ec, 0x18ec4, 0x191e1, 0x1afab, 0x1b08e, 0x1cc1a, 0x1d33f, 0x1ed75,
		0x1f250, 0x209d5, 0x216f0, 0x228ba, 0x2379f, 0x24b0b, 0x2542e, 0x26a64, 0x27541, 0x28c69,
	];

	const formatPattern = [
		[0x77c4, 0x72f3, 0x7daa, 0x789d, 0x662f, 0x6318, 0x6c41, 0x6976], // L
		[0x5412, 0x5125, 0x5e7c, 0x5b4b, 0x45f9, 0x40ce, 0x4f97, 0x4aa0], // M
		[0x355f, 0x3068, 0x3f31, 0x3a06, 0x24b4, 0x2183, 0x2eda, 0x2bed], // Q
		[0x1689, 0x13be, 0x1ce7, 0x19d0, 0x0762, 0x0255, 0x0d0c, 0x083b], // H
	];

	/**
	 * @var int
	 */
	protected $version;

	/**
	 * @var int
	 */
	protected $eclevel;

	/**
	 * @var int
	 */
	protected $maskPattern = QRCode::MASK_PATTERN_AUTO;

	/**
	 * @var int
	 */
	protected $moduleCount;

	/**
	 * @var mixed[]
	 */
	protected $matrix;

	/**
	 * QRMatrix constructor.
	 *
	 * @param int $version
	 * @param int $eclevel
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public function __construct(int $version, int $eclevel){

		if(!in_array($version, range(1, 40), true)){
			throw new QRCodeDataException('invalid QR Code version');
		}

		if(!array_key_exists($eclevel, QRCode::ECC_MODES)){
			throw new QRCodeDataException('invalid ecc level');
		}

		$this->version     = $version;
		$this->eclevel     = $eclevel;
		$this->moduleCount = $this->version * 4 + 17;
		$this->matrix      = array_fill(0, $this->moduleCount, array_fill(0, $this->moduleCount, $this::M_NULL));
	}

	/**
	 * @return array
	 */
	public function matrix():array {
		return $this->matrix;
	}

	/**
	 * @return int
	 */
	public function version():int {
		return $this->version;
	}

	/**
	 * @return int
	 */
	public function eccLevel():int {
		return $this->eclevel;
	}

	/**
	 * @return int
	 */
	public function maskPattern():int {
		return $this->maskPattern;
	}

	/**
	 * Returns the absoulute size of the matrix, including quiet zone (after setting it).
	 *
	 * size = version * 4 + 17 [ + 2 * quietzone size]
	 *
	 * @return int
	 */
	public function size():int{
		return $this->moduleCount;
	}

	/**
	 * Returns the value of the module at position [$x, $y]
	 *
	 * @param int $x
	 * @param int $y
	 *
	 * @return int
	 */
	public function get(int $x, int $y):int{
		return $this->matrix[$y][$x];
	}

	/**
	 * Sets the $M_TYPE value for the module at position [$x, $y]
	 *
	 *   true  => $M_TYPE << 8
	 *   false => $M_TYPE
	 *
	 * @param int  $x
	 * @param int  $y
	 * @param int  $M_TYPE
	 * @param bool $value
	 *
	 * @return \chillerlan\QRCode\Data\QRMatrix
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
	 *
	 * @param int $x
	 * @param int $y
	 *
	 * @return bool
	 */
	public function check(int $x, int $y):bool{
		return $this->matrix[$y][$x] >> 8 > 0;
	}


	/**
	 * Sets the "dark module", that is always on the same position 1x1px away from the bottom left finder
	 *
	 * @return \chillerlan\QRCode\Data\QRMatrix
	 */
	public function setDarkModule():QRMatrix{
		$this->set(8, 4 * $this->version + 9, true, $this::M_DARKMODULE);

		return $this;
	}

	/**
	 * Draws the 7x7 finder patterns in the corners top left/right and bottom left
	 *
	 * @return \chillerlan\QRCode\Data\QRMatrix
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
					$this->set(
						$c[0] + $y,
						$c[1] + $x,
						!(($x > 0 && $x < 6 && ($y === 1 || $y === 5)) || ($y > 0 && $y < 6 && ($x === 1 || $x === 5))),
						$this::M_FINDER
					);
				}
			}
		}

		return $this;
	}

	/**
	 * Draws the separator lines around the finder patterns
	 *
	 * @return \chillerlan\QRCode\Data\QRMatrix
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
	 * @return \chillerlan\QRCode\Data\QRMatrix
	 */
	public function setAlignmentPattern():QRMatrix{
		$pattern = $this::alignmentPattern[$this->version];

		foreach($pattern as $y){
			foreach($pattern as $x){

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
	 * @return \chillerlan\QRCode\Data\QRMatrix
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
	 * @param bool|null  $test
	 *
	 * @return \chillerlan\QRCode\Data\QRMatrix
	 */
	public function setVersionNumber(bool $test = null):QRMatrix{
		$test = $test ?? false;
		$bits = $this::versionPattern[$this->version] ?? false;

		if($bits !== false){

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
	 * @param int        $maskPattern
	 * @param bool|null  $test
	 *
	 * @return \chillerlan\QRCode\Data\QRMatrix
	 */
	public function setFormatInfo(int $maskPattern, bool $test = null):QRMatrix{
		$test = $test ?? false;
		$bits = $this::formatPattern[QRCode::ECC_MODES[$this->eclevel]][$maskPattern] ?? 0;
		$t    = $this::M_FORMAT;

		for($i = 0; $i < 15; $i++){
			$v = !$test && (($bits >> $i) & 1) === 1;

			if($i < 6){
				$this->set(8, $i, $v, $t);
			}
			elseif($i < 8){
				$this->set(8, $i + 1, $v, $t);
			}
			else{
				$this->set(8, $this->moduleCount - 15 + $i, $v, $t);
			}

			if($i < 8){
				$this->set($this->moduleCount - $i - 1, 8, $v, $t);
			}
			elseif($i < 9){
				$this->set(15 - $i, 8, $v, $t);
			}
			else{
				$this->set(15 - $i - 1, 8, $v, $t);
			}

		}

		$this->set(8, $this->moduleCount - 8, !$test, $t);

		return $this;
	}

	/**
	 * Draws the "quiet zone" of $size around the matrix
	 *
	 * @param int|null $size
	 *
	 * @return \chillerlan\QRCode\Data\QRMatrix
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public function setQuietZone(int $size = null):QRMatrix{

		if($this->matrix[$this->moduleCount - 1][$this->moduleCount - 1] === $this::M_NULL){
			throw new QRCodeDataException('use only after writing data');
		}

		$size = $size !== null ? max(0, min($size, floor($this->moduleCount / 2))) : 4;
		$t    = $this::M_QUIETZONE;

		for($y = 0; $y < $this->moduleCount; $y++){
			for($i = 0; $i < $size; $i++){
				array_unshift($this->matrix[$y], $t);
				array_push($this->matrix[$y], $t);
			}
		}

		$this->moduleCount += ($size * 2);
		$r                 = array_fill(0, $this->moduleCount, $t);

		for($i = 0; $i < $size; $i++){
			array_unshift($this->matrix, $r);
			array_push($this->matrix, $r);
		}

		return $this;
	}

	/**
	 * Maps the binary $data array from QRDataInterface::maskECC() on the matrix, using $maskPattern
	 *
	 * @see \chillerlan\QRCode\Data\QRDataAbstract::maskECC()
	 *
	 * @param int[] $data
	 * @param int   $maskPattern
	 *
	 * @return \chillerlan\QRCode\Data\QRMatrix
	 */
	public function mapData(array $data, int $maskPattern):QRMatrix{
		$this->maskPattern = $maskPattern;
		$byteCount         = count($data);
		$size              = $this->moduleCount - 1;

		for($i = $size, $y = $size, $inc = -1, $byteIndex = 0, $bitIndex  = 7; $i > 0; $i -= 2){

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

						if($this->getMask($x, $y, $maskPattern) === 0){
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
	 * @see \chillerlan\QRCode\QRMatrix::mapData()
	 *
	 * @internal
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $maskPattern
	 *
	 * @return int
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	protected function getMask(int $x, int $y, int $maskPattern):int {
		$a = $y + $x;
		$m = $y * $x;

		if($maskPattern >= 0 && $maskPattern < 8){
			// this is literally the same as the stupid switch...
			return [
				$a % 2,
				$y % 2,
				$x % 3,
				$a % 3,
				(floor($y / 2) + floor($x / 3)) % 2,
				$m % 2 + $m % 3,
				($m % 2 + $m % 3) % 2,
				($m % 3 + $a % 2) % 2
			][$maskPattern];
		}

		throw new QRCodeDataException('invalid mask pattern'); // @codeCoverageIgnore
	}

}
