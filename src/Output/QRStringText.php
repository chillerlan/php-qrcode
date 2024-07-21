<?php
/**
 * Class QRStringText
 *
 * @created      25.10.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCode\Output;

use function array_map, implode, is_string, max, min, sprintf;

/**
 * String/plaintext output (for CLI etc.)
 */
class QRStringText extends QROutputAbstract{

	final public const MIME_TYPE = 'text/plain';

	/**
	 * @inheritDoc
	 *
	 * @param string $value
	 */
	public static function moduleValueIsValid(mixed $value):bool{
		return is_string($value);
	}

	/**
	 * @inheritDoc
	 *
	 * @param string $value
	 */
	protected function prepareModuleValue(mixed $value):string{
		return $value;
	}

	protected function getDefaultModuleValue(bool $isDark):string{
		return ($isDark) ? '██' : '░░';
	}

	public function dump(string|null $file = null):string{
		$lines     = [];
		$linestart = $this->options->textLineStart;

		foreach($this->matrix->getMatrix() as $row){
			$lines[] = $linestart.implode('', array_map($this->getModuleValue(...), $row));
		}

		$data = implode($this->eol, $lines);

		$this->saveToFile($data, $file);

		return $data;
	}

	/**
	 * a little helper to create a proper ANSI 8-bit color escape sequence
	 *
	 * @see https://en.wikipedia.org/wiki/ANSI_escape_code#8-bit
	 * @see https://en.wikipedia.org/wiki/Block_Elements
	 *
	 * @codeCoverageIgnore
	 */
	public static function ansi8(string $str, int $color, bool|null $background = null):string{
		$color      = max(0, min($color, 255));
		$background = ($background === true) ? 48 : 38;

		return sprintf("\x1b[%s;5;%sm%s\x1b[0m", $background, $color, $str);
	}

}
