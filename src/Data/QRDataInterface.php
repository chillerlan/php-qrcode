<?php
/**
 * Interface QRDataInterface
 *
 * @filesource   QRDataInterface.php
 * @created      01.12.2015
 * @package      chillerlan\QRCode\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\BitBuffer;

/**
 * @property string data
 * @property int    dataLength
 * @property int    mode
 */
interface QRDataInterface{

	/**
	 * @param \chillerlan\QRCode\BitBuffer $buffer
	 * @return void
	 */
	public function write(BitBuffer &$buffer);

	/**
	 * @param $type
	 *
	 * @return int
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public function getLengthInBits(int $type):int;

}
