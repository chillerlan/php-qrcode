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
	 * @inheritDoc
	 */
	protected function renderImage():void{
		imageavif($this->image, null, max(-1, min(100, $this->options->quality)));
	}

}
