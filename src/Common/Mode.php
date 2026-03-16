<?php
/**
 * Class Mode
 *
 * @created      19.11.2020
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCode\Common;

use chillerlan\QRCode\Data\{AlphaNum, Byte, Hanzi, Kanji, Number};
use chillerlan\QRCode\QRCodeException;

/**
 * Data mode information - ISO 18004:2006, 6.4.1, Tables 2 and 3
 */
final class Mode{

	// ISO/IEC 18004:2000 Table 2

	public const int TERMINATOR       = 0b0000;
	public const int NUMBER           = 0b0001;
	public const int ALPHANUM         = 0b0010;
	public const int BYTE             = 0b0100;
	public const int KANJI            = 0b1000;
	public const int HANZI            = 0b1101;
	public const int STRCTURED_APPEND = 0b0011;
	public const int FNC1_FIRST       = 0b0101;
	public const int FNC1_SECOND      = 0b1001;
	public const int ECI              = 0b0111;

	/**
	 * mode length bits for the version breakpoints 1-9, 10-26 and 27-40
	 *
	 * ISO/IEC 18004:2000 Table 3 - Number of bits in Character Count Indicator
	 */
	public const array LENGTH_BITS = [
		self::NUMBER   => [10, 12, 14],
		self::ALPHANUM => [ 9, 11, 13],
		self::BYTE     => [ 8, 16, 16],
		self::KANJI    => [ 8, 10, 12],
		self::HANZI    => [ 8, 10, 12],
		self::ECI      => [ 0,  0,  0],
	];

	/**
	 * Map of data mode => interface (detection order)
	 *
	 * @var array<int, string>
	 */
	public const array INTERFACES = [
		self::NUMBER   => Number::class,
		self::ALPHANUM => AlphaNum::class,
		self::KANJI    => Kanji::class,
		self::HANZI    => Hanzi::class,
		self::BYTE     => Byte::class,
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

}
