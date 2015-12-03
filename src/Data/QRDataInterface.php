<?php
/**
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
 * Interface QRDataInterface
 */
interface QRDataInterface{

	/**
	 * @param \codemasher\QRCode\BitBuffer $buffer
	 */
	public function write(BitBuffer &$buffer);

	/**
	 * @return int
	 */
	public function getLength();

	/**
	 * @param $type
	 *
	 * @return int
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function getLengthInBits($type);

}