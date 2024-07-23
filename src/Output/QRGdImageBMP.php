<?php
/**
 * Class QRGdImageBMP
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

use function imagebmp;

/**
 * GdImage bmp output
 *
 * @see \imagebmp()
 */
class QRGdImageBMP extends QRGdImage{

	final public const MIME_TYPE = 'image/bmp';

	/**
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function renderImage():void{
		// the $compressed parameter is boolean here
		if(imagebmp(image: $this->image, compressed: ($this->options->quality > 0)) === false){
			throw new QRCodeOutputException('imagebmp() error');
		}
	}

}
