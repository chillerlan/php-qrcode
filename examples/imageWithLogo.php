<?php
/**
 *
 * @filesource   imageWithLogo.php
 * @created      18.11.2020
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeExamples;

use chillerlan\QRCode\{QRCode, QROptions};

require_once __DIR__.'/../vendor/autoload.php';

$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';
/**
 * @property int $logoSpaceWidth
 * @property int $logoSpaceHeight
 *
 * @noinspection PhpIllegalPsrClassPathInspection
 */
class LogoOptions extends QROptions{
	// size in QR modules, multiply with QROptions::$scale for pixel size
	protected $logoSpaceWidth;
	protected $logoSpaceHeight;
}

$options = new LogoOptions;

$options->version          = 7;
$options->eccLevel         = QRCode::ECC_H;
$options->imageBase64      = false;
$options->logoSpaceWidth   = 13;
$options->logoSpaceHeight  = 13;
$options->scale            = 5;
$options->imageTransparent = false;

header('Content-type: image/png');

$qrOutputInterface = new QRImageWithLogo($options, (new QRCode($options))->getMatrix($data));

// dump the output, with an additional logo
echo $qrOutputInterface->dump(null, __DIR__.'/octocat.png');
