<?php
/**
 * Class QRStringJSON
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

use JsonException;
use function json_encode;

/**
 * JSON Output
 *
 * @method string getModuleValue(int $M_TYPE)
 *
 * @phpstan-type Module array{x: int, dark: bool, layer: string, value: string}
 */
class QRStringJSON extends QROutputAbstract{
	use CssColorModuleValueTrait;

	final public const MIME_TYPE = 'application/json';
	final public const SCHEMA    = 'https://raw.githubusercontent.com/chillerlan/php-qrcode/main/src/Output/qrcode.schema.json';

	/**
	 * @inheritDoc
	 *
	 * @return int[]
	 */
	protected function getOutputDimensions():array{
		return [$this->moduleCount, $this->moduleCount];
	}

	/**
	 * @inheritDoc
	 * @throws \JsonException
	 */
	public function dump(string|null $file = null):string{
		[$width, $height] = $this->getOutputDimensions();
		$version          = $this->matrix->getVersion();
		$dimension        = $version->getDimension();

		$json = [
			'$schema' => $this::SCHEMA,
			'qrcode'  => [
				'version'  => $version->getVersionNumber(),
				'eccLevel' => (string)$this->matrix->getEccLevel(),
				'matrix'   => [
					'size'          => $dimension,
					'quietzoneSize' => (int)(($this->moduleCount - $dimension) / 2),
					'maskPattern'   => $this->matrix->getMaskPattern()->getPattern(),
					'width'         => $width,
					'height'        => $height,
					'rows'          => [],
				],
			],
		];

		foreach($this->matrix->getMatrix() as $y => $row){
			$matrixRow = $this->row($y, $row);

			if($matrixRow !== null){
				$json['qrcode']['matrix']['rows'][] = $matrixRow;
			}
		}

		$data = json_encode($json, $this->options->jsonFlags);

		if($data === false){
			throw new JsonException('error while encoding JSON');
		}

		$this->saveToFile($data, $file);

		return $data;
	}

	/**
	 * Creates an array element for a matrix row
	 *
	 * @param  int[] $row
	 * @phpstan-return array{y: int, modules: array<int, Module>}
	 */
	protected function row(int $y, array $row):array|null{
		$matrixRow = ['y' => $y, 'modules' => []];

		foreach($row as $x => $M_TYPE){
			$module = $this->module($x, $y, $M_TYPE);

			if($module !== null){
				$matrixRow['modules'][] = $module;
			}
		}

		if(!empty($matrixRow['modules'])){
			return $matrixRow;
		}

		// skip empty rows
		return null;
	}

	/**
	 * Creates an array element for a single module
	 *
	 * @phpstan-return Module
	 */
	protected function module(int $x, int $y, int $M_TYPE):array|null{
		$isDark = $this->matrix->isDark($M_TYPE);

		if(!$this->drawLightModules && !$isDark){
			return null;
		}

		return [
			'x'     => $x,
			'dark'  => $isDark,
			'layer' => ($this::LAYERNAMES[$M_TYPE] ?? ''),
			'value' => $this->getModuleValue($M_TYPE),
		];
	}

}
