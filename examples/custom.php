<?php

require_once '../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\Output\QROutputAbstract;


/**
 * Class MyCustomOutput
 */
class MyCustomOutput extends QROutputAbstract{

	/**
	 * @return mixed
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function dump(){

		$output = '';

		for($row = 0; $row < $this->pixelCount; $row++){
			for($col = 0; $col < $this->pixelCount; $col++){
				$output .= (string)(int)$this->matrix[$row][$col];
			}
		}

		return $output;
	}

}

$starttime = microtime(true);

echo (new QRCode('otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', new MyCustomOutput))->output();

echo PHP_EOL.'QRCode: '.round((microtime(true)-$starttime), 5);
