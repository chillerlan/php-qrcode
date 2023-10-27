<?php
/**
 * Tests the performance of the mask pattern penalty testing
 *
 * @created      18.10.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Performance;

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\{EccLevel, MaskPattern, Mode, Version};
use chillerlan\QRCodeTest\QRMaxLengthTrait;
use Generator;
use function file_put_contents;
use function json_encode;
use function printf;
use function sprintf;
use function str_repeat;
use function substr;
use const JSON_PRETTY_PRINT;

require_once __DIR__.'/../../vendor/autoload.php';

// excerpt from QRCodeReaderTestAbstract
$generator = new class () {
	use QRMaxLengthTrait;

	public function dataProvider():Generator{
		$str      = str_repeat('https://www.youtube.com/watch?v=dQw4w9WgXcQ ', 100);
		$eccLevel = new EccLevel(EccLevel::H);

		for($v = 1; $v <= 40; $v++){
			$version = new Version($v);

			yield sprintf('version %2s%s', $version, $eccLevel) => [
				$version->getVersionNumber(),
				$eccLevel->getLevel(),
				substr($str, 0, self::getMaxLengthForMode(Mode::BYTE, $version, $eccLevel)),
			];
		}
	}

};

$test = new PerformanceTest(100);
$json = [];

foreach($generator->dataProvider() as $key => [$version, $eccLevel, $data]){
	$qrcode = new QRCode(new QROptions(['version' => $version, 'eccLevel' => $eccLevel]));
	$qrcode->addByteSegment($data);
	$matrix = $qrcode->getQRMatrix();

	$test->run(fn() => MaskPattern::getBestPattern($matrix));

	printf("%s: %01.3fms\n", $key, $test->getResult());
	$json[$version] = $test->getResult();
}

file_put_contents(__DIR__.'/performance_maskpattern.json', json_encode($json, JSON_PRETTY_PRINT));
