# QRStringText

[Class `QRStringText`](https://github.com/chillerlan/php-qrcode/blob/main/src/Output/QRStringText.php):
render in a CLI console, using [ANSI colors](https://en.wikipedia.org/wiki/ANSI_escape_code#Colors) and [block elements](https://en.wikipedia.org/wiki/Block_Elements).


## Example

See: [plaintext example](https://github.com/chillerlan/php-qrcode/blob/main/examples/text.php)

```php
$options = new QROptions;

$options->outputInterface = QRStringText::class;
$options->eol             = "\n";
// add some space on the line start
$options->textLineStart   = str_repeat(' ', 6);
// default values for unassigned module types
$options->textDark        = QRStringText::ansi8('██', 253);
$options->textLight       = QRStringText::ansi8('░░', 253);
$options->moduleValues    = [
	QRMatrix::M_FINDER_DARK    => QRStringText::ansi8('██', 124),
	QRMatrix::M_FINDER         => QRStringText::ansi8('░░', 124),
	QRMatrix::M_FINDER_DOT     => QRStringText::ansi8('██', 124),
	QRMatrix::M_ALIGNMENT_DARK => QRStringText::ansi8('██', 2),
	QRMatrix::M_ALIGNMENT      => QRStringText::ansi8('░░', 2),
	QRMatrix::M_VERSION_DARK   => QRStringText::ansi8('██', 21),
	QRMatrix::M_VERSION        => QRStringText::ansi8('░░', 21),
];
```


Output:

```php
$data   = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
$qrcode = (new QRCode($options))->render($data);

echo "\n\n$qrcode\n\n";
```


## Additional methods

| method                                                    | return   | description                                                         |
|-----------------------------------------------------------|----------|---------------------------------------------------------------------|
| `ansi8(string $str, int $color, bool $background = null)` | `string` | a little helper to create a proper ANSI 8-bit color escape sequence |


## Options that affect this class

| property          | type     |
|-------------------|----------|
| `$eol`            | `string` |
| `$textDark`       | `string` |
| `$textLight`      | `string` |
| `$textLineStart`  | `string` |
