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
declare(strict_types=1);

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\Settings\SettingsContainerInterface;
use FPDF;

use function class_exists;

/**
 * QRFpdf output module (requires fpdf)
 *
 * @see https://github.com/Setasign/FPDF
 * @see http://www.fpdf.org/
 */
class QRFpdf extends QROutputAbstract{
	use RGBArrayModuleValueTrait;

	final public const MIME_TYPE = 'application/pdf';

	/** @var int[]  */
	protected array|null $prevColor = null;
	protected FPDF       $fpdf;

	/**
	 * QRFpdf constructor.
	 *
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function __construct(SettingsContainerInterface|QROptions $options, QRMatrix $matrix){

		if(!class_exists(FPDF::class)){
			// @codeCoverageIgnoreStart
			throw new QRCodeOutputException(
				'The QRFpdf output requires FPDF (https://github.com/Setasign/FPDF)'.
				// phpcs:ignore
				' as dependency but the class "\\FPDF" could not be found.'
			);
			// @codeCoverageIgnoreEnd
		}

		parent::__construct($options, $matrix);
	}

	/**
	 * Initializes an FPDF instance
	 */
	protected function initFPDF():FPDF{
		$fpdf = new FPDF('P', $this->options->fpdfMeasureUnit, $this->getOutputDimensions());
		$fpdf->AddPage();

		return $fpdf;
	}

	public function dump(string|null $file = null, FPDF|null $fpdf = null):string|FPDF{
		$this->fpdf = ($fpdf ?? $this->initFPDF());

		if($this::moduleValueIsValid($this->options->bgColor)){
			$bgColor          = $this->prepareModuleValue($this->options->bgColor);
			[$width, $height] = $this->getOutputDimensions();

			$this->fpdf->SetFillColor(...$bgColor);
			$this->fpdf->Rect(0, 0, $width, $height, 'F');
		}

		$this->prevColor = null;

		foreach($this->matrix->getMatrix() as $y => $row){
			foreach($row as $x => $M_TYPE){
				$this->module($x, $y, $M_TYPE);
			}
		}

		if($this->options->returnResource){
			return $this->fpdf;
		}

		$pdfData = $this->fpdf->Output('S');

		$this->saveToFile($pdfData, $file);

		if($this->options->outputBase64){
			$pdfData = $this->toBase64DataURI($pdfData);
		}

		return $pdfData;
	}

	/**
	 * Renders a single module
	 */
	protected function module(int $x, int $y, int $M_TYPE):void{

		if(!$this->drawLightModules && !$this->matrix->isDark($M_TYPE)){
			return;
		}

		$color = $this->getModuleValue($M_TYPE);

		if($color !== null && $color !== $this->prevColor){
			$this->fpdf->SetFillColor(...$color);
			$this->prevColor = $color;
		}

		$this->fpdf->Rect(($x * $this->scale), ($y * $this->scale), $this->scale, $this->scale, 'F');
	}

}
