<?php
/**
 * Class QRMarkupSVG
 *
 * @created      06.06.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Data\QRMatrix;
use function array_chunk, implode, is_string, preg_match, sprintf, trim;

/**
 * SVG output
 *
 * @see https://github.com/codemasher/php-qrcode/pull/5
 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Element/svg
 * @see https://www.sarasoueidan.com/demos/interactive-svg-coordinate-system/
 * @see http://apex.infogridpacific.com/SVG/svg-tutorial-contents.html
 */
class QRMarkupSVG extends QRMarkup{

	/**
	 * @todo: XSS proof
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/fill
	 * @inheritDoc
	 */
	public static function moduleValueIsValid($value):bool{

		if(!is_string($value)){
			return false;
		}

		$value = trim($value);

		// url(...)
		if(preg_match('~^url\([-/#a-z\d]+\)$~i', $value)){
			return true;
		}

		// otherwise check for standard css notation
		return parent::moduleValueIsValid($value);
	}

	/**
	 * @inheritDoc
	 */
	protected function createMarkup(bool $saveToFile):string{
		$svg = $this->header();

		if(!empty($this->options->svgDefs)){
			$svg .= sprintf('<defs>%1$s%2$s</defs>%2$s', $this->options->svgDefs, $this->options->eol);
		}

		$svg .= $this->paths();

		// close svg
		$svg .= sprintf('%1$s</svg>%1$s', $this->options->eol);

		// transform to data URI only when not saving to file
		if(!$saveToFile && $this->options->imageBase64){
			$svg = $this->toBase64DataURI($svg, 'image/svg+xml');
		}

		return $svg;
	}

	/**
	 * returns the <svg> header with the given options parsed
	 */
	protected function header():string{
		$width  = ($this->options->svgWidth !== null) ? sprintf(' width="%s"', $this->options->svgWidth) : '';
		$height = ($this->options->svgHeight !== null) ? sprintf(' height="%s"', $this->options->svgHeight) : '';

		/** @noinspection HtmlUnknownAttribute */
		return sprintf(
			'<?xml version="1.0" encoding="UTF-8"?>%6$s'.
			'<svg xmlns="http://www.w3.org/2000/svg" class="qr-svg %1$s" viewBox="0 0 %2$s %2$s" preserveAspectRatio="%3$s"%4$s%5$s>%6$s',
			$this->options->cssClass,
			($this->options->svgViewBoxSize ?? $this->moduleCount),
			$this->options->svgPreserveAspectRatio,
			$width,
			$height,
			$this->options->eol
		);
	}

	/**
	 * returns one or more SVG <path> elements
	 */
	protected function paths():string{
		$paths = $this->collectModules(fn(int $x, int $y, int $M_TYPE):string => $this->module($x, $y, $M_TYPE));
		$svg   = [];

		// create the path elements
		foreach($paths as $M_TYPE => $modules){
			// limit the total line length
			$chunks = array_chunk($modules, 100);
			$chonks = [];

			foreach($chunks as $chunk){
				$chonks[] = implode(' ', $chunk);
			}

			$path = implode($this->options->eol, $chonks);

			if(empty($path)){
				continue;
			}

			$svg[] = $this->path($path, $M_TYPE);
		}

		return implode($this->options->eol, $svg);
	}

	/**
	 * renders and returns a single <path> element
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Element/path
	 */
	protected function path(string $path, int $M_TYPE):string{
		$val = $this->getModuleValue($M_TYPE);
		// ignore non-existent module values
		$format = empty($val)
			? '<path class="%1$s" d="%2$s"/>'
			: '<path class="%1$s" fill="%3$s" fill-opacity="%4$s" d="%2$s"/>';

		return sprintf(
			$format,
			$this->getCssClass($M_TYPE),
			$path,
			($val ?? ''), // value may or may not exist
			$this->options->svgOpacity
		);
	}

	/**
	 * @inheritDoc
	 */
	protected function getCssClass(int $M_TYPE):string{
		return implode(' ', [
			'qr-'.$M_TYPE,
			(($M_TYPE & QRMatrix::IS_DARK) === QRMatrix::IS_DARK) ? 'dark' : 'light',
			$this->options->cssClass,
		]);
	}

	/**
	 * returns a path segment for a single module
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/d
	 */
	protected function module(int $x, int $y, int $M_TYPE):string{

		if(!$this->options->drawLightModules && !$this->matrix->check($x, $y)){
			return '';
		}

		if($this->options->drawCircularModules && !$this->matrix->checkTypeIn($x, $y, $this->options->keepAsSquare)){
			$r = $this->options->circleRadius;

			return sprintf(
				'M%1$s %2$s a%3$s %3$s 0 1 0 %4$s 0 a%3$s %3$s 0 1 0 -%4$s 0Z',
				($x + 0.5 - $r),
				($y + 0.5),
				$r,
				($r * 2)
			);

		}

		return sprintf('M%1$s %2$s h1 v1 h-1Z', $x, $y);
	}

}
