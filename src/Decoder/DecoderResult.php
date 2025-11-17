<?php
/**
 * Class DecoderResult
 *
 * @created      17.01.2021
 * @author       ZXing Authors
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2021 Smiley
 * @license      Apache-2.0
 */
declare(strict_types=1);

namespace chillerlan\QRCode\Decoder;

use chillerlan\QRCode\Common\{BitBuffer, EccLevel, MaskPattern, Version};
use chillerlan\QRCode\Data\QRMatrix;
use function property_exists;

/**
 * Encapsulates the result of decoding a matrix of bits. This typically
 * applies to 2D barcode formats. For now, it contains the raw bytes obtained
 * as well as a String interpretation of those bytes, if applicable.
 *
 * @property string                                      $data
 * @property \chillerlan\QRCode\Common\EccLevel          $eccLevel
 * @property \chillerlan\QRCode\Detector\FinderPattern[] $finderPatterns
 * @property \chillerlan\QRCode\Common\MaskPattern       $maskPattern
 * @property \chillerlan\QRCode\Common\BitBuffer         $rawBytes
 * @property int                                         $structuredAppendParity
 * @property int                                         $structuredAppendSequence
 * @property \chillerlan\QRCode\Common\Version           $version
 */
final class DecoderResult{

	private string $data = '';
	private EccLevel    $eccLevel;
	/** @var \chillerlan\QRCode\Detector\FinderPattern[] */
	private array       $finderPatterns = [];
	private MaskPattern $maskPattern;
	private BitBuffer   $rawBytes;
	private int         $structuredAppendParity = -1;
	private int         $structuredAppendSequence = -1;
	private Version     $version;

	/**
	 * DecoderResult constructor.
	 *
	 * @phpstan-param array<string, mixed> $properties
	 */
	public function __construct(iterable|null $properties = null){

		if($properties !== null){

			foreach($properties as $property => $value){

				if(!property_exists($this, $property)){
					continue;
				}

				$this->{$property} = $value;
			}

		}

	}

	public function __get(string $property):mixed{

		if(property_exists($this, $property)){
			return $this->{$property};
		}

		return null;
	}

	public function __toString():string{
		return $this->data;
	}

	public function hasStructuredAppend():bool{
		return $this->structuredAppendParity >= 0 && $this->structuredAppendSequence >= 0;
	}

	/**
	 * Returns a QRMatrix instance with the settings and data of the reader result
	 */
	public function getQRMatrix():QRMatrix{
		return (new QRMatrix($this->version, $this->eccLevel))
			->initFunctionalPatterns()
			->writeCodewords($this->rawBytes)
			->setFormatInfo($this->maskPattern)
			->mask($this->maskPattern)
		;
	}

}
