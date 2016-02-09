<?php
/**
 *
 * @filesource   PolynomialTest.php
 * @created      09.02.2016
 * @package      chillerlan\QRCodeTest
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest;

use chillerlan\QRCode\Polynomial;

class PolynomialTest extends \PHPUnit_Framework_TestCase{

	/**
	 * @var \chillerlan\QRCode\Polynomial
	 */
	protected $polynomial;

	protected function setUp(){
		$this->polynomial = new Polynomial;
	}

	/**
	 * @expectedException \chillerlan\QRCode\QRCodeException
	 * @expectedExceptionMessage log(0)
	 */
	public function testGlogException(){
		$this->polynomial->glog(0);
	}
}
