# QRString

[Class `QRString`](https://github.com/chillerlan/php-qrcode/blob/main/src/Output/QRString.php): String output: plain text, [JSON](https://developer.mozilla.org/en-US/docs/Glossary/JSON)

## Plain text

Render in a CLI console, using [ANSI colors](https://en.wikipedia.org/wiki/ANSI_escape_code#Colors) and [block elements](https://en.wikipedia.org/wiki/Block_Elements):

```php
// a little helper to a create proper ANSI 8-bit color escape sequence
function ansi8(string $str, int $color, bool $background = false):string{
	$color      = max(0, min($color, 255));
	$background = ($background ? 48 : 38);

	return sprintf("\x1b[%s;5;%sm%s\x1b[0m", $background, $color, $str);
}

$options = new QROptions;

$options->outputType     = QROutputInterface::STRING_TEXT;
$options->eol            = "\n";
// add some space on the line start
$options->textLineStart  = str_repeat(' ', 6);
// default values for unassigned module types
$options->textDark       = QRString::ansi8('██', 253);
$options->textLight      = QRString::ansi8('░░', 253);
$options->moduleValues   = [
	QRMatrix::M_FINDER_DARK    => QRString::ansi8('██', 124),
	QRMatrix::M_FINDER         => QRString::ansi8('░░', 124),
	QRMatrix::M_FINDER_DOT     => QRString::ansi8('██', 124),
	QRMatrix::M_ALIGNMENT_DARK => QRString::ansi8('██', 2),
	QRMatrix::M_ALIGNMENT      => QRString::ansi8('░░', 2),
	QRMatrix::M_VERSION_DARK   => QRString::ansi8('██', 21),
	QRMatrix::M_VERSION        => QRString::ansi8('░░', 21),
];
```


Output:

```php
$data   = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
$qrcode = (new QRCode($options))->render($data);

echo "\n\n$qrcode\n\n";
```


## JSON

```php
$options = new QROptions;

$options->outputType     = QROutputInterface::STRING_JSON;
// output the integer values ($M_TYPE) held in the matrix object
$options->jsonAsBooleans = false;

header('Content-type: application/json');

echo (new QRCode($options))->render($data);
```


## Additional methods

| method                                                    | return   | description                                                         |
|-----------------------------------------------------------|----------|---------------------------------------------------------------------|
| (protected) `text()`                                      | `string` | string output                                                       |
| (protected) `json()`                                      | `string` | JSON output                                                         |
| `ansi8(string $str, int $color, bool $background = null)` | `string` | a little helper to create a proper ANSI 8-bit color escape sequence |


## Options that affect this module

| property          | type     |
|-------------------|----------|
| `$eol`            | `string` |
| `$jsonAsBooleans` | `bool`   |
| `$textDark`       | `string` |
| `$textLight`      | `string` |
| `$textLineStart`  | `string` |


### Options that have no effect

| property               | reason          |
|------------------------|-----------------|
| `$bgColor`             | N/A             |
| `$circleRadius`        | N/A             |
| `$connectPaths`        | N/A             |
| `$drawCircularModules` | N/A             |
| `$drawLightModules`    | not implemented |
| `$excludeFromConnect`  | N/A             |
| `$imageTransparent`    | N/A             |
| `$keepAsSquare`        | N/A             |
| `$outputBase64`        | N/A             |
| `$returnResource`      | N/A             |
| `$scale`               | N/A             |
