<?php
/**
 * Class QRGdImageGIF
 *
 * @created      25.10.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */
declare(strict_types=1);

namespace chillerlan\QRCode\Output;

use function imagegif;

/**
 * GdImage gif output
 *
 * @see \imagegif()
 */
class QRGdImageGIF extends QRGdImage{

	final public const MIME_TYPE = 'image/gif';

	/**
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function renderImage():void{
		if(imagegif(image: $this->image) === false){
			throw new QRCodeOutputException('imagegif() error');
		}
	}

}
