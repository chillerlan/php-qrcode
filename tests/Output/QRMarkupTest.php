<?php
/**
 * Class QRMarkupTest
 *
 * @filesource   QRMarkupTest.php
 * @created      24.12.2017
 * @package      chillerlan\QRCodeTest\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\{QRCode, Output\QRMarkup};

class QRMarkupTest extends QROutputTestAbstract{

	protected string $FQCN = QRMarkup::class;

	public function types():array{
		return [
			'html' => [QRCode::OUTPUT_MARKUP_HTML],
			'svg'  => [QRCode::OUTPUT_MARKUP_SVG],
		];
	}

	/**
	 * @dataProvider types
	 */
	public function testMarkupOutputFile(string $type):void{
		$this->options->outputType = $type;
		$this->options->cachefile  = $this::cachefile.$type;
		$this->setOutputInterface();
		$data = $this->outputInterface->dump();

		$this::assertSame($data, file_get_contents($this->options->cachefile));
	}

	/**
	 * @dataProvider types
	 */
	public function testMarkupOutput(string $type):void{
		$this->options->outputType = $type;
		$this->setOutputInterface();

		$expected = explode($this->options->eol, file_get_contents($this::cachefile.$type));
		// cut off the doctype & head
		array_shift($expected);

		if($type === QRCode::OUTPUT_MARKUP_HTML){
			// cut off the </body> tag
			array_pop($expected);
		}

		$expected = implode($this->options->eol, $expected);

		$this::assertSame(trim($expected), trim($this->outputInterface->dump()));
	}

	public function testSetModuleValues():void{

		$this->options->moduleValues = [
			// data
			1024 => '#4A6000',
			4    => '#ECF9BE',
		];

		$this->setOutputInterface();
		$data = $this->outputInterface->dump();
		$this::assertStringContainsString('#4A6000', $data);
		$this::assertStringContainsString('#ECF9BE', $data);
	}

}
