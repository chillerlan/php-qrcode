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

class StringTest extends \PHPUnit_Framework_TestCase{

	/**
	 * @var \chillerlan\QRCode\Output\QRStringOptions
	 */
	protected $options;

	protected function setUp(){
		$this->options = new QRStringOptions;
	}

	public function testOptionsInstance(){
		$this->assertInstanceOf(QRStringOptions::class, $this->options);
		$this->assertEquals(QRCode::OUTPUT_STRING_HTML, $this->options->type);
	}

	public function stringDataProvider(){
		return [
			[QRCode::OUTPUT_STRING_HTML, true,  'foobar', 'str1.html'],
			[QRCode::OUTPUT_STRING_HTML, false, 'foobar', 'str2.html'],
			[QRCode::OUTPUT_STRING_HTML, true,  'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', 'str3.html'],
			[QRCode::OUTPUT_STRING_HTML, false, 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', 'str4.html'],
			[QRCode::OUTPUT_STRING_JSON, false, 'foobar', 'str1.json'],
			[QRCode::OUTPUT_STRING_JSON, false, 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', 'str2.json'],
			[QRCode::OUTPUT_STRING_TEXT, false, 'foobar', 'str1.txt'],
			[QRCode::OUTPUT_STRING_TEXT, false, 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', 'str2.txt'],
		];
	}

	/**
	 * @dataProvider stringDataProvider
	 */
	public function testStringOutput($type, $omitEndTag, $data, $expected){
		$this->options->type = $type;
		$this->options->htmlOmitEndTag = $omitEndTag;
		$this->assertEquals(file_get_contents(__DIR__.'/string/'.$expected), (new QRCode($data, new QRString($this->options)))->output());
	}

	/**
	 * @expectedException \chillerlan\QRCode\Output\QRCodeOutputException
	 * @expectedExceptionMessage Invalid string output type!
	 */
	public function testOutputTypeException(){
		$this->options->type = 'foo';
		new QRString($this->options);
	}

	/**
	 * @expectedException \chillerlan\QRCode\Output\QRCodeOutputException
	 * @expectedExceptionMessage Invalid matrix!
	 */
	public function testSetMatrixException(){
		(new QRString)->setMatrix([]);
	}

}
