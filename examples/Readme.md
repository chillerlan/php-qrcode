# php-qrcode examples

## Simple examples that demonstrate the different output types

- [image](./image.php): Raster images via the [GD extension](https://www.php.net/manual/de/book.image.php)
- [ImageMagick](./imagick.php): Raster Images via [ImageMagick](https://imagemagick.org/)
- [SVG](./svg.php): [Scalable Vector Graphics](https://developer.mozilla.org/en-US/docs/Web/SVG)
- [HTML](./html.php): HTML markup
- [FPDF](./fpdf.php): PDF output via [FPDF](http://www.fpdf.org/)
- [EPS](./eps.php): Encapsulated PostScript
- [String](./text.php): String output
- [Multi mode](./multimode.php): a demostration of multi mode usage
- [QRCode reader](./reader.php): a simple reader example


## Advanced output examples

- [Custom output](./custom_output.php): a simple example that demonstrates the usage of custom output classes
- [GD Image with logo](./imageWithLogo.php): a logo on top of the QR Code
- [GD image with text](./imageWithText.php): description text under the QR Code ([#35](https://github.com/chillerlan/php-qrcode/issues/35))
- [ImageMagick with logo](./imagickWithLogo.php): a logo on top of the QR Code
- [SVG with logo](./svgWithLogo.php): an SVG QR Code with embedded logo (that is also SVG)
- [SVG with "melted" modules](./svgMeltedModules.php): an effect where the matrix appears to be like melted wax ([#127](https://github.com/chillerlan/php-qrcode/issues/127))
- [SVG with randomly colored modules](./svgRandomColoredDots.php): a visual effect using multiple colors for the matrix modules ([#136](https://github.com/chillerlan/php-qrcode/discussions/136))
- [SVG with a round shape and randomly filled quiet zone](./svgRoundQuietzone.php): example similar to the QR Codes of a certain vendor ([#137](https://github.com/chillerlan/php-qrcode/discussions/137))
- [SVG with logo, custom module shapes and custom finder patterns](./svgWithLogoAndCustomShapes.php): module- and finder pattern customization ([#150](https://github.com/chillerlan/php-qrcode/discussions/150))

## Other examples

- [Interactive output](./qrcode-interactive.php): interactive demo (via [index.html](./index.html))
- [Custom module shapes](./shapes.svg): SVG paths to customize the module shapes ([#150](https://github.com/chillerlan/php-qrcode/discussions/150))
