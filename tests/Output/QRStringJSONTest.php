<?php
/**
 * Class QRStringJSONTest
 *
 * @created      11.12.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Output\QRStringJSON;
use PHPUnit\Framework\Attributes\DataProvider;
use function extension_loaded;

/**
 *
 */
final class QRStringJSONTest extends QROutputTestAbstract{

	protected string $FQN = QRStringJSON::class;

	/**
	 * @inheritDoc
	 */
	protected function setUp():void{
		// just in case someone's running this on some weird distro that's been compiled without ext-json
		if(!extension_loaded('json')){
			$this::markTestSkipped('ext-json not loaded');
		}

		parent::setUp();
	}

	public static function moduleValueProvider():array{
		return [[null, false]];
	}

	#[DataProvider('moduleValueProvider')]
	public function testValidateModuleValues(mixed $value, bool $expected):void{
		$this::markTestSkipped('N/A (JSON test)');
	}

	public function testSetModuleValues():void{
		$this::markTestSkipped('N/A (JSON test)');
	}

}
