<?php
/**
 * Class Polynomial
 *
 * @filesource   Polynomial.php
 * @created      25.11.2015
 * @package      chillerlan\QRCode\Helpers
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Helpers;

use chillerlan\QRCode\QRCodeException;

/**
 * @link http://www.thonky.com/qr-code-tutorial/error-correction-coding
 */
class Polynomial{

	/**
	 * @link http://www.thonky.com/qr-code-tutorial/log-antilog-table
	 */
	const table = [
		[  1,   0], [  2,   0], [  4,   1], [  8,  25], [ 16,   2], [ 32,  50], [ 64,  26], [128, 198],
		[ 29,   3], [ 58, 223], [116,  51], [232, 238], [205,  27], [135, 104], [ 19, 199], [ 38,  75],
		[ 76,   4], [152, 100], [ 45, 224], [ 90,  14], [180,  52], [117, 141], [234, 239], [201, 129],
		[143,  28], [  3, 193], [  6, 105], [ 12, 248], [ 24, 200], [ 48,   8], [ 96,  76], [192, 113],
		[157,   5], [ 39, 138], [ 78, 101], [156,  47], [ 37, 225], [ 74,  36], [148,  15], [ 53,  33],
		[106,  53], [212, 147], [181, 142], [119, 218], [238, 240], [193,  18], [159, 130], [ 35,  69],
		[ 70,  29], [140, 181], [  5, 194], [ 10, 125], [ 20, 106], [ 40,  39], [ 80, 249], [160, 185],
		[ 93, 201], [186, 154], [105,   9], [210, 120], [185,  77], [111, 228], [222, 114], [161, 166],
		[ 95,   6], [190, 191], [ 97, 139], [194,  98], [153, 102], [ 47, 221], [ 94,  48], [188, 253],
		[101, 226], [202, 152], [137,  37], [ 15, 179], [ 30,  16], [ 60, 145], [120,  34], [240, 136],
		[253,  54], [231, 208], [211, 148], [187, 206], [107, 143], [214, 150], [177, 219], [127, 189],
		[254, 241], [225, 210], [223,  19], [163,  92], [ 91, 131], [182,  56], [113,  70], [226,  64],
		[217,  30], [175,  66], [ 67, 182], [134, 163], [ 17, 195], [ 34,  72], [ 68, 126], [136, 110],
		[ 13, 107], [ 26,  58], [ 52,  40], [104,  84], [208, 250], [189, 133], [103, 186], [206,  61],
		[129, 202], [ 31,  94], [ 62, 155], [124, 159], [248,  10], [237,  21], [199, 121], [147,  43],
		[ 59,  78], [118, 212], [236, 229], [197, 172], [151, 115], [ 51, 243], [102, 167], [204,  87],
		[133,   7], [ 23, 112], [ 46, 192], [ 92, 247], [184, 140], [109, 128], [218,  99], [169,  13],
		[ 79, 103], [158,  74], [ 33, 222], [ 66, 237], [132,  49], [ 21, 197], [ 42, 254], [ 84,  24],
		[168, 227], [ 77, 165], [154, 153], [ 41, 119], [ 82,  38], [164, 184], [ 85, 180], [170, 124],
		[ 73,  17], [146,  68], [ 57, 146], [114, 217], [228,  35], [213,  32], [183, 137], [115,  46],
		[230,  55], [209,  63], [191, 209], [ 99,  91], [198, 149], [145, 188], [ 63, 207], [126, 205],
		[252, 144], [229, 135], [215, 151], [179, 178], [123, 220], [246, 252], [241, 190], [255,  97],
		[227, 242], [219,  86], [171, 211], [ 75, 171], [150,  20], [ 49,  42], [ 98,  93], [196, 158],
		[149, 132], [ 55,  60], [110,  57], [220,  83], [165,  71], [ 87, 109], [174,  65], [ 65, 162],
		[130,  31], [ 25,  45], [ 50,  67], [100, 216], [200, 183], [141, 123], [  7, 164], [ 14, 118],
		[ 28, 196], [ 56,  23], [112,  73], [224, 236], [221, 127], [167,  12], [ 83, 111], [166, 246],
		[ 81, 108], [162, 161], [ 89,  59], [178,  82], [121,  41], [242, 157], [249,  85], [239, 170],
		[195, 251], [155,  96], [ 43, 134], [ 86, 177], [172, 187], [ 69, 204], [138,  62], [  9,  90],
		[ 18, 203], [ 36,  89], [ 72,  95], [144, 176], [ 61, 156], [122, 169], [244, 160], [245,  81],
		[247,  11], [243, 245], [251,  22], [235, 235], [203, 122], [139, 117], [ 11,  44], [ 22, 215],
		[ 44,  79], [ 88, 174], [176, 213], [125, 233], [250, 230], [233, 231], [207, 173], [131, 232],
		[ 27, 116], [ 54, 214], [108, 244], [216, 234], [173, 168], [ 71,  80], [142,  88], [  1, 175],
	];

	/**
	 * @var array
	 */
	protected $num = [];

	/**
	 * Polynomial constructor.
	 *
	 * @param array|null $num
	 * @param int|null   $shift
	 */
	public function __construct(array $num = null, int $shift = null){
		$this->setNum($num ?? [1], $shift);
	}

	/**
	 * @return array
	 */
	public function getNum():array {
		return $this->num;
	}

	/**
	 * @param array    $num
	 * @param int|null $shift
	 *
	 * @return \chillerlan\QRCode\Helpers\Polynomial
	 */
	public function setNum(array $num, int $shift = null):Polynomial {
		$offset = 0;
		$numCount = count($num);

		while($offset < $numCount && $num[$offset] === 0){
			$offset++;
		}

		$this->num = array_fill(0, $numCount - $offset + ($shift ?? 0), 0);

		for($i = 0; $i < $numCount - $offset; $i++){
			$this->num[$i] = $num[$i + $offset];
		}

		return $this;
	}

	/**
	 * @param array $e
	 *
	 * @return \chillerlan\QRCode\Helpers\Polynomial
	 */
	public function multiply(array $e):Polynomial {
		$n = array_fill(0, count($this->num) + count($e) - 1, 0);

		foreach($this->num as $i => $vi){
			$vi = $this->glog($vi);

			foreach($e as $j => $vj){
				$n[$i + $j] ^= $this->gexp($vi + $this->glog($vj));
			}

		}

		$this->setNum($n);

		return $this;
	}

	/**
	 * @param array $e
	 *
	 * @return \chillerlan\QRCode\Helpers\Polynomial
	 */
	public function mod(array $e):Polynomial{
		$n = $this->num;

		if(count($n) - count($e) < 0){
			return $this;
		}

		$ratio = $this->glog($n[0]) - $this->glog($e[0]);

		foreach($e as $i => $v){
			$n[$i] ^= $this->gexp($this->glog($v) + $ratio);
		}

		$this->setNum($n)->mod($e);

		return $this;
	}

	/**
	 * @param int $n
	 *
	 * @return int
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function glog(int $n):int {

		if($n < 1){
			throw new QRCodeException('log('.$n.')');
		}

		return Polynomial::table[$n][1];
	}

	/**
	 * @param int $n
	 *
	 * @return int
	 */
	public function gexp(int $n):int {

		if($n < 0){
			$n += 255;
		}
		elseif($n >= 256){
			$n -= 255;
		}

		return Polynomial::table[$n][0];
	}

}
