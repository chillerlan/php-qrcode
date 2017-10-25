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

	protected $type = QRCode::OUTPUT_MARKUP_SVG;

	protected $htmlRowTag = 'p';

	protected $htmlOmitEndTag = true;

	protected $fgColor = '#000';

	protected $bgColor = '#fff';

	protected $pixelSize = 5;

	protected $marginSize = 5;

	protected $cssClass = '';

}
