<?php
/**
 * Class AlphaNumTest
 *
 * @created      24.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\Data\AlphaNum;

/**
 * Tests the AlphaNum class
 */
final class AlphaNumTest extends DataInterfaceTestAbstract{

	protected string $FQN      = AlphaNum::class;
	protected string $testdata = '0 $%*+-./:';

	/**
	 * isAlphaNum() should pass on the 45 defined characters and fail on anything else (e.g. lowercase)
	 */
	public function stringValidateProvider():array{
		return [
			['ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:', true],
			['abc', false],
		];
	}

}
