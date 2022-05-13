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

use function date, implode, is_int, sprintf;

/**
 * Encapsulated Postscript (EPS) output
 *
 * @see https://github.com/t0k4rt/phpqrcode/blob/bb29e6eb77e0a2a85bb0eb62725e0adc11ff5a90/qrvect.php#L52-L137
 * @see https://web.archive.org/web/20170818010030/http://wwwimages.adobe.com/content/dam/Adobe/en/devnet/postscript/pdfs/5002.EPSF_Spec.pdf
 * @see https://web.archive.org/web/20210419003859/https://www.adobe.com/content/dam/acom/en/devnet/actionscript/articles/PLRM.pdf
 */
class QREps extends QROutputAbstract{

	/**
	 * @inheritDoc
	 */
	protected function moduleValueIsValid($value):bool{
		return is_int($value) && $value >= 0 && $value <= 0xffffff;
	}

	/**
	 * @inheritDoc
	 */
	protected function getModuleValue($value):array{
		return [
			round((($value & 0xff0000) >> 16) / 255, 5),
			round((($value & 0x00ff00) >> 8) / 255, 5),
			round(($value & 0x0000ff) / 255, 5)
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getDefaultModuleValue(bool $isDark):array{
		return $isDark ? [0.0, 0.0, 0.0] : [1.0, 1.0, 1.0];
	}

	/**
	 * @inheritDoc
	 */
	public function dump(string $file = null):string{
		$file ??= $this->options->cachefile;

		$eps = [
			// main header
			'%!PS-Adobe-3.0 EPSF-3.0',
			'%%Creator: php-qrcode (https://github.com/chillerlan/php-qrcode)',
			'%%Title: QR Code',
			sprintf('%%%%CreationDate: %1$s', date('c')),
			'%%DocumentData: Clean7Bit',
			'%%LanguageLevel: 3',
			sprintf('%%%%BoundingBox: 0 -%1$s %1$s 0', $this->length),
			'%%EndComments',
			// function definitions
			'%%BeginProlog',
			'/F { rectfill } def',
			'/S { setrgbcolor } def',
			'%%EndProlog',
			// rotate into the proper orientation and scale to fit
			'-90 rotate',
			sprintf('%1$s %1$s scale', $this->scale),
		];

		// create the path elements
		$paths = $this->collectModules(fn(int $x, int $y):string => sprintf('%s %s 1 1 F', $x, $y));

		foreach($paths as $M_TYPE => $path){

			if(empty($path)){
				continue;
			}

			$eps[] = sprintf('%f %f %f S', ...$this->moduleValues[$M_TYPE]);
			$eps[] = implode("\n", $path);
		}

		// end file
		$eps[] = '%%EOF';

		$data = implode("\n", $eps);

		if($file !== null){
			$this->saveToFile($data, $file);
		}

		return $data;
	}

}
