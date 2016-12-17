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

/**
 *
 */
abstract class QROutputOptionsAbstract{

	public $type;

	public $eol = PHP_EOL;

	public $cachefile = null;

}
