<?php
/**
 *
 * @filesource   custom_output.php
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Output\QROutputAbstract;

require_once __DIR__.'/../vendor/autoload.php';

class MyCustomOutput extends QROutputAbstract{

	protected function setModuleValues():void{
		// TODO: Implement setModuleValues() method.
	}

	public function dump(string $file = null){

		$output = '';

		for($row = 0; $row < $this->moduleCount; $row++){
			for($col = 0; $col < $this->moduleCount; $col++){
				$output .= (int)$this->matrix->check($col, $row);
			}

			$output .= PHP_EOL;
		}

		return $output;
	}

}


// invoke the QROutputInterface manually
$options = new QROptions([
	'version'  => 5,
	'eccLevel' => QRCode::ECC_L,
]);

$qrOutputInterface = new MyCustomOutput($options, (new QRCode($options))->getMatrix('https://www.youtube.com/watch?v=dQw4w9WgXcQ'));

echo '<pre>'.$qrOutputInterface->dump().'</pre>';


// or just
$options = new QROptions([
	'version'         => 5,
	'eccLevel'        => QRCode::ECC_L,
	'outputType'      => QRCode::OUTPUT_CUSTOM,
	'outputInterface' => MyCustomOutput::class,
]);

echo '<pre>'.(new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ').'</pre>';
