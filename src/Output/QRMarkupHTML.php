<?php
/**
 * Class QRMarkupHTML
 *
 * @created      06.06.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;

use function sprintf;

/**
 * HTML output
 */
class QRMarkupHTML extends QRMarkup{

	/**
	 * @inheritDoc
	 */
	protected function createMarkup(bool $saveToFile):string{
		$html = empty($this->options->cssClass)
			? '<div>'
			: sprintf('<div class="%s">', $this->getCssClass(0)); // @todo $M_TYPE

		$html .= $this->options->eol;

		foreach($this->matrix->matrix() as $row){
			$html .= '<div>';

			foreach($row as $M_TYPE){
				$html .= sprintf('<span style="background: %s;"></span>', $this->moduleValues[$M_TYPE]);
			}

			$html .= '</div>'.$this->options->eol;
		}

		$html .= '</div>'.$this->options->eol;

		// wrap the snippet into a body when saving to file
		if($saveToFile){
			$html = sprintf(
				'<!DOCTYPE html><head><meta charset="UTF-8"><title>QR Code</title></head><body>%s</body>',
				$this->options->eol.$html
			);
		}

		return $html;
	}

	/**
	 * @inheritDoc
	 */
	protected function getCssClass(int $M_TYPE):string{
		return $this->options->cssClass;
	}

}
