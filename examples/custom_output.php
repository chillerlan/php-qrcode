<?php
/**
 * custom output example
 *
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @noinspection PhpIllegalPsrClassPathInspection
 */
declare(strict_types=1);

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Output\QROutputAbstract;

require_once __DIR__.'/../vendor/autoload.php';

/*
 * Class definition
 */

class MyCustomOutput extends QROutputAbstract{

	public static function moduleValueIsValid(mixed $value):bool{
		// TODO: Implement moduleValueIsValid() method. (interface)
		return false;
	}

	protected function prepareModuleValue(mixed $value):mixed{
		// TODO: Implement prepareModuleValue() method. (abstract)
		return null;
	}

	protected function getDefaultModuleValue(bool $isDark):mixed{
		// TODO: Implement getDefaultModuleValue() method. (abstract)
		return null;
	}

	public function dump(string|null $file = null):string{
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

$options = new QROptions;

$options->version  = 5;
$options->eccLevel = 'L';

$data = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';

// invoke the QROutputInterface manually
// please note that an QROutputInterface invoked this way might become unusable after calling dump().
// the clean way would be to extend the QRCode class to ensure a new QROutputInterface instance on each call to render().

$qrcode = new QRCode($options);
$qrcode->addByteSegment($data);

$qrOutputInterface = new MyCustomOutput($options, $qrcode->getQRMatrix());

var_dump($qrOutputInterface->dump());

// or just via the options
$options->outputInterface = MyCustomOutput::class;

var_dump((new QRCode($options))->render($data));

exit;
