<?php
/**
 * Class QRFpdf
 *
 * https://github.com/chillerlan/php-qrcode/pull/49
 *
 * @filesource   QRFpdf.php
 * @created      03.06.2020
 * @package      chillerlan\QRCode\Output
 * @author       Maximilian Kresse
 *
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\QRCodeException;
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

	public function __construct(SettingsContainerInterface $options, QRMatrix $matrix){

		if(!class_exists(FPDF::class)){
			// @codeCoverageIgnoreStart
			throw new QRCodeException(
				'The QRFpdf output requires FPDF as dependency but the class "\FPDF" couldn\'t be found.'
			);
			// @codeCoverageIgnoreEnd
		}

		parent::__construct($options, $matrix);
	}

	/**
	 * @inheritDoc
	 */
	protected function setModuleValues():void{

		foreach($this::DEFAULT_MODULE_VALUES as $M_TYPE => $defaultValue){
			$v = $this->options->moduleValues[$M_TYPE] ?? null;

			if(!is_array($v) || count($v) < 3){
				$this->moduleValues[$M_TYPE] = $defaultValue
					? [0, 0, 0]
					: [255, 255, 255];
			}
			else{
				$this->moduleValues[$M_TYPE] = array_values($v);
			}

		}

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

				if($prevColor === null || $prevColor !== $color){
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
			$pdfData = sprintf('data:application/pdf;base64,%s', base64_encode($pdfData));
		}

		return $pdfData;
	}

}
