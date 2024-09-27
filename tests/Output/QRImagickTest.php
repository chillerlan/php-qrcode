<?php
/**
 * Class QRImagickTest
 *
 * @created      04.07.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRImagick, QROutputInterface};
use chillerlan\Settings\SettingsContainerInterface;
use Imagick;
use function extension_loaded;

/**
 * Tests the QRImagick output module
 */
final class QRImagickTest extends QROutputTestAbstract{

	protected function setUp():void{

		if(!extension_loaded('imagick')){
			$this::markTestSkipped('ext-imagick not loaded');
		}

		parent::setUp();
	}

	protected function getOutputInterface(
		SettingsContainerInterface|QROptions $options,
		QRMatrix                             $matrix,
	):QROutputInterface{
		return new QRImagick($options, $matrix);
	}

	public static function moduleValueProvider():array{
		return [
			'invalid: wrong type'            => [[], false],
			'valid: hex color, numeric (3)'  => ['#123', true],
			'valid: hex color (3)'           => ['#abc', true],
			'valid: hex color (4)'           => ['#abcd', true],
			'valid: hex color (6)'           => ['#aabbcc', true],
			'valid: hex color (8)'           => ['#aabbccdd', true],
			'valid: hex color, numeric (8)'  => ['#11bb33dd', true],
			'valid: hex color (32)'          => ['#aaaaaaaabbbbbbbbccccccccdddddddd', true],
			'invalid: hex color (non-hex)'   => ['#aabbcxyz', false],
			'invalid: hex color (too short)' => ['#aa', false],
			'invalid: hex color (too long)'  => ['#aaaaaaaabbbbbbbbccccccccdddddddd00', false],
			'invalid: hex color (5)'         => ['#aabbc', false],
			'invalid: hex color (7)'         => ['#aabbccd', false],
			'valid: rgb(...%)'               => ['rgb(100.0%, 0.0%, 0.0%)', true],
			'valid: rgba(...)'               => ['  rgba(255, 0, 0,    1.0)  ', true],
			'valid: hsb(...)'                => ['hsb(33.3333%, 100%,  75%)', true],
			'valid: hsla(...)'               => ['hsla(120, 255,   191.25, 1.0)', true],
			'invalid: rgba(non-numeric)'     => ['rgba(255, 0, whatever, 0, 1.0)', false],
			'invalid: rgba(extra-char)'      => ['rgba(255, 0, 0, 1.0);', false],
			'valid: csscolor'                => ['purple', true],
			'invalid: c5s c0lor'             => ['c5sc0lor', false],
		];
	}

	public function testSetModuleValues():void{

		$this->options->moduleValues = [
			// data
			QRMatrix::M_DATA_DARK => '#4A6000',
			QRMatrix::M_DATA      => '#ECF9BE',
		];

		$this->outputInterface = $this->getOutputInterface($this->options, $this->matrix);
		$this->outputInterface->dump();

		/** @phpstan-ignore-next-line */
		$this::assertTrue(true); // tricking the code coverage
	}

	public function testOutputGetResource():void{
		$this->options->returnResource = true;
		$this->outputInterface         = $this->getOutputInterface($this->options, $this->matrix);

		$this::assertInstanceOf(Imagick::class, $this->outputInterface->dump());
	}

}
