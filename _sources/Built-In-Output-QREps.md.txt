# QREps

[Class `QREps`](https://github.com/chillerlan/php-qrcode/blob/main/src/Output/QREps.php): [Encapsulated Postscript](https://en.wikipedia.org/wiki/Encapsulated_PostScript) (EPS) output.


## Example

See: [EPS example](https://github.com/chillerlan/php-qrcode/blob/main/examples/eps.php)

Set the options:

```php
$options = new QROptions;

$options->outputType       = QROutputInterface::EPS;
$options->scale            = 5;
$options->drawLightModules = false;
// colors can be specified either as [R, G, B] or [C, M, Y, K] (0-255)
$options->bgColor          = [222, 222, 222];
$options->moduleValues     = [
	QRMatrix::M_FINDER_DARK    => [0, 63, 255],    // dark (true)
	QRMatrix::M_FINDER_DOT     => [0, 63, 255],    // finder dot, dark (true)
	QRMatrix::M_FINDER         => [233, 233, 233], // light (false)
	QRMatrix::M_ALIGNMENT_DARK => [255, 0, 255],
	QRMatrix::M_ALIGNMENT      => [233, 233, 233],
	QRMatrix::M_DATA_DARK      => [0, 0, 0],
	QRMatrix::M_DATA           => [233, 233, 233],
];
```


Render and save to file:

```php
$data = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
$file = __DIR__.'/qrcode.eps';

(new QRCode($options))->render($data, $file);
```


Push as file download in a browser:

```php
header('Content-type: application/postscript');
header('Content-Disposition: filename="qrcode.eps"');

echo (new QRCode($options))->render($data);

exit;
```

## Additional methods

| method                                            | return   | description                                |
|---------------------------------------------------|----------|--------------------------------------------|
| (protected) `formatColor(array $values)`          | `string` | Set the color format string                |
| (protected) `module(int $x, int $y, int $M_TYPE)` | `string` | Returns a path segment for a single module |


## Options that affect this module

| property              | type    |
|-----------------------|---------|
| `$bgColor`            | `array` |
| `$connectPaths`       | `bool`  |
| `$drawLightModules`   | `bool`  |
| `$excludeFromConnect` | `array` |
| `$scale`              | `int`   |


### Options that have no effect

| property               | reason          |
|------------------------|-----------------|
| `$circleRadius`        | not implemented |
| `$drawCircularModules` | not implemented |
| `$outputBase64`        | N/A             |
| `$imageTransparent`    | N/A             |
| `$keepAsSquare`        | not implemented |
| `$returnResource`      | N/A             |
