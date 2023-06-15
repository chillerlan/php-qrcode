<?php
/**
 * reader.php
 *
 * @created      25.04.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 */

use chillerlan\QRCode\{QRCode, QROptions};

require_once __DIR__.'/../vendor/autoload.php';

// please excuse the IDE yelling https://youtrack.jetbrains.com/issue/WI-66549
$options = new QROptions;
$options->readerUseImagickIfAvailable = false;
$options->readerGrayscale             = true;
$options->readerIncreaseContrast      = true;

try{
	$result = (new QRCode($options))->readFromFile(__DIR__.'/../.github/images/example_image.png');

	var_dump($result);
}
catch(Throwable $e){
	echo $e->getMessage();
}

exit;
