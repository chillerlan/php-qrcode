<?php
/**
 * Class QRMarkupSVGTest
 *
 * @created      11.12.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Output\{QRMarkupSVG, QROutputInterface};

/**
 *
 */
final class QRMarkupSVGTest extends QRMarkupTestAbstract{

	protected string $FQN  = QRMarkupSVG::class;
	protected string $type = QROutputInterface::MARKUP_SVG;

}
