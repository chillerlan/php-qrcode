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
 * @property int $errorCorrectLevel
 * @property int $typeNumber
 */
class QROptions{
	use Container;

	/**
	 * Error correct level
	 *
	 *   QRCode::ERROR_CORRECT_LEVEL_X where X is
	 *    L,   M,   Q,   H
	 *   7%, 15%, 25%, 30%
	 *
	 * @var int
	 */
	protected $errorCorrectLevel = QRCode::ERROR_CORRECT_LEVEL_M;

	/**
	 * Type number
	 *
	 *   QRCode::TYPE_XX where XX is 01 ... 10, null = auto
	 *
	 * @var int
	 */
	protected $typeNumber = null;

}
