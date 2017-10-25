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
 * @property string $type
 * @property string $htmlRowTag
 * @property bool   $htmlOmitEndTag
 * @property string $fgColor
 * @property string $bgColor
 * @property int    $pixelSize
 * @property int    $marginSize
 * @property string $cssClass
 */
class QRMarkupOptions extends QROutputOptionsAbstract{

	/**
	 * QRCode::OUTPUT_MARKUP_XXXX where XXXX = HTML, SVG
	 *
	 * @var string
	 */
	protected $type = QRCode::OUTPUT_MARKUP_SVG;

	/**
	 * the shortest available semanically correct row (block) tag to not bloat the output
	 *
	 * @var string
	 */
	protected $htmlRowTag = 'p';

	/**
	 * the closing tag may be omitted (moar bloat!)
	 *
	 * @var bool
	 */
	protected $htmlOmitEndTag = true;

	/**
	 * foreground color
	 *
	 * @var string
	 */
	protected $fgColor = '#000';

	/**
	 * background color
	 *
	 * @var string
	 */
	protected $bgColor = '#fff';

	/**
	 * size of a QR code pixel
	 *
	 * @var int
	 */
	protected $pixelSize = 5;

	/**
	 * margin around the QR code
	 *
	 * @var int
	 */
	protected $marginSize = 5;

	/**
	 * a common css class
	 *
	 * @var string
	 */
	protected $cssClass;

}
