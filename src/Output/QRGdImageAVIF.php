<?php
/**
 * Class QRGdImageAVIF
 *
 * @created      26.11.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */
declare(strict_types=1);

namespace chillerlan\QRCode\Output;

use function imageavif, max, min;

/**
 * GDImage avif output
 *
 * @see \imageavif()
 */
class QRGdImageAVIF extends QRGdImage{

	final public const MIME_TYPE = 'image/avif';

	/**
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function renderImage():void{
		if(imageavif(image: $this->image, quality: $this->getQuality()) === false){
			throw new QRCodeOutputException('imageavif() error');
		}
	}

}
