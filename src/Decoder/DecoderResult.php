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

use chillerlan\Settings\SettingsContainerAbstract;
use chillerlan\QRCode\Common\{EccLevel, Version};

/**
 * Encapsulates the result of decoding a matrix of bits. This typically
 * applies to 2D barcode formats. For now it contains the raw bytes obtained,
 * as well as a String interpretation of those bytes, if applicable.
 *
 * @property int[]                              $rawBytes
 * @property string                             $text
 * @property \chillerlan\QRCode\Common\Version  $version
 * @property \chillerlan\QRCode\Common\EccLevel $eccLevel
 * @property int                                $structuredAppendParity
 * @property int                                $structuredAppendSequence
 */
final class DecoderResult extends SettingsContainerAbstract{

	protected array    $rawBytes;
	protected string   $text;
	protected Version  $version;
	protected EccLevel $eccLevel;
	protected int      $structuredAppendParity = -1;
	protected int      $structuredAppendSequence = -1;

	/**
	 * @inheritDoc
	 */
	public function __set($property, $value):void{
		// noop, read-only
	}

	/**
	 * @inheritDoc
	 */
	public function __toString():string{
		return $this->text;
	}

	/**
	 * @inheritDoc
	 */
	public function fromIterable(iterable $properties):self{

		foreach($properties as $key => $value){
			parent::__set($key, $value);
		}

		return $this;
	}

	/**
	 *
	 */
	public function hasStructuredAppend():bool{
		return $this->structuredAppendParity >= 0 && $this->structuredAppendSequence >= 0;
	}

}
