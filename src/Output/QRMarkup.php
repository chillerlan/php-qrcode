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

/**
 * Abstract for markup types: HTML, SVG, ... XML anyone?
 */
abstract class QRMarkup extends QROutputAbstract{
	use CssColorModuleValueTrait;

	/**
	 * @inheritDoc
	 */
	public function dump(string $file = null):string{
		$data = $this->createMarkup($file !== null);

		$this->saveToFile($data, $file);

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
