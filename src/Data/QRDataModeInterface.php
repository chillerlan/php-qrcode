<?php
/**
 * Interface QRDataModeInterface
 *
 * @created      01.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\Common\BitBuffer;

/**
 * Specifies the methods reqired for the data modules (Number, Alphanum, Byte and Kanji)
 */
interface QRDataModeInterface{

	/**
	 * returns the current data mode constant
	 */
	public function getDataMode():int;

	/**
	 * retruns the length in bits of the data string
	 */
	public function getLengthInBits():int;

	/**
	 * checks if the given string qualifies for the encoder module
	 */
	public static function validateString(string $string):bool;

	/**
	 * writes the actual data string to the BitBuffer, uses the given version to determine the length bits
	 *
	 * @see \chillerlan\QRCode\Data\QRData::writeBitBuffer()
	 */
	public function write(BitBuffer $bitBuffer, int $versionNumber):void;

	/**
	 * reads a segment from the BitBuffer and decodes in the current data mode
	 */
	public static function decodeSegment(BitBuffer $bitBuffer, int $versionNumber):string;

}
