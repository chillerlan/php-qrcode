<?php
/**
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @noinspection PhpIllegalPsrClassPathInspection
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\{QROutputAbstract, QROutputInterface};

require_once __DIR__.'/../vendor/autoload.php';

/*
 * Class definition
 */

class MyCustomOutput extends QROutputAbstract{

	/**
	 * @inheritDoc
	 */
	protected function moduleValueIsValid($value):bool{
		// TODO: Implement moduleValueIsValid() method. (abstract)
		return false;
	}

	/**
	 * @inheritDoc
	 */
	protected function getModuleValue($value){
		// TODO: Implement getModuleValue() method. (abstract)
		return null;
	}

	/**
	 * @inheritDoc
	 */
	protected function getDefaultModuleValue(bool $isDark){
		// TODO: Implement getDefaultModuleValue() method. (abstract)
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function dump(string $file = null):string{
		$output = '';

		for($y = 0; $y < $this->moduleCount; $y++){
			for($x = 0; $x < $this->moduleCount; $x++){
				$output .= (int)$this->matrix->check($x, $y);
			}

			$output .= $this->options->eol;
		}

		return $output;
	}

}


/*
 * Runtime
 */

$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';

// invoke the QROutputInterface manually
$options = new QROptions([
	'version'      => 5,
	'eccLevel'     => EccLevel::L,
]);

$qrcode = new QRCode($options);
$qrcode->addByteSegment($data);

$qrOutputInterface = new MyCustomOutput($options, $qrcode->getMatrix());

var_dump($qrOutputInterface->dump());


// or just via the options
$options = new QROptions([
	'version'         => 5,
	'eccLevel'        => EccLevel::L,
	'outputType'      => QROutputInterface::CUSTOM,
	'outputInterface' => MyCustomOutput::class,
]);

var_dump((new QRCode($options))->render($data));

exit;
