# QRFpdf

[Class `QRFpdf`](https://github.com/chillerlan/php-qrcode/blob/main/src/Output/QRFpdf.php): [Portable Document Format](https://en.wikipedia.org/wiki/PDF) (PDF) output via [FPDF](https://github.com/setasign/fpdf)


## Example

See: [FPDF example](https://github.com/chillerlan/php-qrcode/blob/main/examples/fpdf.php)

Set the options:

```php
$options = new QROptions;

$options->outputType       = QROutputInterface::FPDF;
$options->scale            = 5;
$options->fpdfMeasureUnit  = 'mm'; // pt, mm, cm, in
$options->bgColor          = [222, 222, 222]; // [R, G, B]
$options->drawLightModules = false;
$options->moduleValues     = [
	QRMatrix::M_FINDER_DARK    => [0, 63, 255],    // dark (true)
	QRMatrix::M_FINDER_DOT     => [0, 63, 255],    // finder dot, dark (true)
	QRMatrix::M_FINDER         => [255, 255, 255], // light (false)
	QRMatrix::M_ALIGNMENT_DARK => [255, 0, 255],
	QRMatrix::M_ALIGNMENT      => [255, 255, 255],
	QRMatrix::M_DATA_DARK      => [0, 0, 0],
	QRMatrix::M_DATA           => [255, 255, 255],
];
```


Render the output:

```php
$data = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
$out  = (new QRCode($options))->render($data); // -> data:application/pdf;base64,...

echo $out;
```


Return the `FPDF` instance (will ignore other output options):

```php
$options->returnResource = true;

/** @var \FPDF $fpdf */
$fpdf = (new QRCode($options))->render($data);

// do stuff with the FPDF instance...

// ...dump output
header('application/pdf');

echo $fpdf->Output('S');
```


## Additional methods

| method                                            | return | description                  |
|---------------------------------------------------|--------|------------------------------|
| (protected) `initFPDF()`                          | `FPDF` | Initializes an FPDF instance |
| (protected) `module(int $x, int $y, int $M_TYPE)` | `void` | Renders a single module      |


## Options that affect this module

| property            | type     |
|---------------------|----------|
| `$bgColor`          | `array`  |
| `$drawLightModules` | `bool`   |
| `$fpdfMeasureUnit`  | `string` |
| `$outputBase64`     | `bool`   |
| `$returnResource`   | `bool`   |
| `$scale`            | `ìnt`    |


### Options that have no effect

| property               | reason |
|------------------------|--------|
| `$circleRadius`        | N/A    |
| `$connectPaths`        | N/A    |
| `$drawCircularModules` | N/A    |
| `$excludeFromConnect`  | N/A    |
| `$imageTransparent`    | N/A    |
| `$keepAsSquare`        | N/A    |
