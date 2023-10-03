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
- [Reflectance](./reflectance.php): demonstrates reflectance reversal
- [QRCode reader](./reader.php): a simple reader example


## Advanced output examples

- [Custom output](./custom_output.php): a simple example that demonstrates the usage of custom output classes
- [GD Image with logo](./imageWithLogo.php): a logo on top of the QR Code
- [GD image with text](./imageWithText.php): description text under the QR Code ([#35](https://github.com/chillerlan/php-qrcode/issues/35))
- [GD Image with rounded modules](./imageWithRoundedShapes.php): similar to the SVG "melted" modules example ([#215](https://github.com/chillerlan/php-qrcode/pull/215))
- [ImageMagick with logo](./imagickWithLogo.php): a logo on top of the QR Code
- [ImageMagick with image as background](./imagickImageAsBackground.php): an image as full size background of the QR Code
- [SVG with logo](./svgWithLogo.php): an SVG QR Code with embedded logo (that is also SVG)
- [SVG with "melted" modules](./svgMeltedModules.php): an effect where the matrix appears to be like melted wax ([#127](https://github.com/chillerlan/php-qrcode/issues/127))
- [SVG with randomly colored modules](./svgRandomColoredDots.php): a visual effect using multiple colors for the matrix modules ([#136](https://github.com/chillerlan/php-qrcode/discussions/136))
- [SVG with a round shape and randomly filled quiet zone](./svgRoundQuietzone.php): example similar to the QR Codes of a certain vendor ([#137](https://github.com/chillerlan/php-qrcode/discussions/137))
- [SVG with logo, custom module shapes and custom finder patterns](./svgWithLogoAndCustomShapes.php): module- and finder pattern customization ([#150](https://github.com/chillerlan/php-qrcode/discussions/150))


## Other examples

- [Authenticator](./authenticator.php): create a QR Code that displays an URI for a mobile authenticator (featuring [`chillerlan/php-authenticator`](https://github.com/chillerlan/php-authenticator))
- [Interactive output](./qrcode-interactive.php): interactive demo (via [index.html](./index.html))
- [Custom module shapes](./shapes.svg): SVG paths to customize the module shapes ([#150](https://github.com/chillerlan/php-qrcode/discussions/150))
- [ImageMagick SVG to raster conversion](./imagickConvertSVGtoPNG.php): uses ImageMagick to convert SVG output to a raster image, e.g. PNG ([#216](https://github.com/chillerlan/php-qrcode/discussions/216))
- [HTML canvas SVG to PNG conversion](./svgConvertViaCanvas.php): converts an SVG element or a data URI fom and image element to a PNG image via the [HTML canvas element](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/canvas), includes [a javascript class](./SVGConvert.js) which handles the conversion.


Please note that the examples are self-contained, meaning that all custom classes are defined in an example file, so they don't necessarily respect the PSR-4 one file = one class principle.
