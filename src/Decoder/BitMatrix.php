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

use chillerlan\QRCode\Common\{FormatInformation, Version};
use function array_fill, count;
use const PHP_INT_MAX, PHP_INT_SIZE;

/**
 *
 */
final class BitMatrix{

	private int                $dimension;
	private int                $rowSize;
	private array              $bits;
	private ?Version           $version    = null;
	private ?FormatInformation $formatInfo = null;
	private bool               $mirror     = false;

	/**
	 *
	 */
	public function __construct(int $dimension){
		$this->dimension = $dimension;
		$this->rowSize   = ((int)(($this->dimension + 0x1f) / 0x20));
		$this->bits      = array_fill(0, $this->rowSize * $this->dimension, 0);
	}

	/**
	 * Sets the given bit to true.
	 *
	 * @param int $x ;  The horizontal component (i.e. which column)
	 * @param int $y ;  The vertical component (i.e. which row)
	 */
	public function set(int $x, int $y):self{
		$offset = (int)($y * $this->rowSize + ($x / 0x20));

		$this->bits[$offset] ??= 0;
		$this->bits[$offset] |= ($this->bits[$offset] |= 1 << ($x & 0x1f));

		return $this;
	}

	/**
	 * Flips the given bit. 1 << (0xf9 & 0x1f)
	 *
	 * @param int $x ;  The horizontal component (i.e. which column)
	 * @param int $y ;  The vertical component (i.e. which row)
	 */
	public function flip(int $x, int $y):self{
		$offset = $y * $this->rowSize + (int)($x / 0x20);

		$this->bits[$offset] = ($this->bits[$offset] ^ (1 << ($x & 0x1f)));

		return $this;
	}

	/**
	 * Sets a square region of the bit matrix to true.
	 *
	 * @param int $left   ;  The horizontal position to begin at (inclusive)
	 * @param int $top    ;  The vertical position to begin at (inclusive)
	 * @param int $width  ;  The width of the region
	 * @param int $height ;  The height of the region
	 *
	 * @throws \chillerlan\QRCode\Decoder\QRCodeDecoderException
	 */
	public function setRegion(int $left, int $top, int $width, int $height):self{

		if($top < 0 || $left < 0){
			throw new QRCodeDecoderException('Left and top must be non-negative');
		}

		if($height < 1 || $width < 1){
			throw new QRCodeDecoderException('Height and width must be at least 1');
		}

		$right  = $left + $width;
		$bottom = $top + $height;

		if($bottom > $this->dimension || $right > $this->dimension){
			throw new QRCodeDecoderException('The region must fit inside the matrix');
		}

		for($y = $top; $y < $bottom; $y++){
			$yOffset = $y * $this->rowSize;

			for($x = $left; $x < $right; $x++){
				$xOffset              = $yOffset + (int)($x / 0x20);
				$this->bits[$xOffset] = ($this->bits[$xOffset] |= 1 << ($x & 0x1f));
			}
		}

		return $this;
	}

	/**
	 * @return int The dimension (width/height) of the matrix
	 */
	public function getDimension():int{
		return $this->dimension;
	}

	/**
	 *
	 */
	public function getFormatInfo():?FormatInformation{
		return $this->formatInfo;
	}

	/**
	 *
	 */
	public function getVersion():?Version{
		return $this->version;
	}

	/**
	 * Gets the requested bit, where true means black.
	 *
	 * @param int $x The horizontal component (i.e. which column)
	 * @param int $y The vertical component (i.e. which row)
	 *
	 * @return bool value of given bit in matrix
	 */
	public function get(int $x, int $y):bool{
		$offset = (int)($y * $this->rowSize + ($x / 0x20));

		$this->bits[$offset] ??= 0;

		return ($this->uRShift($this->bits[$offset], ($x & 0x1f)) & 1) !== 0;
	}

	/**
	 * See ISO 18004:2006 Annex E
	 */
	private function buildFunctionPattern():self{
		$dimension = $this->version->getDimension();
		$bitMatrix = new self($dimension);

		// Top left finder pattern + separator + format
		$bitMatrix->setRegion(0, 0, 9, 9);
		// Top right finder pattern + separator + format
		$bitMatrix->setRegion($dimension - 8, 0, 8, 9);
		// Bottom left finder pattern + separator + format
		$bitMatrix->setRegion(0, $dimension - 8, 9, 8);

		// Alignment patterns
		$apc = $this->version->getAlignmentPattern();
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

		if($this->version->getVersionNumber() > 6){
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
	public function mirror():self{

		for($x = 0; $x < $this->dimension; $x++){
			for($y = $x + 1; $y < $this->dimension; $y++){
				if($this->get($x, $y) !== $this->get($y, $x)){
					$this->flip($y, $x);
					$this->flip($x, $y);
				}
			}
		}

		return $this;
	}

	/**
	 * Implementations of this method reverse the data masking process applied to a QR Code and
	 * make its bits ready to read.
	 */
	private function unmask():void{
		$mask = $this->formatInfo->getMaskPattern()->getMask();

		for($y = 0; $y < $this->dimension; $y++){
			for($x = 0; $x < $this->dimension; $x++){
				if($mask($x, $y)){
					$this->flip($x, $y);
				}
			}
		}

	}

	/**
	 * Prepare the parser for a mirrored operation.
	 * This flag has effect only on the readFormatInformation() and the
	 * readVersion() methods. Before proceeding with readCodewords() the
	 * mirror() method should be called.
	 *
	 * @param bool $mirror Whether to read version and format information mirrored.
	 */
	public function setMirror(bool $mirror):self{
		$this->version    = null;
		$this->formatInfo = null;
		$this->mirror     = $mirror;

		return $this;
	}

	/**
	 *
	 */
	private function copyBit(int $i, int $j, int $versionBits):int{

		$bit = $this->mirror
			? $this->get($j, $i)
			: $this->get($i, $j);

		return $bit ? ($versionBits << 1) | 0x1 : $versionBits << 1;
	}

	/**
	 * Reads the bits in the BitMatrix representing the finder pattern in the
	 * correct order in order to reconstruct the codewords bytes contained within the
	 * QR Code.
	 *
	 * @return array bytes encoded within the QR Code
	 * @throws \chillerlan\QRCode\Decoder\QRCodeDecoderException if the exact number of bytes expected is not read
	 */
	public function readCodewords():array{
		$this->formatInfo = $this->readFormatInformation();
		$this->version    = $this->readVersion();

		// Get the data mask for the format used in this QR Code. This will exclude
		// some bits from reading as we wind through the bit matrix.
		$this->unmask();
		$functionPattern = $this->buildFunctionPattern();

		$readingUp    = true;
		$result       = [];
		$resultOffset = 0;
		$currentByte  = 0;
		$bitsRead     = 0;
		// Read columns in pairs, from right to left
		for($j = $this->dimension - 1; $j > 0; $j -= 2){

			if($j === 6){
				// Skip whole column with vertical alignment pattern;
				// saves time and makes the other code proceed more cleanly
				$j--;
			}
			// Read alternatingly from bottom to top then top to bottom
			for($count = 0; $count < $this->dimension; $count++){
				$i = $readingUp ? $this->dimension - 1 - $count : $count;

				for($col = 0; $col < 2; $col++){
					// Ignore bits covered by the function pattern
					if(!$functionPattern->get($j - $col, $i)){
						// Read a bit
						$bitsRead++;
						$currentByte <<= 1;

						if($this->get($j - $col, $i)){
							$currentByte |= 1;
						}
						// If we've made a whole byte, save it off
						if($bitsRead === 8){
							$result[$resultOffset++] = $currentByte; //(byte)
							$bitsRead                = 0;
							$currentByte             = 0;
						}
					}
				}
			}

			$readingUp = !$readingUp; // switch directions
		}

		if($resultOffset !== $this->version->getTotalCodewords()){
			throw new QRCodeDecoderException('offset differs from total codewords for version');
		}

		return $result;
	}

	/**
	 * Reads format information from one of its two locations within the QR Code.
	 *
	 * @return \chillerlan\QRCode\Common\FormatInformation       encapsulating the QR Code's format info
	 * @throws \chillerlan\QRCode\Decoder\QRCodeDecoderException if both format information locations cannot be parsed as
	 *                                                           the valid encoding of format information
	 */
	private function readFormatInformation():FormatInformation{

		if($this->formatInfo !== null){
			return $this->formatInfo;
		}

		// Read top-left format info bits
		$formatInfoBits1 = 0;

		for($i = 0; $i < 6; $i++){
			$formatInfoBits1 = $this->copyBit($i, 8, $formatInfoBits1);
		}

		// .. and skip a bit in the timing pattern ...
		$formatInfoBits1 = $this->copyBit(7, 8, $formatInfoBits1);
		$formatInfoBits1 = $this->copyBit(8, 8, $formatInfoBits1);
		$formatInfoBits1 = $this->copyBit(8, 7, $formatInfoBits1);
		// .. and skip a bit in the timing pattern ...
		for($j = 5; $j >= 0; $j--){
			$formatInfoBits1 = $this->copyBit(8, $j, $formatInfoBits1);
		}

		// Read the top-right/bottom-left pattern too
		$formatInfoBits2 = 0;
		$jMin            = $this->dimension - 7;

		for($j = $this->dimension - 1; $j >= $jMin; $j--){
			$formatInfoBits2 = $this->copyBit(8, $j, $formatInfoBits2);
		}

		for($i = $this->dimension - 8; $i < $this->dimension; $i++){
			$formatInfoBits2 = $this->copyBit($i, 8, $formatInfoBits2);
		}

		$this->formatInfo = $this->doDecodeFormatInformation($formatInfoBits1, $formatInfoBits2);

		if($this->formatInfo !== null){
			return $this->formatInfo;
		}

		// Should return null, but, some QR codes apparently do not mask this info.
		// Try again by actually masking the pattern first.
		$this->formatInfo = $this->doDecodeFormatInformation(
			$formatInfoBits1 ^ FormatInformation::FORMAT_INFO_MASK_QR,
			$formatInfoBits2 ^ FormatInformation::FORMAT_INFO_MASK_QR
		);

		if($this->formatInfo !== null){
			return $this->formatInfo;
		}

		throw new QRCodeDecoderException('failed to read format info');
	}

	/**
	 * @param int $maskedFormatInfo1 format info indicator, with mask still applied
	 * @param int $maskedFormatInfo2 second copy of same info; both are checked at the same time
	 *                               to establish best match
	 *
	 * @return \chillerlan\QRCode\Common\FormatInformation|null information about the format it specifies, or null
	 *                                                          if doesn't seem to match any known pattern
	 */
	private function doDecodeFormatInformation(int $maskedFormatInfo1, int $maskedFormatInfo2):?FormatInformation{
		// Find the int in FORMAT_INFO_DECODE_LOOKUP with fewest bits differing
		$bestDifference = PHP_INT_MAX;
		$bestFormatInfo = 0;

		foreach(FormatInformation::DECODE_LOOKUP as $decodeInfo){
			[$maskedBits, $dataBits] = $decodeInfo;

			if($maskedFormatInfo1 === $dataBits || $maskedFormatInfo2 === $dataBits){
				// Found an exact match
				return new FormatInformation($maskedBits);
			}

			$bitsDifference = $this->numBitsDiffering($maskedFormatInfo1, $dataBits);

			if($bitsDifference < $bestDifference){
				$bestFormatInfo = $maskedBits;
				$bestDifference = $bitsDifference;
			}

			if($maskedFormatInfo1 !== $maskedFormatInfo2){
				// also try the other option
				$bitsDifference = $this->numBitsDiffering($maskedFormatInfo2, $dataBits);

				if($bitsDifference < $bestDifference){
					$bestFormatInfo = $maskedBits;
					$bestDifference = $bitsDifference;
				}
			}
		}
		// Hamming distance of the 32 masked codes is 7, by construction, so <= 3 bits differing means we found a match
		if($bestDifference <= 3){
			return new FormatInformation($bestFormatInfo);
		}

		return null;
	}

	/**
	 * Reads version information from one of its two locations within the QR Code.
	 *
	 * @return \chillerlan\QRCode\Common\Version                 encapsulating the QR Code's version
	 * @throws \chillerlan\QRCode\Decoder\QRCodeDecoderException if both version information locations cannot be parsed as
	 *                                                           the valid encoding of version information
	 * @noinspection DuplicatedCode
	 */
	private function readVersion():Version{

		if($this->version !== null){
			return $this->version;
		}

		$provisionalVersion = ($this->dimension - 17) / 4;

		if($provisionalVersion <= 6){
			return new Version($provisionalVersion);
		}

		// Read top-right version info: 3 wide by 6 tall
		$versionBits = 0;
		$ijMin       = $this->dimension - 11;

		for($j = 5; $j >= 0; $j--){
			for($i = $this->dimension - 9; $i >= $ijMin; $i--){
				$versionBits = $this->copyBit($i, $j, $versionBits);
			}
		}

		$this->version = $this->decodeVersionInformation($versionBits);

		if($this->version !== null && $this->version->getDimension() === $this->dimension){
			return $this->version;
		}

		// Hmm, failed. Try bottom left: 6 wide by 3 tall
		$versionBits = 0;

		for($i = 5; $i >= 0; $i--){
			for($j = $this->dimension - 9; $j >= $ijMin; $j--){
				$versionBits = $this->copyBit($i, $j, $versionBits);
			}
		}

		$this->version = $this->decodeVersionInformation($versionBits);

		if($this->version !== null && $this->version->getDimension() === $this->dimension){
			return $this->version;
		}

		throw new QRCodeDecoderException('failed to read version');
	}

	/**
	 * @param int $versionBits
	 *
	 * @return \chillerlan\QRCode\Common\Version|null
	 */
	private function decodeVersionInformation(int $versionBits):?Version{
		$bestDifference = PHP_INT_MAX;
		$bestVersion    = 0;

		for($i = 7; $i <= 40; $i++){
			$targetVersion        = new Version($i);
			$targetVersionPattern = $targetVersion->getVersionPattern();

			// Do the version info bits match exactly? done.
			if($targetVersionPattern === $versionBits){
				return $targetVersion;
			}

			// Otherwise see if this is the closest to a real version info bit string
			// we have seen so far
			/** @phan-suppress-next-line PhanTypeMismatchArgumentNullable ($targetVersionPattern is never null here) */
			$bitsDifference = $this->numBitsDiffering($versionBits, $targetVersionPattern);

			if($bitsDifference < $bestDifference){
				$bestVersion    = $i;
				$bestDifference = $bitsDifference;
			}
		}
		// We can tolerate up to 3 bits of error since no two version info codewords will
		// differ in less than 8 bits.
		if($bestDifference <= 3){
			return new Version($bestVersion);
		}

		// If we didn't find a close enough match, fail
		return null;
	}

	/**
	 *
	 */
	private function uRShift(int $a, int $b):int{

		if($b === 0){
			return $a;
		}

		return ($a >> $b) & ~((1 << (8 * PHP_INT_SIZE - 1)) >> ($b - 1));
	}

	/**
	 *
	 */
	private function numBitsDiffering(int $a, int $b):int{
		// a now has a 1 bit exactly where its bit differs with b's
		$a ^= $b;
		// Offset i holds the number of 1 bits in the binary representation of i
		$BITS_SET_IN_HALF_BYTE = [0, 1, 1, 2, 1, 2, 2, 3, 1, 2, 2, 3, 2, 3, 3, 4];
		// Count bits set quickly with a series of lookups:
		$count = 0;

		for($i = 0; $i < 32; $i += 4){
			$count += $BITS_SET_IN_HALF_BYTE[$this->uRShift($a, $i) & 0x0F];
		}

		return $count;
	}

}
