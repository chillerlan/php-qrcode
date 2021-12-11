<?php
/**
 * Class MyCustomOutput
 *
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeExamples;

use chillerlan\QRCode\Output\QROutputAbstract;

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
