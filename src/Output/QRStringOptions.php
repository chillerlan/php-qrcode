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
 *
 */
class QRStringOptions{

	public $type = QRCode::OUTPUT_STRING_TEXT;

	public $textDark = '#';

	public $textLight = ' ';

	public $textNewline = PHP_EOL;

}
