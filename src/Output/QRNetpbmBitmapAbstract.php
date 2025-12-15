<?php
/**
 * Class QRPbm
 *
 * @created      11.12.2025
 * @author       wgevaert
 * @copyright    2025 wgevaert
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Output\QROutputAbstract;
use UnexpectedValueException;

abstract class QRNetpbmBitmapAbstract extends QROutputAbstract {
	public const MIME_TYPE = 'image/x-portable-bitmap';

	protected function prepareModuleValue( mixed $value ): mixed {
		if ( !is_bool( $value ) ) {
			throw new UnexpectedValueException( 'Expected boolean module value' );
		}
		return $value;
	}

	protected function getDefaultModuleValue( bool $isDark ): bool {
		return $isDark;
	}

	public static function moduleValueIsValid( mixed $value ): bool {
		return is_bool( $value );
	}

	abstract protected function getHeader(): string;

	protected function getBody(): string {
		$body = '';
		foreach ($this->matrix->getBooleanMatrix() as $row) {
			$line = '';
			foreach ($row as $isDark) {
				$line .= str_repeat( $isDark ? '1' : '0', $this->scale );
			}
			$line .= "\n";
			$body .= str_repeat( $line, $this->scale );
		}
		return trim($body,"\n");
	}

	public function dump( string|null $file = null ): mixed {
		$qrString = $this->getHeader()."\n"
                    .$this->length.' '.$this->length."\n".$this->getBody();

		$this->saveToFile( $qrString, $file );

		if ( $this->options->outputBase64 ) {
			$qrString = $this->toBase64DataURI( $qrString );
		}

		return $qrString;
	}
}
