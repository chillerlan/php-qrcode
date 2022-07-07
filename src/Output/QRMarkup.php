<?php
/**
 * Class QRMarkup
 *
 * @created      17.12.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;

use function is_string, strip_tags, trim;

/**
 * Abstract for markup types: HTML, SVG, ... XML anyone?
 */
abstract class QRMarkup extends QROutputAbstract{

	/**
	 * @inheritDoc
	 */
	protected function moduleValueIsValid($value):bool{
		return is_string($value);
	}

	/**
	 * @inheritDoc
	 */
	protected function getModuleValue($value):string{
		return trim(strip_tags($value), " '\"\r\n\t");
	}

	/**
	 * @inheritDoc
	 */
	protected function getDefaultModuleValue(bool $isDark):string{
		return $isDark ? $this->options->markupDark : $this->options->markupLight;
	}

	/**
	 * @inheritDoc
	 */
	public function dump(string $file = null):string{
		$file       ??= $this->options->cachefile;
		$saveToFile   = $file !== null;

		$data = $this->createMarkup($saveToFile);

		if($saveToFile){
			$this->saveToFile($data, $file);
		}

		return $data;
	}

	/**
	 * returns a string with all css classes for the current element
	 */
	abstract protected function getCssClass(int $M_TYPE):string;

	/**
	 *
	 */
	abstract protected function createMarkup(bool $saveToFile):string;
}
