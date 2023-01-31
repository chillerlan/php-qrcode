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
use function chr, mb_convert_encoding, mb_detect_encoding, mb_internal_encoding, str_replace;

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

		$resultBytes = (new ReedSolomonDecoder($this->version, $this->eccLevel))->decode($rawCodewords);

		return $this->decodeBitStream($resultBytes);
	}

	/**
	 * Decode the contents of that stream of bytes
	 *
	 * @throws \chillerlan\QRCode\Decoder\QRCodeDecoderException
	 */
	private function decodeBitStream(BitBuffer $bitBuffer):DecoderResult{
		$versionNumber  = $this->version->getVersionNumber();
		$symbolSequence = -1;
		$parityData     = -1;
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
			'rawBytes'                 => $bitBuffer,
			'data'                     => $result,
			'version'                  => $this->version,
			'eccLevel'                 => $this->eccLevel,
			'maskPattern'              => $this->maskPattern,
			'structuredAppendParity'   => $parityData,
			'structuredAppendSequence' => $symbolSequence
		]);
	}

}
