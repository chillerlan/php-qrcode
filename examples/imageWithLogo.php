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
use chillerlan\QRCode\Common\EccLevel;

require_once __DIR__.'/../vendor/autoload.php';

$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';
/**
 * @property int $logoSpaceWidth
 * @property int $logoSpaceHeight
 *
 * @noinspection PhpIllegalPsrClassPathInspection
 */
class LogoOptions extends QROptions{
	protected int $logoSpaceWidth;
	protected int $logoSpaceHeight;
}

$options = new LogoOptions;

$options->version          = 7;
$options->eccLevel         = EccLevel::H;
$options->imageBase64      = false;
$options->logoSpaceWidth   = 13;
$options->logoSpaceHeight  = 13;
$options->scale            = 5;
$options->imageTransparent = false;

$qrcode = new QRCode($options);
$qrcode->addByteSegment($data);

header('Content-type: image/png');

$qrOutputInterface = new QRImageWithLogo($options, $qrcode->getMatrix());

// dump the output, with an additional logo
echo $qrOutputInterface->dump(null, __DIR__.'/octocat.png');
