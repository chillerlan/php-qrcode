<?php
/**
 * Class QRGdImageWEBP
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

use function imagewebp, max, min;

/**
 * GdImage webp output
 *
 * @see \imagewebp()
 */
class QRGdImageWEBP extends QRGdImage{

	final public const MIME_TYPE = 'image/webp';

	/**
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function renderImage():void{
		if(imagewebp(image: $this->image, quality: $this->getQuality()) === false){
			throw new QRCodeOutputException('imagewebp() error');
		}
	}

}
