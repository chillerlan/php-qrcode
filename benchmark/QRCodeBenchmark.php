<?php
/**
 * Class QRCodeBenchmark
 *
 * @created      23.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeBenchmark;

use chillerlan\QRCode\QRCode;
use PhpBench\Attributes\{BeforeMethods, Subject};

/**
 * Tests the overall performance of the QRCode class
 */
final class QRCodeBenchmark extends BenchmarkAbstract{

	public function initOptions():void{

		$options = [
			'version'  => $this->version->getVersionNumber(),
			'eccLevel' => $this->eccLevel->getLevel(),
		];

		$this->initQROptions($options);
	}

	#[Subject]
	#[BeforeMethods(['assignParams', 'generateTestData', 'initOptions'])]
	public function render():void{
		(new QRCode($this->options))->addSegment(new $this->modeFQCN($this->testData))->render();
	}

}
