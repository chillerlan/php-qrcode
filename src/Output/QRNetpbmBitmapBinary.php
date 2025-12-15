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

use chillerlan\QRCode\Output\QRNetpbmBitmapAbstract;

class QRNetpbmBitmapBinary extends QRNetpbmBitmapAbstract {
	protected function getHeader(): string {
		return 'P4';
	}

	protected function getBody(): string {
		$asciiBody = parent::getBody();
		$body = '';
		foreach ( explode("\n", $asciiBody) as $row ) {
			$body .= $this->asciiBinToBinary( $row );
		}
		return $body;
	}

	private function asciiBinToBinary( string $asciiBin ): string {
		$binaryString = '';
		foreach( str_split( $asciiBin, 8 ) as $currentChunk ) {
			$currentChunk = str_pad( $currentChunk, 8, '0' );
			$binaryString .= pack( "C", bindec( $currentChunk ) );
		}
		return $binaryString;
	}
}
