<?php
/**
 *
 * @filesource   StringTest.php
 * @created      08.02.2016
 * @package      chillerlan\QRCodeTest\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Output\QRString;
use chillerlan\QRCode\Output\QRStringOptions;
use chillerlan\QRCode\QRCode;

class StringTest extends OutputTestAbstract{

	protected $outputInterfaceClass = QRString::class;
	protected $outputOptionsClass   = QRStringOptions::class;

	public function testOptions(){
		$this->assertEquals(QRCode::OUTPUT_STRING_JSON, $this->options->type);
	}

	public function stringDataProvider(){
		return [
			[QRCode::OUTPUT_STRING_JSON, 'foobar', 'str1.json'],
			[QRCode::OUTPUT_STRING_JSON, 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', 'str2.json'],
			[QRCode::OUTPUT_STRING_TEXT, 'foobar', 'str1.txt'],
			[QRCode::OUTPUT_STRING_TEXT, 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', 'str2.txt'],
		];
	}

	/**
	 * @dataProvider stringDataProvider
	 */
	public function testStringOutput($type, $data, $expected){
		$this->options->type = $type;
		$this->assertEquals(file_get_contents(__DIR__.'/string/'.$expected), (new QRCode($data, new $this->outputInterfaceClass($this->options)))->output());
	}

}
