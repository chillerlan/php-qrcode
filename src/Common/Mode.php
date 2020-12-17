<?php
/**
 * Class Mode
 *
 * @filesource   Mode.php
 * @created      19.11.2020
 * @package      chillerlan\QRCode\Common
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Common;

use chillerlan\QRCode\Data\{AlphaNum, Byte, Kanji, Number};
use chillerlan\QRCode\QRCodeException;

/**
 * ISO 18004:2006, 6.4.1, Tables 2 and 3
 */
final class Mode{

	// ISO/IEC 18004:2000 Table 2

	/** @var int */
	public const DATA_TERMINATOR       = 0b0000;
	/** @var int */
	public const DATA_NUMBER           = 0b0001;
	/** @var int */
	public const DATA_ALPHANUM         = 0b0010;
	/** @var int */
	public const DATA_BYTE             = 0b0100;
	/** @var int */
	public const DATA_KANJI            = 0b1000;
	/** @var int */
	public const DATA_STRCTURED_APPEND = 0b0011;
	/** @var int */
	public const DATA_FNC1_FIRST       = 0b0101;
	/** @var int */
	public const DATA_FNC1_SECOND      = 0b1001;
	/** @var int */
	public const DATA_ECI              = 0b0111;

	/**
	 * mode length bits for the version breakpoints 1-9, 10-26 and 27-40
	 *
	 * ISO/IEC 18004:2000 Table 3 - Number of bits in Character Count Indicator
	 */
	public const LENGTH_BITS = [
		self::DATA_NUMBER   => [10, 12, 14],
		self::DATA_ALPHANUM => [ 9, 11, 13],
		self::DATA_BYTE     => [ 8, 16, 16],
		self::DATA_KANJI    => [ 8, 10, 12],
	];

	/**
	 * Map of data mode => interface (detection order)
	 *
	 * @var string[]
	 */
	public const DATA_INTERFACES = [
		self::DATA_NUMBER   => Number::class,
		self::DATA_ALPHANUM => AlphaNum::class,
		self::DATA_KANJI    => Kanji::class,
		self::DATA_BYTE     => Byte::class,
	];

	/**
	 * returns the length bits for the version breakpoints 1-9, 10-26 and 27-40
	 *
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public static function getLengthBitsForVersion(int $mode, int $version):int{

		if(!isset(self::LENGTH_BITS[$mode])){
			throw new QRCodeException('invalid mode given');
		}

		$minVersion = 0;

		foreach([9, 26, 40] as $key => $breakpoint){

			if($version > $minVersion && $version <= $breakpoint){
				return self::LENGTH_BITS[$mode][$key];
			}

			$minVersion = $breakpoint;
		}

		throw new QRCodeException(sprintf('invalid version number: %d', $version));
	}

	/**
	 * returns the array of length bits for the given mode
	 */
	public static function getLengthBitsForMode(int $mode):array{
		return self::LENGTH_BITS[$mode];
	}

}
