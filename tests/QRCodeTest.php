<?php

/**
 *
 * @filesource   QRCodeTest.php
 * @created      08.02.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRImage;
use chillerlan\QRCode\Output\QRImageOptions;
use chillerlan\QRCode\Output\QRString;
use chillerlan\QRCode\Output\QRStringOptions;

class QRCodeTest extends \PHPUnit_Framework_TestCase{

	public function testInstance(){
		$output =  new QRString;
		$options = new QROptions;

		$this->assertInstanceOf(QRString::class, $output);
		$this->assertInstanceOf(QROptions::class, $options);
		$this->assertInstanceOf(QRCode::class, new QRCode('data', $output, $options));
	}

}
