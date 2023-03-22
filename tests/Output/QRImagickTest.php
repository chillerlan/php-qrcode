<?php
/**
 * Class QRImagickTest
 *
 * @created      04.07.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 *
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRImagick, QROutputInterface};
use Imagick;

/**
 * Tests the QRImagick output module
 */
final class QRImagickTest extends QROutputTestAbstract{

	protected string $FQN  = QRImagick::class;
	protected string $type = QROutputInterface::IMAGICK;

	/**
	 * @inheritDoc
	 */
	protected function setUp():void{

		if(!extension_loaded('imagick')){
			$this::markTestSkipped('ext-imagick not loaded');
		}

		parent::setUp();
	}

	public static function moduleValueProvider():array{
		return [
			'invalid: wrong type'            => [[], false],
			'valid: hex color (3)'           => ['#abc', true],
			'valid: hex color (4)'           => ['#abcd', true],
			'valid: hex color (6)'           => ['#aabbcc', true],
			'valid: hex color (8)'           => ['#aabbccdd', true],
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

	/**
	 * @inheritDoc
	 */
	public function testSetModuleValues():void{

		$this->options->moduleValues = [
			// data
			QRMatrix::M_DATA_DARK => '#4A6000',
			QRMatrix::M_DATA      => '#ECF9BE',
		];

		$this->outputInterface = new $this->FQN($this->options, $this->matrix);
		$this->outputInterface->dump();

		$this::assertTrue(true); // tricking the code coverage
	}

	public function testOutputGetResource():void{
		$this->options->returnResource = true;
		$this->outputInterface         = new $this->FQN($this->options, $this->matrix);

		$this::assertInstanceOf(Imagick::class, $this->outputInterface->dump());
	}

}
