# QRMarkupHTML

[Class `QRMarkupHTML`](https://github.com/chillerlan/php-qrcode/blob/main/src/Output/QRMarkupHTML.php): HTML output

This class is a cheap markup substitute for when SVG is not available or not an option (which was an issue before ca 2012).
As a general rule: if you plan to display the QR Code in a web browser, you should be using the [SVG output](../Built-In-Output/QRMarkupSVG.md).


## Example

See: [HTML example](https://github.com/chillerlan/php-qrcode/blob/main/examples/html.php)

Set the options:

```php
$options = new QROptions;

$options->outputType   = QROutputInterface::MARKUP_HTML;
$options->cssClass     = 'qrcode';
$options->moduleValues = [
	// finder
	QRMatrix::M_FINDER_DARK    => '#A71111', // dark (true)
	QRMatrix::M_FINDER_DOT     => '#A71111', // finder dot, dark (true)
	QRMatrix::M_FINDER         => '#FFBFBF', // light (false)
	// alignment
	QRMatrix::M_ALIGNMENT_DARK => '#A70364',
	QRMatrix::M_ALIGNMENT      => '#FFC9C9',
];
```

Output in a HTML document (via PHP):

```php
<?php

$data = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
$out  = (new QRCode($options))->render($data);

header('Content-type: text/html');

?>
<!DOCTYPE html>
<html lang="none">
<head>
	<meta charset="UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title>QRCode HTML Example</title>
	<style>
		div.qrcode{
			margin: 1em;
		}

		/* rows */
		div.qrcode > div {
			height: 10px;
		}

		/* modules */
		div.qrcode > div > span {
			display: inline-block;
			width: 10px;
			height: 10px;
		}
	</style>
</head>
<body>
<!-- php poutput -->
<?php echo $out; ?>
</body>
</html>
```


## Additional methods

| method                                       | return   | description                                                             |
|----------------------------------------------|----------|-------------------------------------------------------------------------|
| (protected) `createMarkup(bool $saveToFile)` | `string` | Returns the fully parsed and rendered markup string for the given input |
| (protected) `getCssClass(int $M_TYPE = 0)`   | `string` | Returns a string with all css classes for the current element           |


## Options that affect this module

| property       | type     |
|----------------|----------|
| `$cssClass`    | `string` |
| `$eol`         | `string` |


### Options that have no effect

| property               | reason  |
|------------------------|---------|
| `$bgColor`             | via CSS |
| `$circleRadius`        | N/A     |
| `$connectPaths`        | N/A     |
| `$drawCircularModules` | N/A     |
| `$drawLightModules`    | N/A     |
| `$excludeFromConnect`  | N/A     |
| `$imageTransparent`    | N/A     |
| `$keepAsSquare`        | N/A     |
| `$outputBase64`        | N/A     |
| `$returnResource`      | N/A     |
| `$scale`               | via CSS |
