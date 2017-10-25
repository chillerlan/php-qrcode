<?php
/**
 * Class QROutputOptionsAbstract
 *
 * @filesource   QROutputOptionsAbstract.php
 * @created      17.12.2016
 * @package      chillerlan\QRCode\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Container;

/**
 * @property string $type
 * @property string $eol
 * @property string $cachefile
 */
abstract class QROutputOptionsAbstract{
	use Container;

	/**
	 * Output type, determined by the used interface
	 *
	 * QRCode::OUTPUT_...
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * newline string
	 *
	 * @var string
	 */
	protected $eol = PHP_EOL;

	/**
	 * /path/to/cache.file
	 *
	 * @var string
	 */
	protected $cachefile;

}
