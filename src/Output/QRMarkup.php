<?php
/**
 * Class QRMarkup
 *
 * @created      17.12.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCode\Output;

/**
 * Abstract for markup types: HTML, SVG, ... XML anyone?
 */
abstract class QRMarkup extends QROutputAbstract{
	use CssColorModuleValueTrait;

	public function dump(string|null $file = null):string{
		$saveToFile = $file !== null;
		$data       = $this->createMarkup($saveToFile);

		$this->saveToFile($data, $file);

		// transform to data URI only when not saving to file
		if(!$saveToFile && $this->options->outputBase64){
			return $this->toBase64DataURI($data);
		}

		return $data;
	}

	/**
	 * returns a string with all css classes for the current element
	 */
	protected function getCssClass(int $M_TYPE = 0):string{
		return $this->options->cssClass;
	}

	/**
	 * returns the fully parsed and rendered markup string for the given input
	 */
	abstract protected function createMarkup(bool $saveToFile):string;

}
