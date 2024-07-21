<?php
/**
 * Class QRMarkupXML
 *
 * @created      01.05.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */
declare(strict_types=1);

namespace chillerlan\QRCode\Output;

use DOMDocument;
use DOMElement;
use function sprintf;

/**
 * XML/XSLT output
 */
class QRMarkupXML extends QRMarkup{

	final public const MIME_TYPE = 'application/xml';
	final public const SCHEMA    = 'https://raw.githubusercontent.com/chillerlan/php-qrcode/main/src/Output/qrcode.schema.xsd';

	protected DOMDocument $dom;

	/**
	 * @inheritDoc
	 * @return int[]
	 */
	protected function getOutputDimensions():array{
		return [$this->moduleCount, $this->moduleCount];
	}

	/**
	 * @inheritDoc
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function createMarkup(bool $saveToFile):string{
		/** @noinspection PhpComposerExtensionStubsInspection */
		$this->dom = new DOMDocument(encoding: 'UTF-8');
		$this->dom->formatOutput = true;

		if($this->options->xmlStylesheet !== null){
			$stylesheet = sprintf('type="text/xsl" href="%s"', $this->options->xmlStylesheet);
			$xslt       = $this->dom->createProcessingInstruction('xml-stylesheet', $stylesheet);

			$this->dom->appendChild($xslt);
		}

		$root = $this->dom->createElement('qrcode');

		$root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$root->setAttribute('xsi:noNamespaceSchemaLocation', $this::SCHEMA);
		$root->setAttribute('version', (string)$this->matrix->getVersion());
		$root->setAttribute('eccLevel', (string)$this->matrix->getEccLevel());
		$root->appendChild($this->createMatrix());

		$this->dom->appendChild($root);

		$xml = $this->dom->saveXML();

		if($xml === false){
			throw new QRCodeOutputException('XML error');
		}

		return $xml;
	}

	/**
	 * Creates the matrix element
	 */
	protected function createMatrix():DOMElement{
		[$width, $height] = $this->getOutputDimensions();
		$matrix           = $this->dom->createElement('matrix');
		$dimension        = $this->matrix->getVersion()->getDimension();

		$matrix->setAttribute('size', (string)$dimension);
		$matrix->setAttribute('quietzoneSize', (string)(int)(($this->moduleCount - $dimension) / 2));
		$matrix->setAttribute('maskPattern', (string)$this->matrix->getMaskPattern()->getPattern());
		$matrix->setAttribute('width', (string)$width);
		$matrix->setAttribute('height', (string)$height);

		foreach($this->matrix->getMatrix() as $y => $row){
			$matrixRow = $this->row($y, $row);

			if($matrixRow !== null){
				$matrix->appendChild($matrixRow);
			}
		}

		return $matrix;
	}

	/**
	 * Creates a DOM element for a matrix row
	 *
	 * @param int[] $row
	 */
	protected function row(int $y, array $row):DOMElement|null{
		$matrixRow = $this->dom->createElement('row');

		$matrixRow->setAttribute('y', (string)$y);

		foreach($row as $x => $M_TYPE){
			$module = $this->module($x, $y, $M_TYPE);

			if($module !== null){
				$matrixRow->appendChild($module);
			}

		}

		if($matrixRow->childElementCount > 0){
			return $matrixRow;
		}

		// skip empty rows
		return null;
	}

	/**
	 * Creates a DOM element for a single module
	 */
	protected function module(int $x, int $y, int $M_TYPE):DOMElement|null{
		$isDark = $this->matrix->isDark($M_TYPE);

		if(!$this->drawLightModules && !$isDark){
			return null;
		}

		$module = $this->dom->createElement('module');

		$module->setAttribute('x', (string)$x);
		$module->setAttribute('dark', (($isDark) ? 'true' : 'false'));
		$module->setAttribute('layer', ($this::LAYERNAMES[$M_TYPE] ?? ''));
		$module->setAttribute('value', (string)$this->getModuleValue($M_TYPE));

		return $module;
	}

}
