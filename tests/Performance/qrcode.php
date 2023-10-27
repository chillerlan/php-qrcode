<?php
/**
 * Tests the overall performance of the QRCode class
 *
 * @created      19.10.2023
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
use function mb_substr;
use function printf;
use function sprintf;
use function str_repeat;
use function str_replace;
use const JSON_PRETTY_PRINT;

require_once __DIR__.'/../../vendor/autoload.php';

// excerpt from QRCodeReaderTestAbstract
$generator = new class () {
	use QRMaxLengthTrait;

	public function dataProvider():Generator{

		$dataModeData = [
			Mode::NUMBER   => str_repeat('0123456789', 750),
			Mode::ALPHANUM => str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:', 100),
			Mode::KANJI    => str_repeat('漂う花の香り', 350),
			Mode::HANZI    => str_repeat('无可奈何燃花作香', 250),
			Mode::BYTE     => str_repeat('https://www.youtube.com/watch?v=dQw4w9WgXcQ ', 100),
		];

		foreach(Mode::INTERFACES as $dataMode => $dataModeInterface){
			$dataModeName = str_replace('chillerlan\\QRCode\\Data\\', '', $dataModeInterface);

			for($v = 1; $v <= 40; $v++){
				$version = new Version($v);

				foreach([EccLevel::L, EccLevel::M, EccLevel::Q, EccLevel::H] as $ecc){
					$eccLevel = new EccLevel($ecc);

					yield sprintf('version %2s%s (%s)', $version, $eccLevel, $dataModeName) => [
						$version->getVersionNumber(),
						$eccLevel,
						$dataModeInterface,
						$dataModeName,
						mb_substr($dataModeData[$dataMode], 0, self::getMaxLengthForMode($dataMode, $version, $eccLevel)),
					];
				}
			}
		}

	}

};

$test = new PerformanceTest(100);
$json = [];

foreach($generator->dataProvider() as $key => [$version, $eccLevel, $dataModeInterface, $dataModeName, $data]){

	$options = new QROptions([
		'outputType' => QROutputInterface::STRING_JSON,
		'version'    => $version,
		'eccLevel'   => $eccLevel->getLevel(),
	]);

	$test->run(fn() => (new QRCode($options))->addSegment(new $dataModeInterface($data))->render());

	printf("%s: %01.3fms\n", $key, $test->getResult());
	$json[$dataModeName][(string)$eccLevel][$version] = $test->getResult();
}

file_put_contents(__DIR__.'/performance_qrcode.json', json_encode($json, JSON_PRETTY_PRINT));
