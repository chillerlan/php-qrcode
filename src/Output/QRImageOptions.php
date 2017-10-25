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
 *
 */
class QRImageOptions extends QROutputOptionsAbstract{

	protected $type = QRCode::OUTPUT_IMAGE_PNG;

	protected $base64 = true;

	protected $pixelSize = 5;
	protected $marginSize = 5;

	// not supported by jpg
	protected $transparent = true;

	protected $fgRed   = 0;
	protected $fgGreen = 0;
	protected $fgBlue  = 0;

	protected $bgRed   = 255;
	protected $bgGreen = 255;
	protected $bgBlue  = 255;

	protected $pngCompression = -1;
	protected $jpegQuality = 85;

}
