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
	use Container;

	/**
	 * @var int
	 */
	protected $errorCorrectLevel = QRCode::ERROR_CORRECT_LEVEL_M;

	/**
	 * @var int
	 */
	protected $typeNumber = null;

}
