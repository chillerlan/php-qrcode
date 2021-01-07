<?php
/**
 * Class EccLevel
 *
 * @filesource   EccLevel.php
 * @created      19.11.2020
 * @package      chillerlan\QRCode\Common
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Common;

use chillerlan\QRCode\QRCodeException;

use function array_column, array_combine, array_keys;

/**
 *
 */
final class EccLevel{

	// ISO/IEC 18004:2000 Tables 12, 25

	/** @var int */
	public const L = 0b01; // 7%.
	/** @var int */
	public const M = 0b00; // 15%.
	/** @var int */
	public const Q = 0b11; // 25%.
	/** @var int */
	public const H = 0b10; // 30%.

	/**
	 * References to the keys of the following tables:
	 *
	 * @see \chillerlan\QRCode\Common\Version::MAX_BITS
	 * @see \chillerlan\QRCode\Common\EccLevel::RSBLOCKS
	 * @see \chillerlan\QRCode\Common\EccLevel::formatPattern
	 *
	 * @var int[]
	 */
	public const MODES = [
		self::L => 0,
		self::M => 1,
		self::Q => 2,
		self::H => 3,
	];

	public const MODES_STRING = [
		self::L => 'L',
		self::M => 'M',
		self::Q => 'Q',
		self::H => 'H',
	];

	/**
	 * ISO/IEC 18004:2000 Tables 13-22
	 *
	 * @see http://www.thonky.com/qr-code-tutorial/error-correction-table
	 *
	 * @var int [][][]
	 */
	private const RSBLOCKS = [
		1  => [[ 1,  0,  26,  19], [ 1,  0, 26, 16], [ 1,  0, 26, 13], [ 1,  0, 26,  9]],
		2  => [[ 1,  0,  44,  34], [ 1,  0, 44, 28], [ 1,  0, 44, 22], [ 1,  0, 44, 16]],
		3  => [[ 1,  0,  70,  55], [ 1,  0, 70, 44], [ 2,  0, 35, 17], [ 2,  0, 35, 13]],
		4  => [[ 1,  0, 100,  80], [ 2,  0, 50, 32], [ 2,  0, 50, 24], [ 4,  0, 25,  9]],
		5  => [[ 1,  0, 134, 108], [ 2,  0, 67, 43], [ 2,  2, 33, 15], [ 2,  2, 33, 11]],
		6  => [[ 2,  0,  86,  68], [ 4,  0, 43, 27], [ 4,  0, 43, 19], [ 4,  0, 43, 15]],
		7  => [[ 2,  0,  98,  78], [ 4,  0, 49, 31], [ 2,  4, 32, 14], [ 4,  1, 39, 13]],
		8  => [[ 2,  0, 121,  97], [ 2,  2, 60, 38], [ 4,  2, 40, 18], [ 4,  2, 40, 14]],
		9  => [[ 2,  0, 146, 116], [ 3,  2, 58, 36], [ 4,  4, 36, 16], [ 4,  4, 36, 12]],
		10 => [[ 2,  2,  86,  68], [ 4,  1, 69, 43], [ 6,  2, 43, 19], [ 6,  2, 43, 15]],
		11 => [[ 4,  0, 101,  81], [ 1,  4, 80, 50], [ 4,  4, 50, 22], [ 3,  8, 36, 12]],
		12 => [[ 2,  2, 116,  92], [ 6,  2, 58, 36], [ 4,  6, 46, 20], [ 7,  4, 42, 14]],
		13 => [[ 4,  0, 133, 107], [ 8,  1, 59, 37], [ 8,  4, 44, 20], [12,  4, 33, 11]],
		14 => [[ 3,  1, 145, 115], [ 4,  5, 64, 40], [11,  5, 36, 16], [11,  5, 36, 12]],
		15 => [[ 5,  1, 109,  87], [ 5,  5, 65, 41], [ 5,  7, 54, 24], [11,  7, 36, 12]],
		16 => [[ 5,  1, 122,  98], [ 7,  3, 73, 45], [15,  2, 43, 19], [ 3, 13, 45, 15]],
		17 => [[ 1,  5, 135, 107], [10,  1, 74, 46], [ 1, 15, 50, 22], [ 2, 17, 42, 14]],
		18 => [[ 5,  1, 150, 120], [ 9,  4, 69, 43], [17,  1, 50, 22], [ 2, 19, 42, 14]],
		19 => [[ 3,  4, 141, 113], [ 3, 11, 70, 44], [17,  4, 47, 21], [ 9, 16, 39, 13]],
		20 => [[ 3,  5, 135, 107], [ 3, 13, 67, 41], [15,  5, 54, 24], [15, 10, 43, 15]],
		21 => [[ 4,  4, 144, 116], [17,  0, 68, 42], [17,  6, 50, 22], [19,  6, 46, 16]],
		22 => [[ 2,  7, 139, 111], [17,  0, 74, 46], [ 7, 16, 54, 24], [34,  0, 37, 13]],
		23 => [[ 4,  5, 151, 121], [ 4, 14, 75, 47], [11, 14, 54, 24], [16, 14, 45, 15]],
		24 => [[ 6,  4, 147, 117], [ 6, 14, 73, 45], [11, 16, 54, 24], [30,  2, 46, 16]],
		25 => [[ 8,  4, 132, 106], [ 8, 13, 75, 47], [ 7, 22, 54, 24], [22, 13, 45, 15]],
		26 => [[10,  2, 142, 114], [19,  4, 74, 46], [28,  6, 50, 22], [33,  4, 46, 16]],
		27 => [[ 8,  4, 152, 122], [22,  3, 73, 45], [ 8, 26, 53, 23], [12, 28, 45, 15]],
		28 => [[ 3, 10, 147, 117], [ 3, 23, 73, 45], [ 4, 31, 54, 24], [11, 31, 45, 15]],
		29 => [[ 7,  7, 146, 116], [21,  7, 73, 45], [ 1, 37, 53, 23], [19, 26, 45, 15]],
		30 => [[ 5, 10, 145, 115], [19, 10, 75, 47], [15, 25, 54, 24], [23, 25, 45, 15]],
		31 => [[13,  3, 145, 115], [ 2, 29, 74, 46], [42,  1, 54, 24], [23, 28, 45, 15]],
		32 => [[17,  0, 145, 115], [10, 23, 74, 46], [10, 35, 54, 24], [19, 35, 45, 15]],
		33 => [[17,  1, 145, 115], [14, 21, 74, 46], [29, 19, 54, 24], [11, 46, 45, 15]],
		34 => [[13,  6, 145, 115], [14, 23, 74, 46], [44,  7, 54, 24], [59,  1, 46, 16]],
		35 => [[12,  7, 151, 121], [12, 26, 75, 47], [39, 14, 54, 24], [22, 41, 45, 15]],
		36 => [[ 6, 14, 151, 121], [ 6, 34, 75, 47], [46, 10, 54, 24], [ 2, 64, 45, 15]],
		37 => [[17,  4, 152, 122], [29, 14, 74, 46], [49, 10, 54, 24], [24, 46, 45, 15]],
		38 => [[ 4, 18, 152, 122], [13, 32, 74, 46], [48, 14, 54, 24], [42, 32, 45, 15]],
		39 => [[20,  4, 147, 117], [40,  7, 75, 47], [43, 22, 54, 24], [10, 67, 45, 15]],
		40 => [[19,  6, 148, 118], [18, 31, 75, 47], [34, 34, 54, 24], [20, 61, 45, 15]],
	];

	/**
	 * ISO/IEC 18004:2000 Tables 7-11 - Number of symbol characters and input data capacity for versions 1 to 40
	 *
	 * @var int [][]
	 */
	private const MAX_BITS = [
	//  v  => [    L,     M,     Q,     H]  // modules
		1  => [  152,   128,   104,    72], //  21
		2  => [  272,   224,   176,   128], //  25
		3  => [  440,   352,   272,   208], //  29
		4  => [  640,   512,   384,   288], //  33
		5  => [  864,   688,   496,   368], //  37
		6  => [ 1088,   864,   608,   480], //  41
		7  => [ 1248,   992,   704,   528], //  45
		8  => [ 1552,  1232,   880,   688], //  49
		9  => [ 1856,  1456,  1056,   800], //  53
		10 => [ 2192,  1728,  1232,   976], //  57
		11 => [ 2592,  2032,  1440,  1120], //  61
		12 => [ 2960,  2320,  1648,  1264], //  65
		13 => [ 3424,  2672,  1952,  1440], //  69 NICE!
		14 => [ 3688,  2920,  2088,  1576], //  73
		15 => [ 4184,  3320,  2360,  1784], //  77
		16 => [ 4712,  3624,  2600,  2024], //  81
		17 => [ 5176,  4056,  2936,  2264], //  85
		18 => [ 5768,  4504,  3176,  2504], //  89
		19 => [ 6360,  5016,  3560,  2728], //  93
		20 => [ 6888,  5352,  3880,  3080], //  97
		21 => [ 7456,  5712,  4096,  3248], // 101
		22 => [ 8048,  6256,  4544,  3536], // 105
		23 => [ 8752,  6880,  4912,  3712], // 109
		24 => [ 9392,  7312,  5312,  4112], // 113
		25 => [10208,  8000,  5744,  4304], // 117
		26 => [10960,  8496,  6032,  4768], // 121
		27 => [11744,  9024,  6464,  5024], // 125
		28 => [12248,  9544,  6968,  5288], // 129
		29 => [13048, 10136,  7288,  5608], // 133
		30 => [13880, 10984,  7880,  5960], // 137
		31 => [14744, 11640,  8264,  6344], // 141
		32 => [15640, 12328,  8920,  6760], // 145
		33 => [16568, 13048,  9368,  7208], // 149
		34 => [17528, 13800,  9848,  7688], // 153
		35 => [18448, 14496, 10288,  7888], // 157
		36 => [19472, 15312, 10832,  8432], // 161
		37 => [20528, 15936, 11408,  8768], // 165
		38 => [21616, 16816, 12016,  9136], // 169
		39 => [22496, 17728, 12656,  9776], // 173
		40 => [23648, 18672, 13328, 10208], // 177
	];

	/**
	 * ISO/IEC 18004:2000 Section 8.9 - Format Information
	 *
	 * ECC level -> mask pattern
	 *
	 * @var int[][]
	 */
	private const FORMAT_PATTERN = [
		[ // L
		  0b111011111000100,
		  0b111001011110011,
		  0b111110110101010,
		  0b111100010011101,
		  0b110011000101111,
		  0b110001100011000,
		  0b110110001000001,
		  0b110100101110110,
		],
		[ // M
		  0b101010000010010,
		  0b101000100100101,
		  0b101111001111100,
		  0b101101101001011,
		  0b100010111111001,
		  0b100000011001110,
		  0b100111110010111,
		  0b100101010100000,
		],
		[ // Q
		  0b011010101011111,
		  0b011000001101000,
		  0b011111100110001,
		  0b011101000000110,
		  0b010010010110100,
		  0b010000110000011,
		  0b010111011011010,
		  0b010101111101101,
		],
		[ // H
		  0b001011010001001,
		  0b001001110111110,
		  0b001110011100111,
		  0b001100111010000,
		  0b000011101100010,
		  0b000001001010101,
		  0b000110100001100,
		  0b000100000111011,
		],
	];

	private int $eccLevel;

	/**
	 * @param int $eccLevel containing the two bits encoding a QR Code's error correction level
	 *
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function __construct(int $eccLevel){

		if((0b11 & $eccLevel) !== $eccLevel){
			throw new QRCodeException('invalid ECC level');
		}

		$this->eccLevel = $eccLevel;
	}

	/**
	 * returns the string representation of the current ECC level
	 */
	public function __toString():string{
		return self::MODES_STRING[$this->eccLevel];
	}

	/**
	 * returns the current ECC level
	 */
	public function getLevel():int{
		return $this->eccLevel;
	}

	/**
	 * returns the ordinal value of the current ECC level
	 */
	public function getOrdinal():int{
		return self::MODES[$this->eccLevel];
	}

	/**
	 * returns ECC block information for the given $version and $eccLevel
	 *
	 * @return int[]
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function getRSBlocks(int $version):array{

		if($version < 1 || $version > 40){
			throw new QRCodeException('invalid version');
		}

		return self::RSBLOCKS[$version][self::MODES[$this->eccLevel]];
	}

	/**
	 * returns the format pattern for the given $eccLevel and $maskPattern
	 *
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function getformatPattern(int $maskPattern):int{

		if((0b111 & $maskPattern) !== $maskPattern){
			throw new QRCodeException('invalid mask pattern');
		}

		return self::FORMAT_PATTERN[self::MODES[$this->eccLevel]][$maskPattern];
	}

	/**
	 * returns an array wit the max bit lengths for version 1-40 and the current ECC level
	 */
	public function getMaxBits():array{
		return array_combine(
			array_keys(self::MAX_BITS),
			array_column(self::MAX_BITS, self::MODES[$this->eccLevel])
		);
	}

}
