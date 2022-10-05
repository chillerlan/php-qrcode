<?php
/**
 * Class KanjiTest
 *
 * @created      24.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\Data\Kanji;

/**
 * Tests the Kanji class
 */
final class KanjiTest extends DataInterfaceTestAbstract{

	protected string $FQN      = Kanji::class;
	protected string $testdata = '茗荷茗荷茗荷茗荷茗荷';

	/**
	 * isKanji() should pass on Kanji/SJIS characters and fail on everything else
	 */
	public function stringValidateProvider():array{
		return [
			['茗荷', true],
			['Ã', false],
			['ABC', false],
			['123', false],
		];
	}

}
