# QRMarkupSVG

[Class `QRMarkupSVG`](https://github.com/chillerlan/php-qrcode/blob/main/src/Output/QRMarkupSVG.php): [Scalable Vector Graphics](https://developer.mozilla.org/en-US/docs/Glossary/SVG) (SVG) output

## Example

See: [ImageMagick example](https://github.com/chillerlan/php-qrcode/blob/main/examples/imagick.php)

Set the options:

```php
$options = new QROptions;

$options->version              = 7;
$options->outputType           = QROutputInterface::MARKUP_SVG;
// if set to false, the light modules won't be rendered
$options->drawLightModules     = true;
$options->svgUseFillAttributes = true;
// draw the modules as circles isntead of squares
$options->drawCircularModules  = true;
$options->circleRadius         = 0.4;
// connect paths to avoid render glitches
// @see https://github.com/chillerlan/php-qrcode/issues/57
$options->connectPaths         = true;
// keep modules of these types as square
$options->keepAsSquare         = [
	QRMatrix::M_FINDER_DARK,
	QRMatrix::M_FINDER_DOT,
	QRMatrix::M_ALIGNMENT_DARK,
];
// add a gradient via the <defs> element
// @see https://developer.mozilla.org/en-US/docs/Web/SVG/Element/defs
// @see https://developer.mozilla.org/en-US/docs/Web/SVG/Element/linearGradient
$options->svgDefs             = '
	<linearGradient id="rainbow" x1="1" y2="1">
		<stop stop-color="#e2453c" offset="0"/>
		<stop stop-color="#e07e39" offset="0.2"/>
		<stop stop-color="#e5d667" offset="0.4"/>
		<stop stop-color="#51b95b" offset="0.6"/>
		<stop stop-color="#1e72b7" offset="0.8"/>
		<stop stop-color="#6f5ba7" offset="1"/>
	</linearGradient>
	<style><![CDATA[
		.dark{fill: url(#rainbow);}
		.light{fill: #eee;}
	]]></style>';
```


Render the output:

```php
$data = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
$out  = (new QRCode($options))->render($data); // -> data:image/svg+xml;base64,PD94bWwgdmVyc2...

printf('<img alt="%s" src="%s" />', $alt, $out);
```


## Additional methods

| method                                            | return   | description                                                   |
|---------------------------------------------------|----------|---------------------------------------------------------------|
| (protected) `getCssClass(int $M_TYPE = 0)`        | `string` | returns a string with all css classes for the current element |
| (protected) `header()`                            | `string` | returns the `<svg>` header with the given options parsed      |
| (protected) `module(int $x, int $y, int $M_TYPE)` | `string` | returns a path segment for a single module                    |
| (protected) `path(string $path, int $M_TYPE)`     | `string` | renders and returns a single `<path>` element                 |
| (protected) `paths()`                             | `string` | returns one or more SVG `<path>` elements                     |


## Options that affect this module

| property                  | type        |
|---------------------------|-------------|
| `$circleRadius`           | `float`     |
| `$connectPaths`           | `bool`      |
| `$cssClass`               | `string`    |
| `$drawCircularModules`    | `bool`      |
| `$drawLightModules`       | `bool`      |
| `$eol`                    | `string`    |
| `$excludeFromConnect`     | `array`     |
| `$keepAsSquare`           | `array`     |
| `$outputBase64`           | `bool`      |
| `$svgAddXmlHeader`        | `bool`      |
| `$svgDefs`                | `string`    |
| `$svgOpacity`             | `float`     |
| `$svgPreserveAspectRatio` | `string`    |
| `$svgViewBoxSize`         | `int\|null` |
| `$svgUseFillAttributes`   | `bool`      |


### Options that have no effect

| property            | reason                                                                                                                                                                                                            |
|---------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$bgColor`          | background color can be achieved via CSS, attributes or the `<defs>` element, see also [php-qrcode/discussions/199 (comment)](https://github.com/chillerlan/php-qrcode/discussions/199#discussioncomment-5747471) |
| `$imageTransparent` | SVG is - similar to a HTML element - transparent by default                                                                                                                                                       |
| `$returnResource`   | N/A                                                                                                                                                                                                               |
| `$scale`            | `$scale` (pixel size of a qr module) is intended for raster image types, use `$svgViewBoxSize` instead                                                                                                            |
