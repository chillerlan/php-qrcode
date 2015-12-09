<?php
/**
 * Class QROptions
 *
 * @filesource   QROptions.php
 * @created      08.12.2015
 * @package      chillerlan\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode;

/**
 *
 */
class QROptions{

	/**
	 * mandatory
	 *
	 * @var \chillerlan\QRCode\Output\QROutputInterface
	 */
	public $output = null ;

	/**
	 * @var int
	 */
	public $errorCorrectLevel = QRConst::ERROR_CORRECT_LEVEL_M;

	/**
	 * @var int
	 */
	public $typeNumber = null;

}
