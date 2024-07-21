<?php
/**
 * CssColorModuleValueProviderTrait.php
 *
 * @created      04.05.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Output;

/**
 * A data provider for use in tests that include CssColorModuleValueTrait
 *
 * @see \chillerlan\QRCode\Output\CssColorModuleValueTrait
 */
trait CssColorModuleValueProviderTrait{

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

}
