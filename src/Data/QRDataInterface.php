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

	const MODE_NUMBER   = 1 << 0;
	const MODE_ALPHANUM = 1 << 1;
	const MODE_BYTE     = 1 << 2;
	const MODE_KANJI    = 1 << 3;

	const MODE = [
		self::MODE_NUMBER   => 0,
		self::MODE_ALPHANUM => 1,
		self::MODE_BYTE     => 2,
		self::MODE_KANJI    => 3,
	];

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
