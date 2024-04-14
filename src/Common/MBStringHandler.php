<?php
/**
 * Class MBStringHandler
 *
 * @created      11.01.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace chillerlan\QRCode\Common;

use RuntimeException;
use function is_string;
use function mb_convert_encoding;
use function mb_internal_encoding;
use function mb_strlen;

/**
 * Handles mbstring encoding operations
 */
class MBStringHandler implements CharacterEncodingHandlerInterface{

	/**
	 * @inheritDoc
	 */
	public static function getCharCount(string $string, string $encoding = null):int{
		return mb_strlen($string, $encoding);
	}

	/**
	 * @inheritDoc
	 */
	public static function getInternalEncoding():string{
		return mb_internal_encoding();
	}

	/**
	 * @inheritDoc
	 * @see \mb_detect_encoding()
	 */
	public static function convertEncoding(string $string, string $to_encoding, array|string $from_encoding = null):string{
		$str =  mb_convert_encoding($string, $to_encoding, $from_encoding);

		if(!is_string($str)){
			throw new RuntimeException('mb_convert_encoding error');
		}

		return $str;
	}

}
