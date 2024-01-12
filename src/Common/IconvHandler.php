<?php
/**
 * Class IconvHandler
 *
 * @created      12.01.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace chillerlan\QRCode\Common;

use RuntimeException;
use function iconv_get_encoding;
use function iconv_strlen;
use function is_array;
use function is_string;

/**
 * Handles iconv encoding operations (will probably fail)
 */
class IconvHandler implements CharacterEncodingHandlerInterface{

	/**
	 * @inheritDoc
	 */
	public static function getCharCount(string $string, string $encoding = null):int{
		return iconv_strlen($string, $encoding);
	}

	/**
	 * @inheritDoc
	 */
	public static function getInternalEncoding():string{
		return iconv_get_encoding('internal_encoding');
	}

	/**
	 * @inheritDoc
	 * @todo
	 */
	public static function convertEncoding(string $string, string $to_encoding, array|string $from_encoding = null):string{

		// we don't have detect_encoding here, so we pick the first item, otherwise set to internal
		if(is_array($from_encoding)){
			$from_encoding = ($from_encoding[0] ?? self::getInternalEncoding());
		}

		$str = iconv($from_encoding, $to_encoding, $string);

		if(!is_string($str)){
			throw new RuntimeException('iconv error');
		}

		return $str;
	}

}
