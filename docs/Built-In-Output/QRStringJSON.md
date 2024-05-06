# QRStringJSON

[Class `QRStringJSON`](https://github.com/chillerlan/php-qrcode/blob/main/src/Output/QRStringJSON.php):
[JSON](https://developer.mozilla.org/en-US/docs/Glossary/JSON) output.


## Example

```php
$options = new QROptions;

$options->outputType = QROutputInterface::STRING_JSON;
$options->jsonFlags  = JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES;

header('Content-type: application/json');

$data   = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';

echo (new QRCode($options))->render($data); // -> JSON string
```

The associated [JSON schema](https://json-schema.org/specification) can be found over at GitHub: [`qrcode.schema.json`](https://github.com/chillerlan/php-qrcode/blob/main/src/Output/qrcode.schema.json)


## Previous functionality

The previous versions of `php-qrcode` (v5 and earlier) just dumped an array representation of the internal matrix,
which is equivalent to the following:

```php
$matrix = (new QRCode($options))->getQRMatrix(); // -> QRMatrix instance

// retrieve the internal matrix as an array of booleans
$json   = json_encode($matrix->getMatrix(true), $jsonFlags);
```


## Additional methods

| method                                            | return        | description                               |
|---------------------------------------------------|---------------|-------------------------------------------|
| (protected) `row(int $y, array $row)`             | `array\|null` | creates a DOM element for a matrix row    |
| (protected) `module(int $x, int $y, int $M_TYPE)` | `array\|null` | creates a DOM element for a single module |


## Options that affect this class

| property     | type  |
|--------------|-------|
| `$jsonFlags` | `int` |
