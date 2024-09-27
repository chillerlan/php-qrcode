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
declare(strict_types=1);

namespace chillerlan\QRCode\Decoder;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\{BitBuffer, EccLevel, LuminanceSourceInterface, MaskPattern, Mode, Version};
use chillerlan\QRCode\Data\{AlphaNum, Byte, ECI, Hanzi, Kanji, Number};
use chillerlan\QRCode\Detector\Detector;
use chillerlan\Settings\SettingsContainerInterface;
use Throwable;
use function chr, str_replace;

/**
 * The main class which implements QR Code decoding -- as opposed to locating and extracting
 * the QR Code from an image.
 *
 * @author Sean Owen
 */
final class Decoder{

	private SettingsContainerInterface|QROptions   $options;
	private Version|null                           $version     = null;
	private EccLevel|null                          $eccLevel    = null;
	private MaskPattern|null                       $maskPattern = null;
	private BitBuffer                              $bitBuffer;
	private int $topLeftX;
	private int $topLeftY;
	private int $topRightX;
	private int $topRightY;
	private int $bottomLeftX;
	private int $bottomLeftY;

	public function __construct(SettingsContainerInterface|QROptions $options = new QROptions){
		$this->options = $options;
	}

	/**
	 * Decodes a QR Code represented as a BitMatrix.
	 * A 1 or "true" is taken to mean a black module.
	 *
	 * @throws \Throwable|\chillerlan\QRCode\Decoder\QRCodeDecoderException
	 */
	public function decode(LuminanceSourceInterface $source):DecoderResult{
		$detector = new Detector($source);
		$matrix = $detector->detect();
		$this->topLeftX = $detector->topLeftX;
		$this->topLeftY = $detector->topLeftY;
		$this->topRightX = $detector->topRightX;
		$this->topRightY = $detector->topRightY;
		$this->bottomLeftX = $detector->bottomLeftX;
		$this->bottomLeftY = $detector->bottomLeftY;

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
				return $this->decodeMatrix($matrix->resetVersionInfo()->mirrorDiagonal());
			}
			catch(Throwable){
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
		$this->version     = $matrix->getVersion();
		$this->eccLevel    = $matrix->getEccLevel();
		$this->maskPattern = $matrix->getMaskPattern();

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
		$this->bitBuffer  = $bitBuffer;
		$versionNumber    = $this->version->getVersionNumber();
		$symbolSequence   = -1;
		$parityData       = -1;
		$fc1InEffect      = false;
		$result           = '';

		// While still another segment to read...
		while($this->bitBuffer->available() >= 4){
			$datamode = $this->bitBuffer->read(4); // mode is encoded by 4 bits

			// OK, assume we're done
			if($datamode === Mode::TERMINATOR){
				break;
			}
			elseif($datamode === Mode::NUMBER){
				$result .= Number::decodeSegment($this->bitBuffer, $versionNumber);
			}
			elseif($datamode === Mode::ALPHANUM){
				$result .= $this->decodeAlphanumSegment($versionNumber, $fc1InEffect);
			}
			elseif($datamode === Mode::BYTE){
				$result .= Byte::decodeSegment($this->bitBuffer, $versionNumber);
			}
			elseif($datamode === Mode::KANJI){
				$result .= Kanji::decodeSegment($this->bitBuffer, $versionNumber);
			}
			elseif($datamode === Mode::STRCTURED_APPEND){

				if($this->bitBuffer->available() < 16){
					throw new QRCodeDecoderException('structured append: not enough bits left');
				}
				// sequence number and parity is added later to the result metadata
				// Read next 8 bits (symbol sequence #) and 8 bits (parity data), then continue
				$symbolSequence = $this->bitBuffer->read(8);
				$parityData     = $this->bitBuffer->read(8);
			}
			elseif($datamode === Mode::FNC1_FIRST || $datamode === Mode::FNC1_SECOND){
				// We do little with FNC1 except alter the parsed result a bit according to the spec
				$fc1InEffect = true;
			}
			elseif($datamode === Mode::ECI){
				$result .= ECI::decodeSegment($this->bitBuffer, $versionNumber);
			}
			elseif($datamode === Mode::HANZI){
				$result .= Hanzi::decodeSegment($this->bitBuffer, $versionNumber);
			}
			else{
				throw new QRCodeDecoderException('invalid data mode');
			}

		}

		return new DecoderResult([
			'rawBytes'                 => $this->bitBuffer,
			'data'                     => $result,
			'version'                  => $this->version,
			'eccLevel'                 => $this->eccLevel,
			'maskPattern'              => $this->maskPattern,
			'structuredAppendParity'   => $parityData,
			'structuredAppendSequence' => $symbolSequence,
			'topLeftX'                 => $this->topLeftX,
			'topLeftY'                 => $this->topLeftY,
			'topRightX'                => $this->topRightX,
			'topRightY'                => $this->topRightY,
			'bottomLeftX'              => $this->bottomLeftX,
			'bottomLeftY'              => $this->bottomLeftY,
		]);
	}

	private function decodeAlphanumSegment(int $versionNumber, bool $fc1InEffect):string{
		$str = AlphaNum::decodeSegment($this->bitBuffer, $versionNumber);

		// See section 6.4.8.1, 6.4.8.2
		if($fc1InEffect){ // ???
			// We need to massage the result a bit if in an FNC1 mode:
			$str = str_replace(chr(0x1d), '%', $str);
			$str = str_replace('%%', '%', $str);
		}

		return $str;
	}

}
