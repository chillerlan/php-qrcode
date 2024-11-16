<?php
/**
 * reader.php
 *
 * @created      25.04.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 */
declare(strict_types=1);

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
	printf("%s(%s): %s\n%s", $e->getFile(), $e->getLine(), $e->getMessage(), $e->getTraceAsString());
}

exit;
