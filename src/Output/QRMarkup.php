<?php
/**
 * Class QRMarkup
 *
 * @created      17.12.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\QRCode;

use function implode, is_string, sprintf, strip_tags, trim;

/**
 * Converts the matrix into markup types: HTML, SVG, ...
 */
class QRMarkup extends QROutputAbstract{

	/**
	 * @inheritDoc
	 */
	protected function moduleValueIsValid($value):bool{
		return is_string($value);
	}

	/**
	 * @inheritDoc
	 */
	protected function getModuleValue($value):string{
		return trim(strip_tags($value), " '\"\r\n\t");
	}

	/**
	 * @inheritDoc
	 */
	protected function getDefaultModuleValue(bool $isDark):string{
		return $isDark ? $this->options->markupDark : $this->options->markupLight;
	}

	/**
	 * @inheritDoc
	 */
	public function dump(string $file = null){
		$file       ??= $this->options->cachefile;
		$saveToFile   = $file !== null;

		switch($this->options->outputType){
			case QRCode::OUTPUT_MARKUP_HTML:
				$data = $this->html($saveToFile);
				break;
			case QRCode::OUTPUT_MARKUP_SVG:
			default:
				$data = $this->svg($saveToFile);
		}

		if($saveToFile){
			$this->saveToFile($data, $file);
		}

		return $data;
	}

	/**
	 * HTML output
	 */
	protected function html(bool $saveToFile):string{

		$html = empty($this->options->cssClass)
			? '<div>'
			: sprintf('<div class="%s">', $this->options->cssClass);

		$html .= $this->options->eol;

		foreach($this->matrix->matrix() as $row){
			$html .= '<div>';

			foreach($row as $M_TYPE){
				$html .= sprintf('<span style="background: %s;"></span>', $this->moduleValues[$M_TYPE]);
			}

			$html .= '</div>'.$this->options->eol;
		}

		$html .= '</div>'.$this->options->eol;

		if($saveToFile){
			return sprintf(
				'<!DOCTYPE html><head><meta charset="UTF-8"><title>QR Code</title></head><body>%s</body>',
				$this->options->eol.$html
			);
		}

		return $html;
	}

	/**
	 * SVG output
	 *
	 * @see https://github.com/codemasher/php-qrcode/pull/5
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Element/svg
	 * @see https://www.sarasoueidan.com/demos/interactive-svg-coordinate-system/
	 */
	protected function svg(bool $saveToFile):string{
		$svg = $this->svgHeader();

		if(!empty($this->options->svgDefs)){
			$svg .= sprintf('<defs>%1$s%2$s</defs>%2$s', $this->options->svgDefs, $this->options->eol);
		}

		$svg .= $this->svgPaths();

		// close svg
		$svg .= sprintf('%1$s</svg>%1$s', $this->options->eol);

		// transform to data URI only when not saving to file
		if(!$saveToFile && $this->options->imageBase64){
			$svg = $this->base64encode($svg, 'image/svg+xml');
		}

		return $svg;
	}

	/**
	 * returns the <svg> header with the given options parsed
	 */
	protected function svgHeader():string{
		$width  = $this->options->svgWidth !== null ? sprintf(' width="%s"', $this->options->svgWidth) : '';
		$height = $this->options->svgHeight !== null ? sprintf(' height="%s"', $this->options->svgHeight) : '';

		/** @noinspection HtmlUnknownAttribute */
		return sprintf(
			'<?xml version="1.0" encoding="UTF-8"?>%6$s'.
			'<svg xmlns="http://www.w3.org/2000/svg" class="qr-svg %1$s" viewBox="0 0 %2$s %2$s" preserveAspectRatio="%3$s"%4$s%5$s>%6$s',
			$this->options->cssClass,
			$this->options->svgViewBoxSize ?? $this->moduleCount,
			$this->options->svgPreserveAspectRatio,
			$width,
			$height,
			$this->options->eol
		);
	}

	/**
	 * returns one or more SVG <path> elements
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Element/path
	 */
	protected function svgPaths():string{
		$paths = $this->collectModules(fn(int $x, int $y):string => $this->svgModule($x, $y));
		$svg   = [];

		// create the path elements
		foreach($paths as $M_TYPE => $path){
			$path = trim(implode(' ', $path));

			if(empty($path)){
				continue;
			}

			$cssClass = implode(' ', [
				'qr-'.$M_TYPE,
				($M_TYPE & QRMatrix::IS_DARK) === QRMatrix::IS_DARK ? 'dark' : 'light',
				$this->options->cssClass,
			]);

			$format = empty($this->moduleValues[$M_TYPE])
				? '<path class="%1$s" d="%2$s"/>'
				: '<path class="%1$s" fill="%3$s" fill-opacity="%4$s" d="%2$s"/>';

			$svg[] = sprintf($format, $cssClass, $path, $this->moduleValues[$M_TYPE], $this->options->svgOpacity);
		}

		return implode($this->options->eol, $svg);
	}

	/**
	 * returns a path segment for a single module
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/d
	 */
	protected function svgModule(int $x, int $y):string{

		if($this->options->imageTransparent && !$this->matrix->check($x, $y)){
			return '';
		}

		if($this->options->drawCircularModules && !$this->matrix->checkTypes($x, $y, $this->options->keepAsSquare)){
			$r = $this->options->circleRadius;

			return sprintf(
				'M%1$s %2$s a%3$s %3$s 0 1 0 %4$s 0 a%3$s %3$s 0 1 0 -%4$s 0Z',
				($x + 0.5 - $r),
				($y + 0.5),
				$r,
				($r * 2)
			);

		}

		return sprintf('M%1$s %2$s h%3$s v1 h-%4$sZ', $x, $y, 1, 1);
	}

}
