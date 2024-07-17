<?php
/**
 * Class QRString
 *
 * @filesource   QRString.php
 * @created      05.12.2015
 * @package      chillerlan\QRCode\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 *
 * @noinspection PhpUnusedParameterInspection
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\QRCode;

use function implode, is_string, json_encode;

/**
 * Converts the matrix data into string types
 */
class QRString extends QROutputAbstract{

	protected string $defaultMode = QRCode::OUTPUT_STRING_TEXT;

	/**
	 * @inheritDoc
	 */
	protected function setModuleValues():void{

		foreach($this::DEFAULT_MODULE_VALUES as $M_TYPE => $defaultValue){
			$v = $this->options->moduleValues[$M_TYPE] ?? null;

			if(!is_string($v)){
				$this->moduleValues[$M_TYPE] = $defaultValue
					? $this->options->textDark
					: $this->options->textLight;
			}
			else{
				$this->moduleValues[$M_TYPE] = $v;
			}

		}

	}

	/**
	 * string output
	 */
	protected function text(?string $file = null):string{
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
	protected function json(?string $file = null):string{
		return json_encode($this->matrix->matrix());
	}

}
