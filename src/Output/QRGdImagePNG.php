<?php
/**
 * Class QRGdImagePNG
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

use function imagepng, max, min;

/**
 * GdImage png output
 *
 * @see \imagepng()
 */
class QRGdImagePNG extends QRGdImage{

	final public const MIME_TYPE = 'image/png';

	protected function getQuality():int{
		return max(-1, min(9, $this->options->quality));
	}

	/**
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function renderImage():void{
		if(imagepng(image: $this->image, quality: $this->getQuality()) === false){
			throw new QRCodeOutputException('imagepng() error');
		}
	}

}
