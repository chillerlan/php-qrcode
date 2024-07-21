<?php
/**
 * Class QRMarkupSVGTest
 *
 * @created      11.12.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRMarkupSVG, QROutputInterface};
use chillerlan\Settings\SettingsContainerInterface;

final class QRMarkupSVGTest extends QRMarkupTestAbstract{

	protected function getOutputInterface(
		SettingsContainerInterface|QROptions $options,
		QRMatrix                             $matrix,
	):QROutputInterface{
		return new QRMarkupSVG($options, $matrix);
	}

	public static function moduleValueProvider():array{
		return [
			// css colors from parent
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

			// SVG
			'valid: url(#id)'                => ['url(#fillGradient)', true],
			'invalid: url(link)'             => ['url(https://example.com/noop)', false],
		];
	}

}
