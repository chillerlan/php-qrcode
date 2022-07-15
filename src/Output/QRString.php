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
	protected function moduleValueIsValid($value):bool{
		return is_string($value);
	}

	/**
	 * @inheritDoc
	 */
	protected function getModuleValue($value):string{
		return $value;
	}

	/**
	 * @inheritDoc
	 */
	protected function getDefaultModuleValue(bool $isDark):string{
		return $isDark ? $this->options->textDark : $this->options->textLight;
	}

	/**
	 * @inheritDoc
	 */
	public function dump(string $file = null):string{
		$file ??= $this->options->cachefile;

		switch($this->options->outputType){
			case QROutputInterface::STRING_TEXT:
				$data = $this->text();
				break;
			case QROutputInterface::STRING_JSON:
			default:
				$data = $this->json();
		}

		if($file !== null){
			$this->saveToFile($data, $file);
		}

		return $data;
	}

	/**
	 * string output
	 */
	protected function text():string{
		$str = [];

		foreach($this->matrix->matrix() as $row){
			$r = [];

			foreach($row as $M_TYPE){
				$r[] = $this->moduleValues[$M_TYPE];
			}

			$str[] = implode('', $r);
		}

		return implode($this->options->eol, $str);
	}

	/**
	 * JSON output
	 */
	protected function json():string{
		return json_encode($this->matrix->matrix());
	}

}
