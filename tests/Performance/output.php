<?php
/**
 * Tests the performance of the built-in output classes
 *
 * @created      16.10.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Performance;

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\{EccLevel, Mode, Version};
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCodeTest\QRMaxLengthTrait;
use Generator;
use function file_put_contents;
use function json_encode;
use function printf;
use function sprintf;
use function str_repeat;
use function str_replace;
use function substr;
use const JSON_PRETTY_PRINT;

require_once __DIR__.'/../../vendor/autoload.php';


// excerpt from QRCodeReaderTestAbstract
$generator = new class () {
	use QRMaxLengthTrait;

	public function dataProvider():Generator{
		$str      = str_repeat('https://www.youtube.com/watch?v=dQw4w9WgXcQ ', 100);
		$eccLevel = new EccLevel(EccLevel::L);

		for($v = 5; $v <= 40; $v += 5){
			$version  = new Version($v);
			foreach(QROutputInterface::MODES as $outputType => $FQN){
				$name = str_replace('chillerlan\\QRCode\\Output\\', '', $FQN);

				yield sprintf('version %2s: %-14s', $version, $name) => [
					$version->getVersionNumber(),
					$outputType,
					$FQN,
					substr($str, 0, self::getMaxLengthForMode(Mode::BYTE, $version, $eccLevel)),
					$name,
				];
			}
		}

	}

};

$test = new PerformanceTest(100);
$json = [];

foreach($generator->dataProvider() as $key => [$version, $outputType, $FQN, $data, $name]){

	$options = new QROptions([
		'version'             => $version,
		'outputType'          => $outputType,
		'connectPaths'        => true,
		'drawLightModules'    => true,
		'drawCircularModules' => true,
		'gdImageUseUpscale'   => false, // set to false to allow proper comparison
	]);

	$qrcode = new QRCode($options);
	$qrcode->addByteSegment($data);
	$matrix = $qrcode->getQRMatrix();

	$test->run(fn() => (new $FQN($options, $matrix))->dump());

	printf("%s: %8.3fms\n", $key, $test->getResult());
	$json[$name][$version] = $test->getResult();
}

file_put_contents(__DIR__.'/performance_output.json', json_encode($json, JSON_PRETTY_PRINT));
