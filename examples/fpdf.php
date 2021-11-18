<?php

namespace chillerlan\QRCodeExamples;

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Common\EccLevel;

require_once __DIR__ . '/../vendor/autoload.php';

$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';

$options = new QROptions([
    'version'      => 7,
    'outputType'   => QRCode::OUTPUT_FPDF,
    'eccLevel'     => EccLevel::L,
    'scale'        => 5,
    'imageBase64'  => false,
    'moduleValues' => [
        // finder
        QRMatrix::M_FINDER | QRMatrix::IS_DARK     => [0, 63, 255], // dark (true)
        QRMatrix::M_FINDER                         => [255, 255, 255], // light (false), white is the transparency color and is enabled by default
        // alignment
        QRMatrix::M_ALIGNMENT | QRMatrix::IS_DARK  => [255, 0, 255],
        QRMatrix::M_ALIGNMENT                      => [255, 255, 255],
        // timing
        QRMatrix::M_TIMING | QRMatrix::IS_DARK     => [255, 0, 0],
        QRMatrix::M_TIMING                         => [255, 255, 255],
        // format
        QRMatrix::M_FORMAT | QRMatrix::IS_DARK     => [67, 191, 84],
        QRMatrix::M_FORMAT                         => [255, 255, 255],
        // version
        QRMatrix::M_VERSION | QRMatrix::IS_DARK    => [62, 174, 190],
        QRMatrix::M_VERSION                        => [255, 255, 255],
        // data
        QRMatrix::M_DATA | QRMatrix::IS_DARK       => [0, 0, 0],
        QRMatrix::M_DATA                           => [255, 255, 255],
        // darkmodule
        QRMatrix::M_DARKMODULE | QRMatrix::IS_DARK => [0, 0, 0],
        // separator
        QRMatrix::M_SEPARATOR                      => [255, 255, 255],
        // quietzone
        QRMatrix::M_QUIETZONE                      => [255, 255, 255],
    ],
]);

\header('Content-type: application/pdf');

echo (new QRCode($options))->render($data);
