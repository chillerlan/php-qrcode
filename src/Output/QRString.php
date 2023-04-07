<?php
/**
 * Class QRString
 *
 * @created      05.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 *
 * @noinspection PhpUnusedParameterInspection
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace chillerlan\QRCode\Output;

use function implode, is_string, json_encode;

/**
 * Converts the matrix data into string types
 */
class QRString extends QROutputAbstract{

	/**
	 * @inheritDoc
	 */
	public static function moduleValueIsValid($value):bool{
		return is_string($value);
	}

	/**
	 * @inheritDoc
	 */
	protected function prepareModuleValue($value):string{
		return $value;
	}

	/**
	 * @inheritDoc
	 */
	protected function getDefaultModuleValue(bool $isDark):string{
		return ($isDark) ? $this->options->textDark : $this->options->textLight;
	}

	/**
	 * @inheritDoc
	 */
	public function dump(string $file = null):string{

		switch($this->options->outputType){
			case QROutputInterface::STRING_TEXT:
				$data = $this->text();
				break;
			case QROutputInterface::STRING_JSON:
			default:
				$data = $this->json();
		}

		$this->saveToFile($data, $file);

		return $data;
	}

	/**
	 * string output
	 */
	protected function text():string{
		$str = [];

		for($y = 0; $y < $this->moduleCount; $y++){
			$r = [];

			for($x = 0; $x < $this->moduleCount; $x++){
				$r[] = $this->getModuleValueAt($x, $y);
			}

			$str[] = implode('', $r);
		}

		return implode($this->options->eol, $str);
	}

	/**
	 * JSON output
	 */
	protected function json():string{
		return json_encode($this->matrix->getMatrix());
	}

}
