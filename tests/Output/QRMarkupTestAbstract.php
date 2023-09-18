<?php
/**
 * Class QRMarkupTestAbstract
 *
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Data\QRMatrix;

/**
 * Tests the QRMarkup output module
 */
abstract class QRMarkupTestAbstract extends QROutputTestAbstract{

	public static function moduleValueProvider():array{
		return [
			'invalid: wrong type'            => [[], false],
			'valid: hex color (3)'           => ['#abc', true],
			'valid: hex color (4)'           => ['#abcd', true],
			'valid: hex color (6)'           => ['#aabbcc', true],
			'valid: hex color (8)'           => ['#aabbccdd', true],
			'invalid: hex color (non-hex)'   => ['#aabbcxyz', false],
			'invalid: hex color (too short)' => ['#aa', false],
			'invalid: hex color (5)'         => ['#aabbc', false],
			'invalid: hex color (7)'         => ['#aabbccd', false],
			'valid: rgb(...%)'               => ['rgb(100.0%, 0.0%, 0.0%)', true],
			'valid: rgba(...)'               => ['  rgba(255, 0, 0,    1.0)  ', true],
			'valid: hsl(...)'                => ['hsl(120, 60%, 50%)', true],
			'valid: hsla(...)'               => ['hsla(120, 255,   191.25, 1.0)', true],
			'invalid: rgba(non-numeric)'     => ['rgba(255, 0, whatever, 0, 1.0)', false],
			'invalid: rgba(extra-char)'      => ['rgba(255, 0, 0, 1.0);', false],
			'valid: csscolor'                => ['purple', true],
			'invalid: c5sc0lor'              => ['c5sc0lor', false],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function testSetModuleValues():void{
		$this->options->outputBase64     = false;
		$this->options->drawLightModules = true;
		$this->options->moduleValues     = [
			// data
			QRMatrix::M_DATA_DARK => '#4A6000',
			QRMatrix::M_DATA      => '#ECF9BE',
		];

		$this->outputInterface = new $this->FQN($this->options, $this->matrix);
		$data = $this->outputInterface->dump();
		$this::assertStringContainsString('#4A6000', $data);
		$this::assertStringContainsString('#ECF9BE', $data);
	}

}
