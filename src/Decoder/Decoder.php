<?php
/**
 * Class Decoder
 *
 * @created      17.01.2021
 * @author       ZXing Authors
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2021 Smiley
 * @license      Apache-2.0
 */

namespace chillerlan\QRCode\Decoder;

use chillerlan\QRCode\Common\{BitBuffer, EccLevel, MaskPattern, Mode, ReedSolomonDecoder, Version};
use chillerlan\QRCode\Data\{AlphaNum, Byte, ECI, Hanzi, Kanji, Number};
use chillerlan\QRCode\Detector\Detector;
use Throwable;
use function array_fill, chr, count, mb_convert_encoding, mb_detect_encoding, mb_internal_encoding, str_replace;

/**
 * The main class which implements QR Code decoding -- as opposed to locating and extracting
 * the QR Code from an image.
 *
 * @author Sean Owen
 */
final class Decoder{

	private ?Version     $version = null;
	private ?EccLevel    $eccLevel = null;
	private ?MaskPattern $maskPattern = null;

	/**
	 * Decodes a QR Code represented as a BitMatrix.
	 * A 1 or "true" is taken to mean a black module.
	 *
	 * @throws \Throwable|\chillerlan\QRCode\Decoder\QRCodeDecoderException
	 */
	public function decode(LuminanceSourceInterface $source):DecoderResult{
		$matrix = (new Detector($source))->detect();

		try{
			// clone the BitMatrix to avoid errors in case we run into mirroring
			return $this->decodeMatrix(clone $matrix);
		}
		catch(Throwable $e){

			try{
				/*
				 * Prepare for a mirrored reading.
				 *
				 * Since we're here, this means we have successfully detected some kind
				 * of version and format information when mirrored. This is a good sign,
				 * that the QR code may be mirrored, and we should try once more with a
				 * mirrored content.
				 */
				return $this->decodeMatrix($matrix->setMirror(true)->mirror());
			}
			catch(Throwable $f){
				// Throw the exception from the original reading
				throw $e;
			}

		}

	}

	/**
	 * @throws \chillerlan\QRCode\Decoder\QRCodeDecoderException
	 */
	private function decodeMatrix(BitMatrix $matrix):DecoderResult{
		// Read raw codewords
		$rawCodewords      = $matrix->readCodewords();
		$this->version     = $matrix->version();
		$this->eccLevel    = $matrix->eccLevel();
		$this->maskPattern = $matrix->maskPattern();

		if($this->version === null || $this->eccLevel === null || $this->maskPattern === null){
			throw new QRCodeDecoderException('unable to read version or format info'); // @codeCoverageIgnore
		}

		$resultBytes = (new ReedSolomonDecoder)->decode($this->getDataBlocks($rawCodewords));
		// Decode the contents of that stream of bytes
		return $this->decodeBitStream($resultBytes);
	}

	/**
	 * When QR Codes use multiple data blocks, they are actually interleaved.
	 * That is, the first byte of data block 1 to n is written, then the second bytes, and so on. This
	 * method will separate the data into original blocks.
	 *
	 * @param array $rawCodewords bytes as read directly from the QR Code
	 *
	 * @return array DataBlocks containing original bytes, "de-interleaved" from representation in the QR Code
	 * @throws \chillerlan\QRCode\Decoder\QRCodeDecoderException
	 */
	private function getDataBlocks(array $rawCodewords):array{

		// Figure out the number and size of data blocks used by this version and
		// error correction level
		/** @phan-suppress-next-line PhanTypeMismatchArgumentNullable */
		[$numEccCodewords, $eccBlocks] = $this->version->getRSBlocks($this->eccLevel);

		// Now establish DataBlocks of the appropriate size and number of data codewords
		$result          = [];//new DataBlock[$totalBlocks];
		$numResultBlocks = 0;

		foreach($eccBlocks as $blockData){
			[$numEccBlocks, $eccPerBlock] = $blockData;

			for($i = 0; $i < $numEccBlocks; $i++, $numResultBlocks++){
				$result[$numResultBlocks] = [$eccPerBlock, array_fill(0, $numEccCodewords + $eccPerBlock, 0)];
			}
		}

		// All blocks have the same amount of data, except that the last n
		// (where n may be 0) have 1 more byte. Figure out where these start.
		/** @phan-suppress-next-line PhanTypePossiblyInvalidDimOffset */
		$shorterBlocksTotalCodewords = count($result[0][1]);
		$longerBlocksStartAt         = count($result) - 1;

		while($longerBlocksStartAt >= 0){
			$numCodewords = count($result[$longerBlocksStartAt][1]);

			if($numCodewords == $shorterBlocksTotalCodewords){
				break;
			}

			$longerBlocksStartAt--;
		}

		$longerBlocksStartAt++;

		$shorterBlocksNumDataCodewords = $shorterBlocksTotalCodewords - $numEccCodewords;
		// The last elements of result may be 1 element longer;
		// first fill out as many elements as all of them have
		$rawCodewordsOffset = 0;

		for($i = 0; $i < $shorterBlocksNumDataCodewords; $i++){
			for($j = 0; $j < $numResultBlocks; $j++){
				$result[$j][1][$i] = $rawCodewords[$rawCodewordsOffset++];
			}
		}

		// Fill out the last data block in the longer ones
		for($j = $longerBlocksStartAt; $j < $numResultBlocks; $j++){
			$result[$j][1][$shorterBlocksNumDataCodewords] = $rawCodewords[$rawCodewordsOffset++];
		}

		// Now add in error correction blocks
		/** @phan-suppress-next-line PhanTypePossiblyInvalidDimOffset */
		$max = count($result[0][1]);

		for($i = $shorterBlocksNumDataCodewords; $i < $max; $i++){
			for($j = 0; $j < $numResultBlocks; $j++){
				$iOffset                 = $j < $longerBlocksStartAt ? $i : $i + 1;
				$result[$j][1][$iOffset] = $rawCodewords[$rawCodewordsOffset++];
			}
		}

		return $result;
	}

	/**
	 * @throws \chillerlan\QRCode\Decoder\QRCodeDecoderException
	 */
	private function decodeBitStream(array $bytes):DecoderResult{
		$bitBuffer      = new BitBuffer($bytes);
		$symbolSequence = -1;
		$parityData     = -1;
		$versionNumber  = $this->version->getVersionNumber();
		$eciCharset     = null;
		$fc1InEffect    = false;
		$result         = '';

		// While still another segment to read...
		while($bitBuffer->available() >= 4){
			$datamode = $bitBuffer->read(4); // mode is encoded by 4 bits

			// OK, assume we're done. Really, a TERMINATOR mode should have been recorded here
			if($datamode === Mode::TERMINATOR){
				break;
			}
			elseif($datamode === Mode::ECI){
				$eciCharset = ECI::parseValue($bitBuffer);
			}
			elseif($datamode === Mode::FNC1_FIRST || $datamode === Mode::FNC1_SECOND){
				// We do little with FNC1 except alter the parsed result a bit according to the spec
				$fc1InEffect = true;
			}
			elseif($datamode === Mode::STRCTURED_APPEND){

				if($bitBuffer->available() < 16){
					throw new QRCodeDecoderException('structured append: not enough bits left');
				}
				// sequence number and parity is added later to the result metadata
				// Read next 8 bits (symbol sequence #) and 8 bits (parity data), then continue
				$symbolSequence = $bitBuffer->read(8);
				$parityData     = $bitBuffer->read(8);
			}
			elseif($datamode === Mode::NUMBER){
				$result .= Number::decodeSegment($bitBuffer, $versionNumber);
			}
			elseif($datamode === Mode::ALPHANUM){
				$str = AlphaNum::decodeSegment($bitBuffer, $versionNumber);

				// See section 6.4.8.1, 6.4.8.2
				if($fc1InEffect){ // ???
					// We need to massage the result a bit if in an FNC1 mode:
					$str = str_replace(chr(0x1d), '%', $str);
					$str = str_replace('%%', '%', $str);
				}

				$result .= $str;
			}
			elseif($datamode === Mode::BYTE){
				$str = Byte::decodeSegment($bitBuffer, $versionNumber);

				if($eciCharset !== null){
					$encoding = $eciCharset->getName();

					if($encoding === null){
						// The spec isn't clear on this mode; see
						// section 6.4.5: t does not say which encoding to assuming
						// upon decoding. I have seen ISO-8859-1 used as well as
						// Shift_JIS -- without anything like an ECI designator to
						// give a hint.
						$encoding = mb_detect_encoding($str, ['ISO-8859-1', 'Windows-1252', 'SJIS', 'UTF-8'], true);

						if($encoding === false){
							throw new QRCodeDecoderException('could not determine encoding in ECI mode');
						}
					}

					$eciCharset = null;
					$str = mb_convert_encoding($str, mb_internal_encoding(), $encoding);
				}

				$result .= $str;
			}
			elseif($datamode === Mode::KANJI){
				$result .= Kanji::decodeSegment($bitBuffer, $versionNumber);
			}
			elseif($datamode === Mode::HANZI){
				// Hanzi mode contains a subset indicator right after mode indicator
				if($bitBuffer->read(4) !== Hanzi::GB2312_SUBSET){
					throw new QRCodeDecoderException('ecpected subset indicator for Hanzi mode');
				}

				$result .= Hanzi::decodeSegment($bitBuffer, $versionNumber);
			}
			else{
				throw new QRCodeDecoderException('invalid data mode');
			}

		}

		return new DecoderResult([
			'rawBytes'                 => $bytes,
			'data'                     => $result,
			'version'                  => $this->version,
			'eccLevel'                 => $this->eccLevel,
			'maskPattern'              => $this->maskPattern,
			'structuredAppendParity'   => $parityData,
			'structuredAppendSequence' => $symbolSequence
		]);
	}

}
