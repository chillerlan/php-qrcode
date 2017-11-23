<?php
/**
 * Class QRCodeTest
 *
 * @filesource   QRCodeTest.php
 * @created      17.11.2017
 * @package      chillerlan\QRCodeTest
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest;

use chillerlan\QRCode\Output\QRMarkupOptions;
use chillerlan\QRCode\QRCode;

abstract class QRCodeTest extends QRTestAbstract{

	protected $FQCN = QRCode::class;

	public function optionsDataProvider(){
		return [
			[QRMarkupOptions::class],
#			[],
		];
	}

	/**
	 * @dataProvider optionsDataProvider
	 */
	public function testInstance($options){
		$q = $this->reflection->newInstanceArgs([new $options]);
		$this->assertInstanceOf($this->FQCN, $q);
#		print_r($q->render('test'));
	}
}
