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
			['foobar', QRCode::OUTPUT_STRING_HTML, '<p><b></b><b></b><b></b><b></b><b></b><b></b><b></b><i></i><i></i><i></i><b></b><b></b><b></b><i></i><b></b><b></b><b></b><b></b><b></b><b></b><b></b>'.PHP_EOL.'<p><b></b><i></i><i></i><i></i><i></i><i></i><b></b><i></i><b></b><b></b><b></b><i></i><i></i><i></i><b></b><i></i><i></i><i></i><i></i><i></i><b></b>'.PHP_EOL.'<p><b></b><i></i><b></b><b></b><b></b><i></i><b></b><i></i><i></i><b></b><i></i><i></i><b></b><i></i><b></b><i></i><b></b><b></b><b></b><i></i><b></b>'.PHP_EOL.'<p><b></b><i></i><b></b><b></b><b></b><i></i><b></b><i></i><i></i><b></b><b></b><b></b><i></i><i></i><b></b><i></i><b></b><b></b><b></b><i></i><b></b>'.PHP_EOL.'<p><b></b><i></i><b></b><b></b><b></b><i></i><b></b><i></i><b></b><b></b><i></i><b></b><b></b><i></i><b></b><i></i><b></b><b></b><b></b><i></i><b></b>'.PHP_EOL.'<p><b></b><i></i><i></i><i></i><i></i><i></i><b></b><i></i><i></i><b></b><i></i><b></b><i></i><i></i><b></b><i></i><i></i><i></i><i></i><i></i><b></b>'.PHP_EOL.'<p><b></b><b></b><b></b><b></b><b></b><b></b><b></b><i></i><b></b><i></i><b></b><i></i><b></b><i></i><b></b><b></b><b></b><b></b><b></b><b></b><b></b>'.PHP_EOL.'<p><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><b></b><b></b><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>'.PHP_EOL.'<p><b></b><i></i><b></b><i></i><b></b><i></i><b></b><i></i><i></i><b></b><i></i><i></i><b></b><i></i><i></i><i></i><b></b><i></i><i></i><b></b><i></i>'.PHP_EOL.'<p><b></b><b></b><b></b><b></b><b></b><i></i><i></i><i></i><i></i><b></b><b></b><b></b><i></i><b></b><i></i><b></b><i></i><i></i><b></b><b></b><b></b>'.PHP_EOL.'<p><b></b><b></b><i></i><b></b><b></b><b></b><b></b><b></b><i></i><i></i><b></b><b></b><i></i><b></b><b></b><b></b><i></i><b></b><i></i><b></b><b></b>'.PHP_EOL.'<p><b></b><b></b><i></i><i></i><b></b><b></b><i></i><i></i><b></b><i></i><b></b><b></b><b></b><b></b><i></i><b></b><b></b><i></i><i></i><b></b><b></b>'.PHP_EOL.'<p><b></b><i></i><b></b><i></i><i></i><b></b><b></b><b></b><i></i><b></b><i></i><b></b><i></i><b></b><b></b><b></b><i></i><i></i><i></i><b></b><b></b>'.PHP_EOL.'<p><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><b></b><i></i><i></i><i></i><i></i><i></i><b></b><b></b><i></i><i></i><b></b><b></b><b></b>'.PHP_EOL.'<p><b></b><b></b><b></b><b></b><b></b><b></b><b></b><i></i><i></i><b></b><b></b><i></i><b></b><i></i><i></i><i></i><b></b><b></b><i></i><b></b><b></b>'.PHP_EOL.'<p><b></b><i></i><i></i><i></i><i></i><i></i><b></b><i></i><i></i><b></b><b></b><i></i><i></i><i></i><b></b><b></b><b></b><i></i><i></i><b></b><b></b>'.PHP_EOL.'<p><b></b><i></i><b></b><b></b><b></b><i></i><b></b><i></i><b></b><b></b><b></b><i></i><b></b><i></i><b></b><i></i><b></b><i></i><i></i><b></b><b></b>'.PHP_EOL.'<p><b></b><i></i><b></b><b></b><b></b><i></i><b></b><i></i><i></i><b></b><i></i><b></b><i></i><b></b><i></i><i></i><b></b><b></b><i></i><b></b><i></i>'.PHP_EOL.'<p><b></b><i></i><b></b><b></b><b></b><i></i><b></b><i></i><b></b><b></b><i></i><b></b><i></i><b></b><b></b><b></b><b></b><i></i><i></i><i></i><b></b>'.PHP_EOL.'<p><b></b><i></i><i></i><i></i><i></i><i></i><b></b><i></i><i></i><i></i><i></i><b></b><b></b><b></b><i></i><i></i><i></i><i></i><i></i><b></b><i></i>'.PHP_EOL.'<p><b></b><b></b><b></b><b></b><b></b><b></b><b></b><i></i><b></b><i></i><b></b><b></b><i></i><b></b><b></b><i></i><b></b><i></i><i></i><b></b><b></b>'.PHP_EOL.''],
			['foobar', QRCode::OUTPUT_STRING_JSON, '[[true,true,true,true,true,true,true,false,false,false,true,true,true,false,true,true,true,true,true,true,true],[true,false,false,false,false,false,true,false,true,true,true,false,false,false,true,false,false,false,false,false,true],[true,false,true,true,true,false,true,false,false,true,false,false,true,false,true,false,true,true,true,false,true],[true,false,true,true,true,false,true,false,false,true,true,true,false,false,true,false,true,true,true,false,true],[true,false,true,true,true,false,true,false,true,true,false,true,true,false,true,false,true,true,true,false,true],[true,false,false,false,false,false,true,false,false,true,false,true,false,false,true,false,false,false,false,false,true],[true,true,true,true,true,true,true,false,true,false,true,false,true,false,true,true,true,true,true,true,true],[false,false,false,false,false,false,false,false,false,true,true,false,false,false,false,false,false,false,false,false,false],[true,false,true,false,true,false,true,false,false,true,false,false,true,false,false,false,true,false,false,true,false],[true,true,true,true,true,false,false,false,false,true,true,true,false,true,false,true,false,false,true,true,true],[true,true,false,true,true,true,true,true,false,false,true,true,false,true,true,true,false,true,false,true,true],[true,true,false,false,true,true,false,false,true,false,true,true,true,true,false,true,true,false,false,true,true],[true,false,true,false,false,true,true,true,false,true,false,true,false,true,true,true,false,false,false,true,true],[false,false,false,false,false,false,false,false,true,false,false,false,false,false,true,true,false,false,true,true,true],[true,true,true,true,true,true,true,false,false,true,true,false,true,false,false,false,true,true,false,true,true],[true,false,false,false,false,false,true,false,false,true,true,false,false,false,true,true,true,false,false,true,true],[true,false,true,true,true,false,true,false,true,true,true,false,true,false,true,false,true,false,false,true,true],[true,false,true,true,true,false,true,false,false,true,false,true,false,true,false,false,true,true,false,true,false],[true,false,true,true,true,false,true,false,true,true,false,true,false,true,true,true,true,false,false,false,true],[true,false,false,false,false,false,true,false,false,false,false,true,true,true,false,false,false,false,false,true,false],[true,true,true,true,true,true,true,false,true,false,true,true,false,true,true,false,true,false,false,true,true]]'],
			['foobar', QRCode::OUTPUT_STRING_TEXT, '#######   ### #######'.PHP_EOL.'#     # ###   #     #'.PHP_EOL.'# ### #  #  # # ### #'.PHP_EOL.'# ### #  ###  # ### #'.PHP_EOL.'# ### # ## ## # ### #'.PHP_EOL.'#     #  # #  #     #'.PHP_EOL.'####### # # # #######'.PHP_EOL.'         ##          '.PHP_EOL.'# # # #  #  #   #  # '.PHP_EOL.'#####    ### # #  ###'.PHP_EOL.'## #####  ## ### # ##'.PHP_EOL.'##  ##  # #### ##  ##'.PHP_EOL.'# #  ### # # ###   ##'.PHP_EOL.'        #     ##  ###'.PHP_EOL.'#######  ## #   ## ##'.PHP_EOL.'#     #  ##   ###  ##'.PHP_EOL.'# ### # ### # # #  ##'.PHP_EOL.'# ### #  # # #  ## # '.PHP_EOL.'# ### # ## # ####   #'.PHP_EOL.'#     #    ###     # '.PHP_EOL.'####### # ## ## #  ##'.PHP_EOL.''],
		];
	}

	/**
	 * @dataProvider stringDataProvider
	 */
	public function testImageOutput($data, $type, $expected){
		$this->options->type = $type;
		$this->assertEquals($expected, (new QRCode($data, new QRString($this->options)))->output());
	}

	/**
	 * @expectedException \chillerlan\QRCode\Output\QRCodeOutputException
	 * @expectedExceptionMessage Invalid matrix!
	 */
	public function testSetMatrixException(){
		(new QRString)->setMatrix([]);
	}

}
