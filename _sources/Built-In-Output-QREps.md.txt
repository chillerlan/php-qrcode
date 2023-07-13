# QREps

[Class `QREps`](https://github.com/chillerlan/php-qrcode/blob/main/src/Output/QREps.php): [Encapsulated Postscript](https://en.wikipedia.org/wiki/Encapsulated_PostScript) (EPS) output


## Options that affect this module

| property                       | type           |
|--------------------------------|----------------|
| `$drawLightModules`            | `bool`         |
| `$connectPaths`                | `bool`         |
| `$excludeFromConnect`          | `array`        |
| `$scale`                       | `int`          |


### Options that have no effect

| property               | reason          |
|------------------------|-----------------|
| `$returnResource`      | N/A             |
| `$imageBase64`         | N/A             |
| `$bgColor`             | not implemented |
| `$drawCircularModules` | not implemented |
| `$circleRadius`        | not implemented |
| `$keepAsSquare`        | not implemented |
| `$imageTransparent`    | N/A             |
