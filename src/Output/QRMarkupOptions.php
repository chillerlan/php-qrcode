<?php
/**
 * Class QRMarkupOptions
 *
 * @filesource   QRMarkupOptions.php
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
class QRMarkupOptions extends QROutputOptionsAbstract{

	public $type = QRCode::OUTPUT_MARKUP_SVG;

	public $htmlRowTag = 'p';

	public $htmlOmitEndTag = true;

	public $fgColor = '#000';

	public $bgColor = '#fff';

	public $pixelSize = 5;

	public $marginSize = 5;

	public $cssClass = '';

}
