<?php
/**
 * Interface QRDataInterface
 *
 * @filesource   QRDataInterface.php
 * @created      01.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace codemasher\QRCode\Data;

use codemasher\QRCode\BitBuffer;

/**
 *
 */
interface QRDataInterface{

	/**
	 * @param \codemasher\QRCode\BitBuffer $buffer
	 */
	public function write(BitBuffer &$buffer);

	/**
	 * @param $type
	 *
	 * @return int
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function getLengthInBits($type);

}