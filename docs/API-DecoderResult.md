# DecoderResult

The full phpDocumentor API documentation can be found at [chillerlan.github.io/php-qrcode](https://chillerlan.github.io/php-qrcode/classes/chillerlan-QRCode-Decoder-DecoderResult.html).


## Methods

| method                                     | return     | description                                                                                                |
|--------------------------------------------|------------|------------------------------------------------------------------------------------------------------------|
| `__construct(iterable $properties = null)` | -          | used internally by [`Decoder`](https://github.com/chillerlan/php-qrcode/blob/main/src/Decoder/Decoder.php) |
| `__toString()`                             | `string`   | returns the data contained in the QR symbol                                                                |
| `hasStructuredAppend()`                    | `bool`     |                                                                                                            |
| `getQRMatrix()`                            | `QRMatrix` |                                                                                                            |


## Magic Properties (read-only)

| property                    | type          | description      |
|-----------------------------|---------------|------------------|
| `$rawBytes`                 | `BitBuffer`   |                  |
| `$version`                  | `Version`     |                  |
| `$eccLevel`                 | `EccLevel`    |                  |
| `$maskPattern`              | `MaskPattern` |                  |
| `$data`                     | `string`      | defaults to `''` |
| `$structuredAppendParity`   | `int`         | defaults to `-1` |
| `$structuredAppendSequence` | `int`         | defaults to `-1` |
