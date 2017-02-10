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

	/**
	 * @param array $matrix
	 *
	 * @return \chillerlan\QRCode\Output\QROutputInterface
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function setMatrix(array $matrix):QROutputInterface;

}
