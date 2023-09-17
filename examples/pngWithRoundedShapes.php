<?php

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRGdImage;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

require_once __DIR__.'/../vendor/autoload.php';

// --------------------
// Class definition
// --------------------

class QRCustomPNG extends QRGdImage {

    protected function setPixel(int $x, int $y, int $M_TYPE): void {

        $x1 = ($x * $this->scale);
        $y1 = ($y * $this->scale);
        $x2 = (($x + 1) * $this->scale);
        $y2 = (($y + 1) * $this->scale);

        $rectsize = $this->scale / 2;

        /**
         * @var int $neighbours
         * The right bit order (starting from 0):
         *   0 1 2
         *   7 # 3
         *   6 5 4
         */
        $neighbours = $this->matrix->checkNeighbours($x, $y);

        // ------------------
        // Outer rounding
        // ------------------

        if ($neighbours & (1 << 7)) { // neighbour left
            // top left
            imagefilledrectangle(
                $this->image,
                $x1,
                $y1,
                $x1 + $rectsize,
                $y1 + $rectsize,
                $this->moduleValues[$M_TYPE]
            );
            // bottom left
            imagefilledrectangle(
                $this->image,
                $x1,
                $y2 - $rectsize,
                $x1 + $rectsize,
                $y2,
                $this->moduleValues[$M_TYPE]
            );
        }

        if ($neighbours & (1 << 3)) { // neighbour right
            // top right
            imagefilledrectangle(
                $this->image,
                $x2 - $rectsize,
                $y1,
                $x2,
                $y1 + $rectsize,
                $this->moduleValues[$M_TYPE]
            );
            // bottom right
            imagefilledrectangle(
                $this->image,
                $x2 - $rectsize,
                $y2 - $rectsize,
                $x2,
                $y2,
                $this->moduleValues[$M_TYPE]
            );
        }

        if ($neighbours & (1 << 1)) { // neighbour сверху
            // top left
            imagefilledrectangle(
                $this->image,
                $x1,
                $y1,
                $x1 + $rectsize,
                $y1 + $rectsize,
                $this->moduleValues[$M_TYPE]
            );
            // top right
            imagefilledrectangle(
                $this->image,
                $x2 - $rectsize,
                $y1,
                $x2,
                $y1 + $rectsize,
                $this->moduleValues[$M_TYPE]
            );
        }

        if ($neighbours & (1 << 5)) { // neighbour снизу
            // bottom left
            imagefilledrectangle(
                $this->image,
                $x1,
                $y2 - $rectsize,
                $x1 + $rectsize,
                $y2,
                $this->moduleValues[$M_TYPE]
            );
            // bottom right
            imagefilledrectangle(
                $this->image,
                $x2 - $rectsize,
                $y2 - $rectsize,
                $x2,
                $y2,
                $this->moduleValues[$M_TYPE]
            );
        }

        // ---------------------
        // inner rounding
        // ---------------------

        if (!$this->matrix->check($x, $y)) {

            if ($neighbours & 1 && $neighbours & (1 << 7) && $neighbours & (1 << 1)) {
                // top left
                imagefilledrectangle(
                    $this->image,
                    $x1,
                    $y1,
                    $x1 + $rectsize,
                    $y1 + $rectsize,
                    $this->moduleValues[$M_TYPE | QRMatrix::IS_DARK]
                );
            }

            if ($neighbours & (1 << 1) && $neighbours & (1 << 2) && $neighbours & (1 << 3)) {
                // top right
                imagefilledrectangle(
                    $this->image,
                    $x2 - $rectsize,
                    $y1,
                    $x2,
                    $y1 + $rectsize,
                    $this->moduleValues[$M_TYPE | QRMatrix::IS_DARK]
                );
            }

            if ($neighbours & (1 << 7) && $neighbours & (1 << 6) && $neighbours & (1 << 5)) {
                // bottom left
                imagefilledrectangle(
                    $this->image,
                    $x1,
                    $y2 - $rectsize,
                    $x1 + $rectsize,
                    $y2,
                    $this->moduleValues[$M_TYPE | QRMatrix::IS_DARK]
                );
            }

            if ($neighbours & (1 << 3) && $neighbours & (1 << 4) && $neighbours & (1 << 5)) {
                // bottom right
                imagefilledrectangle(
                    $this->image,
                    $x2 - $rectsize,
                    $y2 - $rectsize,
                    $x2,
                    $y2,
                    $this->moduleValues[$M_TYPE | QRMatrix::IS_DARK]
                );
            }
        }

        imagefilledellipse(
            $this->image,
            (int)(($x * $this->scale) + ($this->scale / 2)),
            (int)(($y * $this->scale) + ($this->scale / 2)),
            (int)(2 * $this->options->circleRadius * $this->scale),
            (int)(2 * $this->options->circleRadius * $this->scale),
            $this->moduleValues[$M_TYPE]
        );
    }
}


// --------------------
// Example
// --------------------

$options = new QROptions([
    'outputType'=> QROutputInterface::CUSTOM,
    'outputInterface' => QRCustomPNG::class,
    'eccLevel' => EccLevel::M,
    'imageTransparent' => false,
    'imageBase64' => false,
    'scale' => 20
]);

$qrcode = new QRCode($options);

$img = $qrcode->render("https://www.youtube.com/watch?v=dQw4w9WgXcQ");

header('Content-type: image/png');

echo $img;
