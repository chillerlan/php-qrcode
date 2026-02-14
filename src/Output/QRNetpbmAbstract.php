<?php
/**
 * Class QRNetbmAbstract
 *
 * @created      19.12.2025
 * @author       wgevaert & codemasher
 * @copyright    2025 wgevaert & codemasher
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Output\QROutputAbstract;
use UnexpectedValueException;
use function sprintf;

abstract class QRNetpbmAbstract extends QROutputAbstract{

	protected const HEADER_ASCII  = '';
	protected const HEADER_BINARY = '';

	protected function getMagicNumber():string{
		return $this->options->netpbmPlain ? static::HEADER_ASCII : static::HEADER_BINARY;
	}

	protected function getHeader():string{
		$comment = 'created by https://github.com/chillerlan/php-qrcode';

		return sprintf(
			"%s\n# %s\n%s %s\n%s",
			$this->getMagicNumber(),
			$comment,
			$this->length,
			$this->length,
			$this->getMaxValueHeaderString(),
		);
	}

	protected function getMaxValueHeaderString():string {
		return $this->options->netpbmMaxValue."\n";
	}

	abstract protected function getBodyASCII():string;
	abstract protected function getBodyBinary():string;

	public function dump(string|null $file = null):string{
		$qrString = $this->getHeader();

		$qrString .= $this->options->netpbmPlain
			? $this->getBodyASCII()
			: $this->getBodyBinary();

		$this->saveToFile($qrString, $file);

		if($this->options->outputBase64){
			$qrString = $this->toBase64DataURI($qrString);
		}

		return $qrString;
	}

}
