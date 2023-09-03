<?php
/**
 * multi/mixed mode example
 *
 * @created      31.01.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

require_once __DIR__.'/../vendor/autoload.php';

// make sure we run in UTF-8
// ideally, this should be set in php.ini => internal_encoding/default_charset or mbstring.internal_encoding
mb_internal_encoding('UTF-8');

// please excuse the IDE yelling https://youtrack.jetbrains.com/issue/WI-66549
$options = new QROptions;
$options->outputBase64 = false;
$options->connectPaths = true;

$qrcode = (new QRCode($options))
	->addNumericSegment('1312')
	->addByteSegment("\n")
	->addAlphaNumSegment('ACAB')
	->addByteSegment("\n")
	->addKanjiSegment('すべての警官はろくでなしです')
	->addByteSegment("\n")
	->addHanziSegment('所有警察都是混蛋')
	->addByteSegment("\n")
	->addByteSegment('https://www.bundesverfassungsgericht.de/SharedDocs/Pressemitteilungen/DE/2016/bvg16-036.html')
;


$out = $qrcode->render();


if(PHP_SAPI !== 'cli'){
	header('Content-type: image/svg+xml');

	if(extension_loaded('zlib')){
		header('Vary: Accept-Encoding');
		header('Content-Encoding: gzip');
		$out = gzencode($out, 9);
	}
}

echo $out;

exit;
