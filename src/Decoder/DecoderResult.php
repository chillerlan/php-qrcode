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

namespace chillerlan\QRCode\Decoder;

use chillerlan\QRCode\Common\{EccLevel, MaskPattern, Version};
use function property_exists;

/**
 * Encapsulates the result of decoding a matrix of bits. This typically
 * applies to 2D barcode formats. For now it contains the raw bytes obtained,
 * as well as a String interpretation of those bytes, if applicable.
 *
 * @property int[]                                 $rawBytes
 * @property string                                $data
 * @property \chillerlan\QRCode\Common\Version     $version
 * @property \chillerlan\QRCode\Common\EccLevel    $eccLevel
 * @property \chillerlan\QRCode\Common\MaskPattern $maskPattern
 * @property int                                   $structuredAppendParity
 * @property int                                   $structuredAppendSequence
 */
final class DecoderResult{

	protected array       $rawBytes;
	protected string      $data;
	protected Version     $version;
	protected EccLevel    $eccLevel;
	protected MaskPattern $maskPattern;
	protected int         $structuredAppendParity = -1;
	protected int         $structuredAppendSequence = -1;

	/**
	 * DecoderResult constructor.
	 */
	public function __construct(iterable $properties = null){

		if(!empty($properties)){

			foreach($properties as $property => $value){

				if(!property_exists($this, $property)){
					continue;
				}

				$this->{$property} = $value;
			}

		}

	}

	/**
	 * @return mixed|null
	 */
	public function __get(string $property){

		if(property_exists($this, $property)){
			return $this->{$property};
		}

		return null;
	}

	/**
	 *
	 */
	public function __toString():string{
		return $this->data;
	}

	/**
	 *
	 */
	public function hasStructuredAppend():bool{
		return $this->structuredAppendParity >= 0 && $this->structuredAppendSequence >= 0;
	}

}
