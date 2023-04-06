<?php
/**
 * Class QREps
 *
 * @created      09.05.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;

use function array_values, count, date, implode, is_array, is_numeric, max, min, round, sprintf;

/**
 * Encapsulated Postscript (EPS) output
 *
 * @see https://github.com/t0k4rt/phpqrcode/blob/bb29e6eb77e0a2a85bb0eb62725e0adc11ff5a90/qrvect.php#L52-L137
 * @see https://web.archive.org/web/20170818010030/http://wwwimages.adobe.com/content/dam/Adobe/en/devnet/postscript/pdfs/5002.EPSF_Spec.pdf
 * @see https://web.archive.org/web/20210419003859/https://www.adobe.com/content/dam/acom/en/devnet/actionscript/articles/PLRM.pdf
 * @see https://github.com/chillerlan/php-qrcode/discussions/148
 */
class QREps extends QROutputAbstract{

	/**
	 * @inheritDoc
	 */
	public static function moduleValueIsValid($value):bool{

		if(!is_array($value) || count($value) < 3){
			return false;
		}

		// check the first values of the array
		foreach(array_values($value) as $i => $val){

			if($i > 3){
				break;
			}

			if(!is_numeric($val)){
				return false;
			}

		}

		return true;
	}

	/**
	 * @param array $value
	 *
	 * @inheritDoc
	 */
	protected function prepareModuleValue($value):array{
		$values = [];

		foreach(array_values($value) as $i => $val){

			if($i > 3){
				break;
			}

			// clamp value and convert from int 0-255 to float 0-1 RGB/CMYK range
			$values[] = round((max(0, min(255, intval($val))) / 255), 6);
		}

		return $values;
	}

	/**
	 * @inheritDoc
	 */
	protected function getDefaultModuleValue(bool $isDark):array{
		return ($isDark) ? [0.0, 0.0, 0.0] : [1.0, 1.0, 1.0];
	}

	/**
	 * @inheritDoc
	 */
	public function dump(string $file = null):string{

		$eps = [
			// main header
			'%!PS-Adobe-3.0 EPSF-3.0',
			'%%Creator: php-qrcode (https://github.com/chillerlan/php-qrcode)',
			'%%Title: QR Code',
			sprintf('%%%%CreationDate: %1$s', date('c')),
			'%%DocumentData: Clean7Bit',
			'%%LanguageLevel: 3',
			sprintf('%%%%BoundingBox: 0 0 %1$s %1$s', $this->length),
			'%%EndComments',
			// function definitions
			'%%BeginProlog',
			'/F { rectfill } def',
			'/R { setrgbcolor } def',
			'/C { setcmykcolor } def',
			'%%EndProlog',
		];

		// create the path elements
		$paths = $this->collectModules(fn(int $x, int $y):string => $this->module($x, $y));

		foreach($paths as $M_TYPE => $path){

			if(empty($path)){
				continue;
			}

			// 4 values will be interpreted as CMYK, 3 as RGB
			$val    = $this->getModuleValue($M_TYPE);
			$format = (count($val) === 4) ? '%f %f %f %f C' : '%f %f %f R';
			$eps[]  = sprintf($format, ...$val);
			$eps[]  = implode("\n", $path);
		}

		// end file
		$eps[] = '%%EOF';

		$data = implode("\n", $eps);

		$this->saveToFile($data, $file);

		return $data;
	}

	/**
	 * returns a path segment for a single module
	 */
	protected function module(int $x, int $y):string{

		if(!$this->options->drawLightModules && !$this->matrix->check($x, $y)){
			return '';
		}

		$outputX = ($x * $this->scale);
		// Actual size - one block = Topmost y pos.
		$top     = ($this->length - $this->scale);
		// Apparently y-axis is inverted (y0 is at bottom and not top) in EPS, so we have to switch the y-axis here
		$outputY = ($top - ($y * $this->scale));

		return sprintf('%d %d %d %d F', $outputX, $outputY, $this->scale, $this->scale);
	}

}
