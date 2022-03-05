<?php
/**
 * Class QRSvgWithLogo
 *
 * @created      05.03.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeExamples;

use chillerlan\QRCode\Output\QRMarkup;
use function file_get_contents, sprintf;

/**
 * Create SVG QR Codes with embedded logos (that are also SVG)
 */
class QRSvgWithLogo extends QRMarkup{

	/**
	 * SVG output
	 *
	 * @see https://github.com/codemasher/php-qrcode/pull/5
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Element/svg
	 * @see https://www.sarasoueidan.com/demos/interactive-svg-coordinate-system/
	 */
	protected function svg(bool $saveToFile):string{
		$size = (int)ceil($this->moduleCount * $this->options->svgLogoScale);

		// we're calling QRMatrix::setLogoSpace() manually, so QROptions::$addLogoSpace has no effect here
		$this->matrix->setLogoSpace($size, $size);

		$svg = $this->svgHeader();

		if(!empty($this->options->svgDefs)){
			$svg .= sprintf('<defs>%1$s%2$s</defs>%2$s', $this->options->svgDefs, $this->options->eol);
		}

		$svg .= $this->svgPaths();
		$svg .= $this->getLogo();

		// close svg
		$svg .= sprintf('%1$s</svg>%1$s', $this->options->eol);

		// transform to data URI only when not saving to file
		if(!$saveToFile && $this->options->imageBase64){
			$svg = $this->base64encode($svg, 'image/svg+xml');
		}

		return $svg;
	}

	/**
	 * returns a <g> element that contains the SVG logo and positions it properly within the QR Code
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Element/g
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/transform
	 */
	protected function getLogo():string{
		// @todo: customize the <g> element to your liking (css class, style...)
		return sprintf(
			'%5$s<g transform="translate(%1$s %1$s) scale(%2$s)" class="%3$s">%5$s	%4$s%5$s</g>',
			($this->moduleCount - ($this->moduleCount * $this->options->svgLogoScale)) / 2,
			$this->options->svgLogoScale,
			$this->options->svgLogoCssClass,
			file_get_contents($this->options->svgLogo),
			$this->options->eol
		);
	}

}
