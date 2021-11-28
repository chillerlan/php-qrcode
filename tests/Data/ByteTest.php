<?php
/**
 * Class ByteTest
 *
 * @created      24.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\Data\Byte;

/**
 * Tests the Byte class
 */
final class ByteTest extends DatainterfaceTestAbstract{

	protected string $FQN      = Byte::class;
	protected string $testdata = '[¯\_(ツ)_/¯]';

	/**
	 * @inheritDoc
	 */
	public function testInvalidDataException():void{
		$this->markTestSkipped('N/A');
	}

}
