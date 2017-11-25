<?php
/**
 * Class PolynomialTest
 *
 * @filesource   PolynomialTest.php
 * @created      09.02.2016
 * @package      chillerlan\QRCodeTest\Helpers
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Helpers;

use chillerlan\QRCode\Helpers\Polynomial;
use chillerlan\QRCodeTest\QRTestAbstract;

class PolynomialTest extends QRTestAbstract{

	/**
	 * @var \chillerlan\QRCode\Helpers\Polynomial
	 */
	protected $polynomial;

	protected function setUp(){
		$this->polynomial = new Polynomial;
	}

	public function testGexp(){
		$this->assertEquals(142, $this->polynomial->gexp(-1));
		$this->assertEquals(133, $this->polynomial->gexp(128));
		$this->assertEquals(2,   $this->polynomial->gexp(256));
	}

	/**
	 * @expectedException \chillerlan\QRCode\QRCodeException
	 * @expectedExceptionMessage log(0)
	 */
	public function testGlogException(){
		$this->polynomial->glog(0);
	}
}
