<?php
/**
 * Class QRImageOptions
 *
 * @filesource   QRImageOptions.php
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
 * @property bool $base64
 * @property int  $pixelSize
 * @property int  $marginSize
 * @property bool $transparent
 * @property int  $fgRed
 * @property int  $fgGreen
 * @property int  $fgBlue
 * @property int  $bgRed
 * @property int  $bgGreen
 * @property int  $bgBlue
 * @property int  $pngCompression
 * @property int  $jpegQuality
 */
class QRImageOptions extends QROutputOptionsAbstract{

	/**
	 * @var string
	 */
	protected $type = QRCode::OUTPUT_IMAGE_PNG;

	/**
	 * @var bool
	 */
	protected $base64 = true;

	/**
	 * @var int
	 */
	protected $pixelSize = 5;

	/**
	 * @var int
	 */
	protected $marginSize = 5;

	/**
	 * not supported by jpg
	 *
	 * @var bool
	 */
	protected $transparent = true;

	/**
	 * @var int
	 */
	protected $fgRed   = 0;

	/**
	 * @var int
	 */
	protected $fgGreen = 0;

	/**
	 * @var int
	 */
	protected $fgBlue  = 0;

	/**
	 * @var int
	 */
	protected $bgRed   = 255;

	/**
	 * @var int
	 */
	protected $bgGreen = 255;

	/**
	 * @var int
	 */
	protected $bgBlue  = 255;

	/**
	 * @var int
	 */
	protected $pngCompression = -1;

	/**
	 * @var int
	 */
	protected $jpegQuality = 85;

}
