<?php
/**
 *
 * @filesource   RSBlock.php
 * @created      26.11.2015
 * @package      codemasher\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace codemasher\QRCode;

use codemasher\QRCode\QRConst;
use codemasher\QRCode\QRCodeException;

/**
 * Class RSBlock
 */
class RSBlock{

	/**
	 * @var int
	 */
	public $totalCount;

	/**
	 * @var int
	 */
	public $dataCount;

	/**
	 * @var array
	 */
	protected $QR_RS_BLOCK_TABLE = [

		// L
		// M
		// Q
		// H

		// 1
		[1, 26, 19],
		[1, 26, 16],
		[1, 26, 13],
		[1, 26, 9],

		// 2
		[1, 44, 34],
		[1, 44, 28],
		[1, 44, 22],
		[1, 44, 16],

		// 3
		[1, 70, 55],
		[1, 70, 44],
		[2, 35, 17],
		[2, 35, 13],

		// 4
		[1, 100, 80],
		[2, 50, 32],
		[2, 50, 24],
		[4, 25, 9],

		// 5
		[1, 134, 108],
		[2, 67, 43],
		[2, 33, 15, 2, 34, 16],
		[2, 33, 11, 2, 34, 12],

		// 6
		[2, 86, 68],
		[4, 43, 27],
		[4, 43, 19],
		[4, 43, 15],

		// 7
		[2, 98, 78],
		[4, 49, 31],
		[2, 32, 14, 4, 33, 15],
		[4, 39, 13, 1, 40, 14],

		// 8
		[2, 121, 97],
		[2, 60, 38, 2, 61, 39],
		[4, 40, 18, 2, 41, 19],
		[4, 40, 14, 2, 41, 15],

		// 9
		[2, 146, 116],
		[3, 58, 36, 2, 59, 37],
		[4, 36, 16, 4, 37, 17],
		[4, 36, 12, 4, 37, 13],

		// 10
		[2, 86, 68, 2, 87, 69],
		[4, 69, 43, 1, 70, 44],
		[6, 43, 19, 2, 44, 20],
		[6, 43, 15, 2, 44, 16],

	];

	/**
	 * @param $typeNumber
	 * @param $errorCorrectLevel
	 *
	 * @return array
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function getRSBlocks($typeNumber, $errorCorrectLevel){

		switch($errorCorrectLevel){
			case QRConst::ERROR_CORRECT_LEVEL_L: $rsBlock = 0; break;
			case QRConst::ERROR_CORRECT_LEVEL_M: $rsBlock = 1; break;
			case QRConst::ERROR_CORRECT_LEVEL_Q: $rsBlock = 2; break;
			case QRConst::ERROR_CORRECT_LEVEL_H: $rsBlock = 3; break;
			default:
				throw new QRCodeException('tn:'.$typeNumber.'/ecl:'.$errorCorrectLevel);
		}

		$rsBlock = $this->QR_RS_BLOCK_TABLE[($typeNumber - 1) * 4 + $rsBlock];

		$list = [];
		$length = count($rsBlock) / 3;
		for($i = 0; $i < $length; $i++){
			$count = $rsBlock[$i * 3 + 0];
			$totalCount = $rsBlock[$i * 3 + 1];
			$dataCount = $rsBlock[$i * 3 + 2];

			for($j = 0; $j < $count; $j++){
				$list[] = [$totalCount, $dataCount];
			}
		}

		return $list;
	}


}
