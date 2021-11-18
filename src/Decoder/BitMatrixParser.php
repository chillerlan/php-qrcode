<?php
/**
 * Class BitMatrixParser
 *
 * @created      17.01.2021
 * @author       ZXing Authors
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2021 Smiley
 * @license      Apache-2.0
 */

namespace chillerlan\QRCode\Decoder;

use RuntimeException;
use chillerlan\QRCode\Common\{Version, FormatInformation};
use const PHP_INT_MAX, PHP_INT_SIZE;

/**
 * @author Sean Owen
 */
final class BitMatrixParser{

	private BitMatrix          $bitMatrix;
	private ?Version           $parsedVersion    = null;
	private ?FormatInformation $parsedFormatInfo = null;
	private bool               $mirror           = false;

	/**
	 * @param \chillerlan\QRCode\Decoder\BitMatrix $bitMatrix
	 *
	 * @throws \RuntimeException if dimension is not >= 21 and 1 mod 4
	 */
	public function __construct(BitMatrix $bitMatrix){
		$dimension = $bitMatrix->getDimension();

		if($dimension < 21 || ($dimension % 4) !== 1){
			throw new RuntimeException('dimension is not >= 21, dimension mod 4 not 1');
		}

		$this->bitMatrix = $bitMatrix;
	}

	/**
	 * Prepare the parser for a mirrored operation.
	 * This flag has effect only on the {@link #readFormatInformation()} and the
	 * {@link #readVersion()}. Before proceeding with {@link #readCodewords()} the
	 * {@link #mirror()} method should be called.
	 *
	 * @param bool $mirror Whether to read version and format information mirrored.
	 */
	public function setMirror(bool $mirror):void{
		$this->parsedVersion    = null;
		$this->parsedFormatInfo = null;
		$this->mirror           = $mirror;
	}

	/**
	 * Mirror the bit matrix in order to attempt a second reading.
	 */
	public function mirror():void{
		$this->bitMatrix->mirror();
	}

	/**
	 *
	 */
	private function copyBit(int $i, int $j, int $versionBits):int{

		$bit = $this->mirror
			? $this->bitMatrix->get($j, $i)
			: $this->bitMatrix->get($i, $j);

		return $bit ? ($versionBits << 1) | 0x1 : $versionBits << 1;
	}

	/**
	 * <p>Reads the bits in the {@link BitMatrix} representing the finder pattern in the
	 * correct order in order to reconstruct the codewords bytes contained within the
	 * QR Code.</p>
	 *
	 * @return array bytes encoded within the QR Code
	 * @throws \RuntimeException if the exact number of bytes expected is not read
	 */
	public function readCodewords():array{
		$formatInfo = $this->readFormatInformation();
		$version    = $this->readVersion();

		// Get the data mask for the format used in this QR Code. This will exclude
		// some bits from reading as we wind through the bit matrix.
		$dimension = $this->bitMatrix->getDimension();
		$this->bitMatrix->unmask($dimension, $formatInfo->getDataMask());
		$functionPattern = $this->bitMatrix->buildFunctionPattern($version);

		$readingUp    = true;
		$result       = [];
		$resultOffset = 0;
		$currentByte  = 0;
		$bitsRead     = 0;
		// Read columns in pairs, from right to left
		for($j = $dimension - 1; $j > 0; $j -= 2){

			if($j === 6){
				// Skip whole column with vertical alignment pattern;
				// saves time and makes the other code proceed more cleanly
				$j--;
			}
			// Read alternatingly from bottom to top then top to bottom
			for($count = 0; $count < $dimension; $count++){
				$i = $readingUp ? $dimension - 1 - $count : $count;

				for($col = 0; $col < 2; $col++){
					// Ignore bits covered by the function pattern
					if(!$functionPattern->get($j - $col, $i)){
						// Read a bit
						$bitsRead++;
						$currentByte <<= 1;

						if($this->bitMatrix->get($j - $col, $i)){
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

		if($resultOffset !== $version->getTotalCodewords()){
			throw new RuntimeException('offset differs from total codewords for version');
		}

		return $result;
	}

	/**
	 * <p>Reads format information from one of its two locations within the QR Code.</p>
	 *
	 * @return \chillerlan\QRCode\Common\FormatInformation encapsulating the QR Code's format info
	 * @throws \RuntimeException                           if both format information locations cannot be parsed as
	 *                                                     the valid encoding of format information
	 */
	public function readFormatInformation():FormatInformation{

		if($this->parsedFormatInfo !== null){
			return $this->parsedFormatInfo;
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
		$dimension       = $this->bitMatrix->getDimension();
		$formatInfoBits2 = 0;
		$jMin            = $dimension - 7;

		for($j = $dimension - 1; $j >= $jMin; $j--){
			$formatInfoBits2 = $this->copyBit(8, $j, $formatInfoBits2);
		}

		for($i = $dimension - 8; $i < $dimension; $i++){
			$formatInfoBits2 = $this->copyBit($i, 8, $formatInfoBits2);
		}

		$this->parsedFormatInfo = $this->doDecodeFormatInformation($formatInfoBits1, $formatInfoBits2);

		if($this->parsedFormatInfo !== null){
			return $this->parsedFormatInfo;
		}

		// Should return null, but, some QR codes apparently do not mask this info.
		// Try again by actually masking the pattern first.
		$this->parsedFormatInfo = $this->doDecodeFormatInformation(
			$formatInfoBits1 ^ FormatInformation::MASK_QR,
			$formatInfoBits2 ^ FormatInformation::MASK_QR
		);

		if($this->parsedFormatInfo !== null){
			return $this->parsedFormatInfo;
		}

		throw new RuntimeException('failed to read format info');
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

			$bitsDifference = self::numBitsDiffering($maskedFormatInfo1, $dataBits);

			if($bitsDifference < $bestDifference){
				$bestFormatInfo = $maskedBits;
				$bestDifference = $bitsDifference;
			}

			if($maskedFormatInfo1 !== $maskedFormatInfo2){
				// also try the other option
				$bitsDifference = self::numBitsDiffering($maskedFormatInfo2, $dataBits);

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
	 * <p>Reads version information from one of its two locations within the QR Code.</p>
	 *
	 * @return \chillerlan\QRCode\Common\Version encapsulating the QR Code's version
	 * @throws \RuntimeException                 if both version information locations cannot be parsed as
	 *                                           the valid encoding of version information
	 */
	public function readVersion():Version{

		if($this->parsedVersion !== null){
			return $this->parsedVersion;
		}

		$dimension          = $this->bitMatrix->getDimension();
		$provisionalVersion = ($dimension - 17) / 4;

		if($provisionalVersion <= 6){
			return new Version($provisionalVersion);
		}

		// Read top-right version info: 3 wide by 6 tall
		$versionBits = 0;
		$ijMin       = $dimension - 11;

		for($j = 5; $j >= 0; $j--){
			for($i = $dimension - 9; $i >= $ijMin; $i--){
				$versionBits = $this->copyBit($i, $j, $versionBits);
			}
		}

		$this->parsedVersion = $this->decodeVersionInformation($versionBits);

		if($this->parsedVersion !== null && $this->parsedVersion->getDimension() === $dimension){
			return $this->parsedVersion;
		}

		// Hmm, failed. Try bottom left: 6 wide by 3 tall
		$versionBits = 0;

		for($i = 5; $i >= 0; $i--){
			for($j = $dimension - 9; $j >= $ijMin; $j--){
				$versionBits = $this->copyBit($i, $j, $versionBits);
			}
		}

		$this->parsedVersion = $this->decodeVersionInformation($versionBits);

		if($this->parsedVersion !== null && $this->parsedVersion->getDimension() === $dimension){
			return $this->parsedVersion;
		}

		throw new RuntimeException('failed to read version');
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
			$bitsDifference = self::numBitsDiffering($versionBits, $targetVersionPattern);

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
	public static function uRShift(int $a, int $b):int{

		if($b === 0){
			return $a;
		}

		return ($a >> $b) & ~((1 << (8 * PHP_INT_SIZE - 1)) >> ($b - 1));
	}

	/**
	 *
	 */
	private static function numBitsDiffering(int $a, int $b):int{
		// a now has a 1 bit exactly where its bit differs with b's
		$a ^= $b;
		// Offset i holds the number of 1 bits in the binary representation of i
		$BITS_SET_IN_HALF_BYTE = [0, 1, 1, 2, 1, 2, 2, 3, 1, 2, 2, 3, 2, 3, 3, 4];
		// Count bits set quickly with a series of lookups:
		$count = 0;

		for($i = 0; $i < 32; $i += 4){
			$count += $BITS_SET_IN_HALF_BYTE[self::uRShift($a, $i) & 0x0F];
		}

		return $count;
	}

}
