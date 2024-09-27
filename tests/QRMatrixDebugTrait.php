<?php
/**
 * QRMatrixDebugTrait.php
 *
 * @created      26.11.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRStringText;
use function defined, printf;

/**
 * Trait QRMatrixDebugTrait
 */
trait QRMatrixDebugTrait{

	/**
	 * Matrix debugging console output
	 */
	protected function debugMatrix(QRMatrix $matrix):void{

		/** @noinspection PhpUndefinedConstantInspection - see phpunit.xml.dist */
		if(defined('TEST_IS_CI') && TEST_IS_CI === true){
			return;
		}

		$options = new QROptions;

		$options->eol          = "\n";
		$options->moduleValues = [
			// finder
			QRMatrix::M_FINDER_DARK      => QRStringText::ansi8('██', 124),
			QRMatrix::M_FINDER           => QRStringText::ansi8('░░', 124),
			QRMatrix::M_FINDER_DOT       => QRStringText::ansi8('██', 124),
			QRMatrix::M_FINDER_DOT_LIGHT => QRStringText::ansi8('░░', 124),
			// alignment
			QRMatrix::M_ALIGNMENT_DARK   => QRStringText::ansi8('██', 2),
			QRMatrix::M_ALIGNMENT        => QRStringText::ansi8('░░', 2),
			// timing
			QRMatrix::M_TIMING_DARK      => QRStringText::ansi8('██', 184),
			QRMatrix::M_TIMING           => QRStringText::ansi8('░░', 184),
			// format
			QRMatrix::M_FORMAT_DARK      => QRStringText::ansi8('██', 200),
			QRMatrix::M_FORMAT           => QRStringText::ansi8('░░', 200),
			// version
			QRMatrix::M_VERSION_DARK     => QRStringText::ansi8('██', 21),
			QRMatrix::M_VERSION          => QRStringText::ansi8('░░', 21),
			// data
			QRMatrix::M_DATA_DARK        => QRStringText::ansi8('██', 166),
			QRMatrix::M_DATA             => QRStringText::ansi8('░░', 166),
			// dark module
			QRMatrix::M_DARKMODULE       => QRStringText::ansi8('██', 53),
			QRMatrix::M_DARKMODULE_LIGHT => QRStringText::ansi8('░░', 53),
			// separator
			QRMatrix::M_SEPARATOR_DARK   => QRStringText::ansi8('██', 219),
			QRMatrix::M_SEPARATOR        => QRStringText::ansi8('░░', 219),
			// quiet zone
			QRMatrix::M_QUIETZONE_DARK   => QRStringText::ansi8('██', 195),
			QRMatrix::M_QUIETZONE        => QRStringText::ansi8('░░', 195),
			// logo space
			QRMatrix::M_LOGO_DARK        => QRStringText::ansi8('██', 105),
			QRMatrix::M_LOGO             => QRStringText::ansi8('░░', 105),
			// empty
			QRMatrix::M_NULL             => QRStringText::ansi8('░░', 231),
		];

		$out = (new QRStringText($options, $matrix))->dump();

		printf("\n\n%s\n\n", $out);
	}

	/**
	 * debugging shortcut - limit to a single version when using with matrixProvider
	 *
	 * @see QRMatrixTest::matrixProvider()
	 */
	protected function dm(QRMatrix $matrix):void{

		// limit
		/** @noinspection PhpUndefinedConstantInspection - see phpunit.xml.dist */
		if(!defined('MATRIX_DEBUG_VERSION') || $matrix->getVersion()->getVersionNumber() !== MATRIX_DEBUG_VERSION){
			return;
		}

		$this->debugMatrix($matrix);
	}

}
