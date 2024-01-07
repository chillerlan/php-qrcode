<?php
/**
 * Class QRMarkupHTMLTest
 *
 * @created      11.12.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRMarkupHTML, QROutputInterface};

/**
 *
 */
final class QRMarkupHTMLTest extends QRMarkupTestAbstract{

	protected string $type = QROutputInterface::MARKUP_HTML;

	protected function getOutputInterface(QROptions $options, QRMatrix $matrix):QROutputInterface{
		return new QRMarkupHTML($options, $matrix);
	}

}
