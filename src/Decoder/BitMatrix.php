<?php
/**
 * Class BitMatrix
 *
 * @created      17.01.2021
 * @author       ZXing Authors
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2021 Smiley
 * @license      Apache-2.0
 */

namespace chillerlan\QRCode\Decoder;

use chillerlan\QRCode\Common\{MaskPattern, Version};
use InvalidArgumentException;
use function chillerlan\QRCode\Common\uRShift;
use function array_fill, count;

final class BitMatrix{

	private int   $dimension;
	private int   $rowSize;
	private array $bits;

	public function __construct(int $dimension){
		$this->dimension = $dimension;
		$this->rowSize   = ((int)(($this->dimension + 0x1f) / 0x20));
		$this->bits      = array_fill(0, $this->rowSize * $this->dimension, 0);
	}

	/**
	 * <p>Sets the given bit to true.</p>
	 *
	 * @param int $x ;  The horizontal component (i.e. which column)
	 * @param int $y ;  The vertical component (i.e. which row)
	 */
	public function set(int $x, int $y):void{
		$offset = (int)($y * $this->rowSize + ($x / 0x20));

		$this->bits[$offset] ??= 0;
		$this->bits[$offset] |= ($this->bits[$offset] |= 1 << ($x & 0x1f));
	}

	/**
	 * <p>Flips the given bit. 1 << (0xf9 & 0x1f)</p>
	 *
	 * @param int $x ;  The horizontal component (i.e. which column)
	 * @param int $y ;  The vertical component (i.e. which row)
	 */
	public function flip(int $x, int $y):void{
		$offset = $y * $this->rowSize + (int)($x / 0x20);

		$this->bits[$offset] = ($this->bits[$offset] ^ (1 << ($x & 0x1f)));
	}

	/**
	 * <p>Sets a square region of the bit matrix to true.</p>
	 *
	 * @param int $left   ;  The horizontal position to begin at (inclusive)
	 * @param int $top    ;  The vertical position to begin at (inclusive)
	 * @param int $width  ;  The width of the region
	 * @param int $height ;  The height of the region
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setRegion(int $left, int $top, int $width, int $height):void{

		if($top < 0 || $left < 0){
			throw new InvalidArgumentException('Left and top must be nonnegative');
		}

		if($height < 1 || $width < 1){
			throw new InvalidArgumentException('Height and width must be at least 1');
		}

		$right  = $left + $width;
		$bottom = $top + $height;

		if($bottom > $this->dimension || $right > $this->dimension){
			throw new InvalidArgumentException('The region must fit inside the matrix');
		}

		for($y = $top; $y < $bottom; $y++){
			$yOffset = $y * $this->rowSize;

			for($x = $left; $x < $right; $x++){
				$xOffset              = $yOffset + (int)($x / 0x20);
				$this->bits[$xOffset] = ($this->bits[$xOffset] |= 1 << ($x & 0x1f));
			}
		}
	}

	/**
	 * @return int The dimension (width/height) of the matrix
	 */
	public function getDimension():int{
		return $this->dimension;
	}

	/**
	 * <p>Gets the requested bit, where true means black.</p>
	 *
	 * @param int $x The horizontal component (i.e. which column)
	 * @param int $y The vertical component (i.e. which row)
	 *
	 * @return bool value of given bit in matrix
	 */
	public function get(int $x, int $y):bool{
		$offset = (int)($y * $this->rowSize + ($x / 0x20));

		$this->bits[$offset] ??= 0;

		return (uRShift($this->bits[$offset], ($x & 0x1f)) & 1) !== 0;
	}

	/**
	 * See ISO 18004:2006 Annex E
	 */
	public function buildFunctionPattern(Version $version):BitMatrix{
		$dimension = $version->getDimension();
		// @todo
		$bitMatrix = new self($dimension);

		// Top left finder pattern + separator + format
		$bitMatrix->setRegion(0, 0, 9, 9);
		// Top right finder pattern + separator + format
		$bitMatrix->setRegion($dimension - 8, 0, 8, 9);
		// Bottom left finder pattern + separator + format
		$bitMatrix->setRegion(0, $dimension - 8, 9, 8);

		// Alignment patterns
		$apc = $version->getAlignmentPattern();
		$max = count($apc);

		for($x = 0; $x < $max; $x++){
			$i = $apc[$x] - 2;

			for($y = 0; $y < $max; $y++){
				if(($x === 0 && ($y === 0 || $y === $max - 1)) || ($x === $max - 1 && $y === 0)){
					// No alignment patterns near the three finder paterns
					continue;
				}

				$bitMatrix->setRegion($apc[$y] - 2, $i, 5, 5);
			}
		}

		// Vertical timing pattern
		$bitMatrix->setRegion(6, 9, 1, $dimension - 17);
		// Horizontal timing pattern
		$bitMatrix->setRegion(9, 6, $dimension - 17, 1);

		if($version->getVersionNumber() > 6){
			// Version info, top right
			$bitMatrix->setRegion($dimension - 11, 0, 3, 6);
			// Version info, bottom left
			$bitMatrix->setRegion(0, $dimension - 11, 6, 3);
		}

		return $bitMatrix;
	}

	/**
	 * Mirror the bit matrix in order to attempt a second reading.
	 */
	public function mirror():void{

		for($x = 0; $x < $this->dimension; $x++){
			for($y = $x + 1; $y < $this->dimension; $y++){
				if($this->get($x, $y) !== $this->get($y, $x)){
					$this->flip($y, $x);
					$this->flip($x, $y);
				}
			}
		}

	}

	/**
	 * <p>Encapsulates data masks for the data bits in a QR code, per ISO 18004:2006 6.8. Implementations
	 * of this class can un-mask a raw BitMatrix. For simplicity, they will unmask the entire BitMatrix,
	 * including areas used for finder patterns, timing patterns, etc. These areas should be unused
	 * after the point they are unmasked anyway.</p>
	 *
	 * <p>Note that the diagram in section 6.8.1 is misleading since it indicates that i is column position
	 * and j is row position. In fact, as the text says, i is row position and j is column position.</p>
	 *
	 * <p>Implementations of this method reverse the data masking process applied to a QR Code and
	 * make its bits ready to read.</p>
	 */
	public function unmask(int $dimension, MaskPattern $maskPattern):void{
		$mask = $maskPattern->getMask();

		for($y = 0; $y < $dimension; $y++){
			for($x = 0; $x < $dimension; $x++){
				if($mask($x, $y) === 0){
					$this->flip($x, $y);
				}
			}
		}

	}

}
