<?php
/**
 * Class QRStringTest
 *
 * @filesource   QRStringTest.php
 * @created      24.12.2017
 * @package      chillerlan\QRCodeTest\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Output\QRString;
use chillerlan\QRCode\QRCode;

class QRStringTest extends QROutputTestAbstract{

	protected $FQCN = QRString::class;

	public function types(){
		return [
			[QRCode::OUTPUT_STRING_JSON],
			[QRCode::OUTPUT_STRING_TEXT],
		];
	}

	/**
	 * @dataProvider types
	 * @param $type
	 */
	public function testStringOutput($type){
		$this->options->outputType = $type;
		$this->options->cachefile  = $this::cachefile.$type;
		$this->setOutputInterface();
		$data = $this->outputInterface->dump();

		$this->assertSame($data, file_get_contents($this->options->cachefile));
	}

}
