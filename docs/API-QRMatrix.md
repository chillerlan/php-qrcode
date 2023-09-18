# QRMatrix

The full phpDocumentor API documentation can be found at [chillerlan.github.io/php-qrcode](https://chillerlan.github.io/php-qrcode/classes/chillerlan-QRCode-Data-QRMatrix.html).


## Methods
<!-- using non-breaking spaces chr(255) in the longest method signature to force the silly table to stretch -->
| method                                                                                 | return              | description                                                                                                                |
|----------------------------------------------------------------------------------------|---------------------|----------------------------------------------------------------------------------------------------------------------------|
| `__construct(Version $version, EccLevel $eccLevel)`                                    | -                   |                                                                                                                            |
| `initFunctionalPatterns()`                                                             | `self`              | shortcut to initialize the functional patterns                                                                             |
| `getMatrix()`                                                                          | `array`             | the internal matrix representation as a 2 dimensional array                                                                |
| `getVersion()`                                                                         | `Version\|null`     | the current QR Code version instance                                                                                       |
| `getEccLevel()`                                                                        | `EccLevel\|null`    | the current ECC level instance                                                                                             |
| `getMaskPattern()`                                                                     | `MaskPattern\|null` | the used mask pattern instance                                                                                             |
| `getSize()`                                                                            | `int`               | the absoulute size of the matrix, including quiet zone (if set). `$version * 4 + 17 + 2 * $quietzone`                      |
| `get(int $x, int $y)`                                                                  | `int`               | returns the value of the module                                                                                            |
| `set(int $x, int $y, bool $value, int $M_TYPE)`                                        | `self`              | sets the `$M_TYPE` value for the module                                                                                    |
| `setArea(int $startX, int $startY, int $width, int $height, bool $value, int $M_TYPE)` | `self`              | Fills an area of $width * $height, from the given starting point $startX, $startY (top left) with $value for $M_TYPE       |
| `checkType(int $x, int $y, int $M_TYPE)`                                               | `bool`              | Checks whether a module is of the given $M_TYPE                                                                            |
| `checkTypeIn(int $x, int $y, array $M_TYPES)`                                          | `bool`              | Checks whether the module at ($x, $y) is in the given array of $M_TYPES, returns true if a match is found, otherwise false |
| `check(int $x, int $y)`                                                                | `bool`              | checks whether a module is true (dark) or false (light)                                                                    |
| `checkNeighbours(int $x, int $y, int $M_TYPE = null)`                                  | `int`               | Checks the status neighbouring modules of the given module at ($x, $y) and returns a bitmask with the results.             |
| `setDarkModule()`                                                                      | `self`              |                                                                                                                            |
| `setFinderPattern()`                                                                   | `self`              |                                                                                                                            |
| `setSeparators()`                                                                      | `self`              |                                                                                                                            |
| `setAlignmentPattern()`                                                                | `self`              |                                                                                                                            |
| `setTimingPattern()`                                                                   | `self`              |                                                                                                                            |
| `setVersionNumber()`                                                                   | `self`              |                                                                                                                            |
| `setFormatInfo(MaskPattern $maskPattern = null)`                                       | `self`              |                                                                                                                            |
| `setQuietZone(int $quietZoneSize)`                                                     | `self`              | Draws the "quiet zone" of $quietZoneSize around the matrix                                                                 |
| `rotate90()`                                                                           | `self`              | Rotates the matrix by 90 degrees clock wise                                                                                |
| `setLogoSpace(int $width, int $height = null, int $startX = null, int $startY = null)` | `self`              | Clears a space of $width * $height in order to add a logo or text.                                                         |
| `writeCodewords(BitBuffer $bitBuffer)`                                                 | `self`              | Maps the interleaved binary data on the matrix                                                                             |
| `mask(MaskPattern $maskPattern)`                                                       | `self`              | Applies/reverses the mask pattern                                                                                          |


### Deprecated methods

| method          | since   | replacement                  |
|-----------------|---------|------------------------------|
| `matrix()`      | `5.0.0` | `QRMatrix::getMatrix()`      |
| `eccLevel()`    | `5.0.0` | `QRMatrix::getEccLevel()`    |
| `version()`     | `5.0.0` | `QRMatrix::getVersion()`     |
| `maskPattern()` | `5.0.0` | `QRMatrix::getMaskPattern()` |
| `size()`        | `5.0.0` | `QRMatrix::getSize()`        |


## Constants

The `_DARK` and `_LIGHT` postfixed constans exist purely for convenience - their value is the same as
`QRMatrix::M_XXX | QRMatrix::IS_DARK` and `QRMatrix::M_XXX ^ QRMatrix::IS_DARK` respectively,
see [`QROutputInterface`](./API-QROutputInterface.md).

| name                 | description                                                                       |
|----------------------|-----------------------------------------------------------------------------------|
| `IS_DARK`            | sets the "dark" flag for the given value: `QRMatrix::M_DATA \| QRMatrix::IS_DARK` |
| `M_NULL`             | module not set                                                                    |
| `M_DARKMODULE_LIGHT` | convenience (reversed reflectance)                                                |
| `M_DARKMODULE`       | once per matrix at `$xy = [8, 4 * $version + 9]`                                  |
| `M_DATA`             | the actual encoded data                                                           |
| `M_DATA_DARK`        | convenience                                                                       |
| `M_FINDER`           | the 7x7 finder patterns                                                           |
| `M_FINDER_DARK`      | convenience                                                                       |
| `M_FINDER_DOT_LIGHT` | convenience (reversed reflectance)                                                |
| `M_FINDER_DOT`       | the inner 3x3 block of the finder pattern                                         |
| `M_SEPARATOR`        | separator lines along the finder patterns                                         |
| `M_SEPARATOR_DARK`   | convenience                                                                       |
| `M_ALIGNMENT`        | the 5x5 alignment patterns                                                        |
| `M_ALIGNMENT_DARK`   | convenience                                                                       |
| `M_TIMING`           | the timing pattern lines                                                          |
| `M_TIMING_DARK`      | convenience                                                                       |
| `M_FORMAT`           | format information pattern                                                        |
| `M_FORMAT_DARK`      | convenience                                                                       |
| `M_VERSION`          | version information pattern                                                       |
| `M_VERSION_DARK`     | convenience                                                                       |
| `M_QUIETZONE`        | margin around the QR Code                                                         |
| `M_QUIETZONE_DARK`   | convenience                                                                       |
| `M_LOGO`             | space for a logo image (not used yet)                                             |
| `M_LOGO_DARK`        | convenience                                                                       |
| `M_TEST`             | test value                                                                        |
| `M_TEST_DARK`        | convenience                                                                       |
