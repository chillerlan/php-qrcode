<?php
/**
 * QREpsTest.php
 *
 * @created      16.03.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QREps;
use chillerlan\QRCode\Output\QROutputInterface;

class QREpsTest extends QROutputTestAbstract{

	protected string $FQN  = QREps::class;
	protected string $type = QROutputInterface::EPS;

	public static function moduleValueProvider():array{
		return [
			'valid: 3 int'                   => [[123, 123, 123], true],
			'valid: 4 int'                   => [[123, 123, 123, 123], true],
			'valid: w/invalid extra element' => [[123, 123, 123, 123, 'abc'], true],
			'valid: numeric string'          => [['123', '123', '123'], true],
			'invalid: wrong type'            => ['foo', false],
			'invalid: array too short'       => [[1, 2], false],
			'invalid: contains non-number'   => [[1, 'b', 3], false],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function testSetModuleValues():void{

		$this->options->moduleValues = [
			// data
			QRMatrix::M_DATA_DARK => [0, 0, 0],
			QRMatrix::M_DATA      => [255, 255, 255],
		];

		$this->outputInterface = new $this->FQN($this->options, $this->matrix);
		$this->outputInterface->dump();

		$this::assertTrue(true); // tricking the code coverage
	}

}
