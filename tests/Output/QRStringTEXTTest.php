<?php
/**
 * Class QRStringTEXTTest
 *
 * @created      11.12.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;

/**
 *
 */
final class QRStringTEXTTest extends QRStringTestAbstract{

	protected string $type = QROutputInterface::STRING_TEXT;

	/**
	 * @inheritDoc
	 */
	public function testSetModuleValues():void{

		$this->options->moduleValues = [
			// data
			QRMatrix::M_DATA | QRMatrix::IS_DARK => 'A',
			QRMatrix::M_DATA                     => 'B',
		];

		$this->outputInterface = new $this->FQN($this->options, $this->matrix);
		$data                  = $this->outputInterface->dump();

		$this::assertStringContainsString('A', $data);
		$this::assertStringContainsString('B', $data);
	}

}
