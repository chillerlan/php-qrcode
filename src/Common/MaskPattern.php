<?php
/**
 * Class MaskPattern
 *
 * @filesource   MaskPattern.php
 * @created      19.01.2021
 * @package      chillerlan\QRCode\Common
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Common;

use chillerlan\QRCode\QRCodeException;
use Closure;

/**
 * ISO/IEC 18004:2000 Section 8.8.1
 */
final class MaskPattern{

	public const PATTERN_000 = 0b000;
	public const PATTERN_001 = 0b001;
	public const PATTERN_010 = 0b010;
	public const PATTERN_011 = 0b011;
	public const PATTERN_100 = 0b100;
	public const PATTERN_101 = 0b101;
	public const PATTERN_110 = 0b110;
	public const PATTERN_111 = 0b111;

	public const PATTERNS = [
		self::PATTERN_000,
		self::PATTERN_001,
		self::PATTERN_010,
		self::PATTERN_011,
		self::PATTERN_100,
		self::PATTERN_101,
		self::PATTERN_110,
		self::PATTERN_111,
	];

	private int $maskPattern;

	/**
	 * MaskPattern constructor.
	 *
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function __construct(int $maskPattern){

		if((0b111 & $maskPattern) !== $maskPattern){
			throw new QRCodeException('invalid mask pattern');
		}

		$this->maskPattern = $maskPattern;
	}

	/**
	 * Returns the current mask pattern
	 */
	public function getPattern():int{
		return $this->maskPattern;
	}

	/**
	 * Returns a closure that applies the mask for the chosen mask pattern.
	 *
	 * Note that some versions of the QR code standard have had errors in the section about mask patterns.
	 * The information below has been corrected.
	 *
	 * @see https://www.thonky.com/qr-code-tutorial/mask-patterns
	 */
	public function getMask():Closure{
		// $x = column (width), $y = row (height)
		return [
			self::PATTERN_000 => fn(int $x, int $y):int => ($x + $y) % 2,
			self::PATTERN_001 => fn(int $x, int $y):int => $y % 2,
			self::PATTERN_010 => fn(int $x, int $y):int => $x % 3,
			self::PATTERN_011 => fn(int $x, int $y):int => ($x + $y) % 3,
			self::PATTERN_100 => fn(int $x, int $y):int => ((int)($y / 2) + (int)($x / 3)) % 2,
			self::PATTERN_101 => fn(int $x, int $y):int => (($x * $y) % 2) + (($x * $y) % 3),
			self::PATTERN_110 => fn(int $x, int $y):int => ((($x * $y) % 2) + (($x * $y) % 3)) % 2,
			self::PATTERN_111 => fn(int $x, int $y):int => ((($x * $y) % 3) + (($x + $y) % 2)) % 2,
		][$this->maskPattern];
	}

}
