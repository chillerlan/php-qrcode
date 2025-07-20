<?php
/**
 * Miscellaneous stubs for phan
 *
 * @created      25.01.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

// looks like some linux distros don't support AVIF?? (Ubuntu 25.04 PHP 8.4.5-ondrej)
function imageavif(\GdImage $image, mixed $file=null, int $quality=-1, int $speed=-1):bool{
	return true;
}
