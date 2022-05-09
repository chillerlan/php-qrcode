<?php
/**
 * reader.php
 *
 * @created      25.04.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeExamples;

use chillerlan\QRCode\{QRCode, QROptions};

require_once __DIR__.'/../vendor/autoload.php';

/** @var \chillerlan\Settings\SettingsContainerInterface $options */
$options = new QROptions;
$options->readerUseImagickIfAvailable = false;
$options->readerGrayscale = true;
$options->readerIncreaseContrast = true;

$result = (new QRCode($options))->readFromFile(__DIR__.'/../.github/images/example_image.png');

var_dump($result);
