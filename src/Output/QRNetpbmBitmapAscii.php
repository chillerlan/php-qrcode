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

class QRNetpbmBitmapAscii extends QRNetpbmBitmapAbstract {
	protected function getHeader(): string {
		return 'P1';
	}
}
