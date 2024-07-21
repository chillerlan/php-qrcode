<?php
/**
 * RGBArrayModuleValueProviderTrait.php
 *
 * @created      06.05.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Output;

/**
 * A data provider for use in tests that include RGBArrayModuleValueTrait
 *
 * @see \chillerlan\QRCode\Output\RGBArrayModuleValueTrait
 */
trait RGBArrayModuleValueProviderTrait{

	public static function moduleValueProvider():array{
		return [
			'valid: int'                     => [[123, 123, 123], true],
			'valid: w/invalid extra element' => [[123, 123, 123, 'abc'], true],
			'valid: numeric string'          => [['123', '123', '123'], true],
			'invalid: wrong type'            => ['foo', false],
			'invalid: array too short'       => [[1, 2], false],
			'invalid: contains non-number'   => [[1, 'b', 3], false],
		];
	}

}
