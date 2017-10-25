<?php
/**
 * Class QRStringOptions
 *
 * @filesource   QRStringOptions.php
 * @created      08.12.2015
 * @package      chillerlan\QRCode\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\QRCode;

/**
 * @property string $type
 * @property string $textDark
 * @property string $textLight
 */
class QRStringOptions extends QROutputOptionsAbstract{

	/**
	 * QRCode::OUTPUT_STRING_XXXX where XXXX = TEXT, JSON
	 *
	 * @var string
	 */
	protected $type = QRCode::OUTPUT_STRING_JSON;

	/**
	 * string substitute for dark
	 *
	 * @var string
	 */
	protected $textDark = '🔴';

	/**
	 * string substitute for light
	 *
	 * @var string
	 */
	protected $textLight = '⭕';

}
