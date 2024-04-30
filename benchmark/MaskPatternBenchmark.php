<?php
/**
 * Class MaskPatternBenchmark
 *
 * @created      23.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeBenchmark;

use chillerlan\QRCode\Common\{MaskPattern, Mode};
use chillerlan\QRCode\Data\Byte;
use PhpBench\Attributes\{BeforeMethods, Subject};

/**
 * Tests the performance of the mask pattern penalty testing
 */
final class MaskPatternBenchmark extends BenchmarkAbstract{

	protected const DATAMODES = [Mode::BYTE => Byte::class];

	public function initOptions():void{

		$options = [
			'version'  => $this->version->getVersionNumber(),
			'eccLevel' => $this->eccLevel->getLevel(),
		];

		$this->initQROptions($options);
	}

	#[Subject]
	#[BeforeMethods(['assignParams', 'generateTestData', 'initOptions', 'initMatrix'])]
	public function getBestPattern():void{
		MaskPattern::getBestPattern($this->matrix);
	}

}
