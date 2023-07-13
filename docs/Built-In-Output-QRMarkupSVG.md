# QRMarkupSVG

[Class `QRMarkupSVG`](https://github.com/chillerlan/php-qrcode/blob/main/src/Output/QRMarkupSVG.php): [Scalable Vector Graphics](https://developer.mozilla.org/en-US/docs/Glossary/SVG) (SVG) output

## Options that affect this module

| property                       | type           |
|--------------------------------|----------------|
| `$imageBase64`                 | `bool`         |
| `$eol`                         | `string`       |
| `$drawLightModules`            | `bool`         |
| `$drawCircularModules`         | `bool`         |
| `$circleRadius`                | `float`        |
| `$keepAsSquare`                | `array`        |
| `$connectPaths`                | `bool`         |
| `$excludeFromConnect`          | `array`        |
| `$cssClass`                    | `string`       |
| `$markupDark`                  | `string`       |
| `$markupLight`                 | `string`       |
| `$svgAddXmlHeader`             | `bool`         |
| `$svgOpacity`                  | `float`        |
| `$svgDefs`                     | `string`       |
| `$svgViewBoxSize`              | `int\|null`    |
| `$svgPreserveAspectRatio`      | `string`       |
| `$svgWidth`                    | `string\|null` |
| `$svgHeight`                   | `string\|null` |


### Options that have no effect

| property            | reason                                                                                                                                                                                                |
|---------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$returnResource`   | N/A                                                                                                                                                                                                   |
| `$bgColor`          | background color can be achieved via CSS, `<defs>` or attributes, see also [php-qrcode/discussions/199 (comment)](https://github.com/chillerlan/php-qrcode/discussions/199#discussioncomment-5747471) |
| `$scale`            | `$scale` is intended for raster image types, use `$svgViewBoxSize` instead                                                                                                                            |
| `$imageTransparent` | SVG is transparent by default                                                                                                                                                                         |
