<?php
/**
 * Interface CharacterEncodingHandlerInterface
 *
 * @created      11.01.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Common;

/**
 * Handles all multibyte character encoding/conversion operations
 */
interface CharacterEncodingHandlerInterface{

	/**
	 * Get the string length
	 */
	public static function getCharCount(string $string, string $encoding = null):int;

	/**
	 * Gets the internal character encoding
	 */
	public static function getInternalEncoding():string;

	/**
	 * Converts the given `$string` to `$to_encoding`, optionally using the given encoding(s) in `$from_encoding`
	 *
	 * @throws \RuntimeException if an error occurred
	 */
	public static function convertEncoding(string $string, string $to_encoding, array|string $from_encoding = null):string;

}
