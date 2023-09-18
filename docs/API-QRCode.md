# QRCode

The full phpDocumentor API documentation can be found at [chillerlan.github.io/php-qrcode](https://chillerlan.github.io/php-qrcode/classes/chillerlan-QRCode-QRCode.html).


## Methods
<!-- using non-breaking spaces chr(255) in the longest method signature to force the silly table to stretch -->
| method                                                    | return          | description                                                                                                                                                                 |
|-----------------------------------------------------------|-----------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `__construct(SettingsContainerInterface $options = null)` | -               | see [`QROptions`](./API-QROptions.md) and [`SettingsContainerInterface`](https://github.com/chillerlan/php-settings-container/blob/main/src/SettingsContainerInterface.php) |
| `setOptions(SettingsContainerInterface $options)`         | `self`          | Sets an options instance, internally called via the constructor                                                                                                             |
| `render(string $data, string $file = null)`               | `mixed`         | renders a QR Code for the given `$data` and `QROptions`, saves `$file` optionally, output depends on the chosen mode, see `QROutputInterface::dump()`                       |
| `renderMatrix(QRMatrix $matrix, string $file = null)`     | `mixed`         | renders a QR Code for the given `QRMatrix` and `QROptions`, saves `$file` optionally, output depends on the chosen mode, see `QROutputInterface::dump()`                    |
| `getQRMatrix()`                                           | `QRMatrix`      | returns a `QRMatrix` object for the given `$data` and current `QROptions`                                                                                                   |
| `addSegment(QRDataModeInterface $segment)`                | `self`          | Adds a `QRDataModeInterface` segment                                                                                                                                        |
| `clearSegments()`                                         | `self`          | Clears the data segments array                                                                                                                                              |
| `addNumericSegment(string $data)`                         | `self`          | Adds a numeric data segment                                                                                                                                                 |
| `addAlphaNumSegment(string $data)`                        | `self`          | Adds an alphanumeric data segment                                                                                                                                           |
| `addKanjiSegment(string $data)`                           | `self`          | Adds a Kanji data segment (Japanese 13-bit double-byte characters, Shift-JIS)                                                                                               |
| `addHanziSegment(string $data)`                           | `self`          | Adds a Hanzi data segment (simplified Chinese 13-bit double-byte characters, GB2312/GB18030)                                                                                |
| `addByteSegment(string $data)`                            | `self`          | Adds an 8-bit byte data segment                                                                                                                                             |
| `addEciDesignator(int $encoding)`                         | `self`          | Adds a standalone ECI designator                                                                                                                                            |
| `addEciSegment(int $encoding, string $data)`              | `self`          | Adds an ECI data segment (including designator)                                                                                                                             |
| `readFromFile(string $path)`                              | `DecoderResult` | Reads a QR Code from a given file                                                                                                                                           |
| `readFromBlob(string $blob)`                              | `DecoderResult` | Reads a QR Code from the given data blob                                                                                                                                    |
| `readFromSource(LuminanceSourceInterface $source)`        | `DecoderResult` | Reads a QR Code from the given luminance source                                                                                                                             |


### Deprecated methods

| method                       | since   | replacement                                |
|------------------------------|---------|--------------------------------------------|
| `getMatrix()`                | `5.0.0` | `QRCode::getQRMatrix()`                    |
| `isNumber(string $string)`   | `5.0.0` | `Number::validateString(string $string)`   |
| `isAlphaNum(string $string)` | `5.0.0` | `AlphaNum::validateString(string $string)` |
| `isKanji(string $string)`    | `5.0.0` | `Kanji::validateString(string $string)`    |
| `isByte(string $string)`     | `5.0.0` | `Byte::validateString(string $string)`     |


##  Constants

### Deprecated constants

| name                 | since   | replacement                      |
|----------------------|---------|----------------------------------|
| `VERSION_AUTO`       | `5.0.0` | `Version::AUTO`                  |
| `MASK_PATTERN_AUTO`  | `5.0.0` | `MaskPattern::AUTO`              |
| `OUTPUT_MARKUP_SVG`  | `5.0.0` | `QROutputInterface::MARKUP_SVG`  |
| `OUTPUT_MARKUP_HTML` | `5.0.0` | `QROutputInterface::MARKUP_HTML` |
| `OUTPUT_IMAGE_PNG`   | `5.0.0` | `QROutputInterface::GDIMAGE_PNG` |
| `OUTPUT_IMAGE_JPG`   | `5.0.0` | `QROutputInterface::GDIMAGE_JPG` |
| `OUTPUT_IMAGE_GIF`   | `5.0.0` | `QROutputInterface::GDIMAGE_GIF` |
| `OUTPUT_STRING_JSON` | `5.0.0` | `QROutputInterface::STRING_JSON` |
| `OUTPUT_STRING_TEXT` | `5.0.0` | `QROutputInterface::STRING_TEXT` |
| `OUTPUT_IMAGICK`     | `5.0.0` | `QROutputInterface::IMAGICK`     |
| `OUTPUT_FPDF`        | `5.0.0` | `QROutputInterface::FPDF`        |
| `OUTPUT_CUSTOM`      | `5.0.0` | `QROutputInterface::CUSTOM`      |
| `ECC_L`              | `5.0.0` | `EccLevel::L`                    |
| `ECC_M`              | `5.0.0` | `EccLevel::M`                    |
| `ECC_Q`              | `5.0.0` | `EccLevel::Q`                    |
| `ECC_H`              | `5.0.0` | `EccLevel::H`                    |
| `DATA_NUMBER`        | `5.0.0` | `Mode::NUMBER`                   |
| `DATA_ALPHANUM`      | `5.0.0` | `Mode::ALPHANUM`                 |
| `DATA_BYTE`          | `5.0.0` | `Mode::BYTE`                     |
| `DATA_KANJI`         | `5.0.0` | `Mode::KANJI`                    |
