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

	public $type = QRCode::OUTPUT_STRING_HTML;

	public $textDark = '#';

	public $textLight = ' ';

	public $eol = PHP_EOL;

	public $htmlRowTag = 'p';

	public $htmlOmitEndTag = true;

}
