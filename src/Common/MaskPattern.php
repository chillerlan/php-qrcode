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
 *
 */
class MaskPattern{

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
	 * ISO/IEC 18004:2000 Section 8.8.1
	 *
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function __construct(int $maskPattern){

		if((0b111 & $maskPattern) !== $maskPattern){
			throw new QRCodeException('invalid mask pattern'); // @codeCoverageIgnore
		}

		$this->maskPattern = $maskPattern;
	}

	public function getPattern():int{
		return $this->maskPattern;
	}

	/**
	 * ISO/IEC 18004:2000 Section 8.8.1
	 *
	 * Note that some versions of the QR code standard have had errors in the section about mask patterns.
	 * The information below has been corrected. (https://www.thonky.com/qr-code-tutorial/mask-patterns)
	 */
	public function getMask():Closure{
		return [
			self::PATTERN_000 => fn($x, $y):int => ($x + $y) % 2,
			self::PATTERN_001 => fn($x, $y):int => $y % 2,
			self::PATTERN_010 => fn($x, $y):int => $x % 3,
			self::PATTERN_011 => fn($x, $y):int => ($x + $y) % 3,
			self::PATTERN_100 => fn($x, $y):int => ((int)($y / 2) + (int)($x / 3)) % 2,
			self::PATTERN_101 => fn($x, $y):int => (($x * $y) % 2) + (($x * $y) % 3),
			self::PATTERN_110 => fn($x, $y):int => ((($x * $y) % 2) + (($x * $y) % 3)) % 2,
			self::PATTERN_111 => fn($x, $y):int => ((($x * $y) % 3) + (($x + $y) % 2)) % 2,
		][$this->maskPattern];
	}

}
