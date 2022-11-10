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

use function class_exists, count, is_array, is_numeric, max, min;

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

		if(!is_array($value) || count($value) < 3){
			return false;
		}

		// check the first 3 values of the array
		for($i = 0; $i < 3; $i++){
			if(!is_numeric($value[$i])){
				return false;
			}
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function getModuleValue($value):array{
		$v = [];

		for($i = 0; $i < 3; $i++){
			// clamp value
			$v[] = (int)max(0, min(255, $value[$i]));
		}

		return $v;
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

		if($this->moduleValueIsValid($this->options->bgColor)){
			$bgColor = $this->getModuleValue($this->options->bgColor);
			/** @phan-suppress-next-line PhanParamTooFewUnpack */
			$fpdf->SetFillColor(...$bgColor);
			$fpdf->Rect(0, 0, $this->length, $this->length, 'F');
		}

		$prevColor = null;

		foreach($this->matrix->matrix() as $y => $row){

			foreach($row as $x => $M_TYPE){

				if(!$this->options->drawLightModules && !$this->matrix->check($x, $y)){
					continue;
				}

				/** @var int $M_TYPE */
				$color = $this->moduleValues[$M_TYPE];

				if($prevColor !== $color){
					/** @phan-suppress-next-line PhanParamTooFewUnpack */
					$fpdf->SetFillColor(...$color);
					$prevColor = $color;
				}

				$fpdf->Rect($x * $this->scale, $y * $this->scale, $this->scale, $this->scale, 'F');
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
			$pdfData = $this->toBase64DataURI($pdfData, 'application/pdf');
		}

		return $pdfData;
	}

}
