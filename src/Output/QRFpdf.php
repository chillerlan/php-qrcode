<?php
/**
 * Class QRFpdf
 *
 * @created      03.06.2020
 * @author       Maximilian Kresse
 * @license      MIT
 *
 * @see https://github.com/chillerlan/php-qrcode/pull/49
 */

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\Settings\SettingsContainerInterface;
use FPDF;

use function array_values, class_exists, count, is_array;

/**
 * QRFpdf output module (requires fpdf)
 *
 * @see https://github.com/Setasign/FPDF
 * @see http://www.fpdf.org/
 */
class QRFpdf extends QROutputAbstract{

	/**
	 * QRFpdf constructor.
	 *
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function __construct(SettingsContainerInterface $options, QRMatrix $matrix){

		if(!class_exists(FPDF::class)){
			// @codeCoverageIgnoreStart
			throw new QRCodeOutputException(
				'The QRFpdf output requires FPDF (https://github.com/Setasign/FPDF)'.
				' as dependency but the class "\\FPDF" couldn\'t be found.'
			);
			// @codeCoverageIgnoreEnd
		}

		parent::__construct($options, $matrix);
	}

	/**
	 * @inheritDoc
	 */
	protected function moduleValueIsValid($value):bool{
		return is_array($value) && count($value) >= 3;
	}

	/**
	 * @inheritDoc
	 */
	protected function getModuleValue($value):array{
		return array_values($value);
	}

	/**
	 * @inheritDoc
	 */
	protected function getDefaultModuleValue(bool $isDark):array{
		return $isDark ? [0, 0, 0] : [255, 255, 255];
	}

	/**
	 * @inheritDoc
	 *
	 * @return string|\FPDF
	 */
	public function dump(string $file = null){
		$file ??= $this->options->cachefile;

		$fpdf = new FPDF('P', $this->options->fpdfMeasureUnit, [$this->length, $this->length]);
		$fpdf->AddPage();

		$prevColor = null;

		foreach($this->matrix->matrix() as $y => $row){

			foreach($row as $x => $M_TYPE){
				/** @var int $M_TYPE */
				$color = $this->moduleValues[$M_TYPE];

				if($prevColor !== $color){
					/** @phan-suppress-next-line PhanParamTooFewUnpack */
					$fpdf->SetFillColor(...$color);
					$prevColor = $color;
				}

				$fpdf->Rect($x * $this->scale, $y * $this->scale, 1 * $this->scale, 1 * $this->scale, 'F');
			}

		}

		if($this->options->returnResource){
			return $fpdf;
		}

		$pdfData = $fpdf->Output('S');

		if($file !== null){
			$this->saveToFile($pdfData, $file);
		}

		if($this->options->imageBase64){
			$pdfData = $this->base64encode($pdfData, 'application/pdf');
		}

		return $pdfData;
	}

}
