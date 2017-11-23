<?php
/**
 * Interface QROutputInterface,
 *
 * @filesource   QROutputInterface.php
 * @created      02.12.2015
 * @package      chillerlan\QRCode\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;

/**
 *
 */
interface QROutputInterface{

	/**
	 * @return mixed
	 */
	public function dump();

}
