<?php
/**
 *
 * @filesource   QRConst.php
 * @created      26.11.2015
 * @package      codemasher\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace codemasher\QRCode;

/**
 * Class QRConst
 */
class QRConst{

	const MODE_NUMBER = 1 << 0;
	const MODE_ALPHANUM = 1 << 1;
	const MODE_BYTE = 1 << 2;
	const MODE_KANJI = 1 << 3;

	const MASK_PATTERN000 = 0;
	const MASK_PATTERN001 = 1;
	const MASK_PATTERN010 = 2;
	const MASK_PATTERN011 = 3;
	const MASK_PATTERN100 = 4;
	const MASK_PATTERN101 = 5;
	const MASK_PATTERN110 = 6;
	const MASK_PATTERN111 = 7;

	const ERROR_CORRECT_LEVEL_L = 1; // 7%.
	const ERROR_CORRECT_LEVEL_M = 0; // 15%.
	const ERROR_CORRECT_LEVEL_Q = 3; // 25%.
	const ERROR_CORRECT_LEVEL_H = 2; // 30%.

}
