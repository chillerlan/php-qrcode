<?php
/**
 * Class QRDataBenchmark
 *
 * @created      23.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeBenchmark;

use chillerlan\QRCode\Common\BitBuffer;
use chillerlan\QRCode\Data\QRData;
use PhpBench\Attributes\{BeforeMethods, Subject};

/**
 * Tests the QRMatrix write performance
 */
final class QRDataBenchmark extends BenchmarkAbstract{

	private QRData    $qrData;
	private BitBuffer $bitBuffer;

	public function initOptions():void{

		$options = [
			'version'  => $this->version->getVersionNumber(),
			'eccLevel' => $this->eccLevel->getLevel(),
		];

		$this->initQROptions($options);
	}

	public function initQRData():void{
		$this->qrData = new QRData($this->options, [new $this->modeFQCN($this->testData)]);
	}

	public function initBitBuffer():void{
		$this->bitBuffer = $this->qrData->getBitBuffer();
		$this->bitBuffer->read(4); // read data mode indicator
	}

	/**
	 * Tests the performance of QRData invovcation, includes QRData::writeBitBuffer()
	 */
	#[Subject]
	#[BeforeMethods(['assignParams', 'generateTestData', 'initOptions'])]
	public function invocation():void{
		new QRData($this->options, [new $this->modeFQCN($this->testData)]);
	}

	/**
	 * Tests the performance of QRData::writeMatrix(), includes QRMatrix::writeCodewords() and the ReedSolomonEncoder
	 */
	#[Subject]
	#[BeforeMethods(['assignParams', 'generateTestData', 'initOptions', 'initQRData'])]
	public function writeMatrix():void{
		$this->qrData->writeMatrix();
	}

	/**
	 * Tests the performance of QRDataModeInterface::decodeSegment()
	 *
	 * we need to clone the BitBuffer instance here because its internal state is modified during decoding
	 */
	#[Subject]
	#[BeforeMethods(['assignParams', 'generateTestData', 'initOptions', 'initQRData', 'initBitBuffer'])]
	public function decodeSegment():void{
		/** @noinspection PhpUndefinedMethodInspection */
		$this->modeFQCN::decodeSegment(clone $this->bitBuffer, $this->version->getVersionNumber());
	}

}
