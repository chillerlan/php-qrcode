<?php
/**
 * Class NumberTest
 *
 * @created      24.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\Data\Number;

/**
 * Tests the Number class
 */
final class NumberTest extends DataInterfaceTestAbstract{

	protected string $FQN      = Number::class;
	protected string $testdata = '0123456789';

	/**
	 * isNumber() should pass on any number and fail on anything else
	 */
	public function stringValidateProvider():array{
		return [
			['0123456789', true],
			['ABC123', false],
		];
	}

}
