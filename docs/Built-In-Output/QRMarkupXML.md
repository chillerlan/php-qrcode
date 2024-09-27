# QRMarkupXML

[Class `QRMarkupXML`](https://github.com/chillerlan/php-qrcode/blob/main/src/Output/QRMarkupXML.php): [eXtensible Markup Language](https://developer.mozilla.org/en-US/docs/Glossary/XML) (XML) output


## Example

See: [XML example](https://github.com/chillerlan/php-qrcode/blob/main/examples/xml.php)

Set the options:

```php
$options = new QROptions;

$options->outputInterface  = QRMarkupXML::class;
$options->outputBase64     = false;
// if set to false, the light modules won't be included in the output
$options->drawLightModules = false;

// assign an XSLT stylesheet
$options->xmlStylesheet    = './qrcode.style.xsl';

$options->moduleValues     = [
	QRMatrix::M_FINDER_DARK    => '#A71111', // dark (true)
	QRMatrix::M_FINDER_DOT     => '#A71111', // finder dot, dark (true)
	QRMatrix::M_FINDER         => '#FFBFBF', // light (false)
	QRMatrix::M_ALIGNMENT_DARK => '#A70364',
	QRMatrix::M_ALIGNMENT      => '#FFC9C9',
	QRMatrix::M_VERSION_DARK   => '#650098',
	QRMatrix::M_VERSION        => '#E0B8FF',
];
```


The XSLT stylesheet `qrcode.style.xsl`:

```XSLT
<?xml version="1.0" encoding="UTF-8"?>
<!-- XSLT style for the XML output example -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<!-- SVG header -->
		<svg xmlns="http://www.w3.org/2000/svg"
		     version="1.0"
		     viewBox="0 0 {qrcode/matrix/@width} {qrcode/matrix/@height}"
		     preserveAspectRatio="xMidYMid"
		>
			<!--
				path for a single module
				we could define a path for each layer and use the @layer attribute for selection,
				but that would exaggerate this example
			-->
			<symbol id="module" width="1" height="1">
				<circle cx="0.5" cy="0.5" r="0.4" />
			</symbol>
			<!-- loop over the rows -->
			<xsl:for-each select="qrcode/matrix/row">
				<!-- set a variable for $y (vertical) -->
				<xsl:variable name="y" select="@y"/>
				<xsl:for-each select="module">
					<!-- set a variable for $x (horizontal) -->
					<xsl:variable name="x" select="@x"/>
					<!-- draw only dark modules -->
					<xsl:if test="@dark='true'">
						<!-- position the module and set its fill color -->
						<use href="#module" class="{@layer}" x="{$x}" y="{$y}" fill="{@value}"/>
					</xsl:if>
				</xsl:for-each>
			</xsl:for-each>
		</svg>
	</xsl:template>
</xsl:stylesheet>
```


Render the output:

```php
$data = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
$out  = (new QRCode($options))->render($data); // -> XML, rendered as SVG

header('Content-type: application/xml');

echo $out;
```

The associated [XML schema](https://www.w3.org/XML/Schema) can be found over at GitHub: [`qrcode.schema.xsd`](https://github.com/chillerlan/php-qrcode/blob/main/src/Output/qrcode.schema.xsd)


## Additional methods

| method                                            | return             | description                               |
|---------------------------------------------------|--------------------|-------------------------------------------|
| (protected) `createMatrix()`                      | `DOMElement`       | creates the matrix element                |
| (protected) `row(int $y, array $row)`             | `DOMElement\|null` | creates a DOM element for a matrix row    |
| (protected) `module(int $x, int $y, int $M_TYPE)` | `DOMElement\|null` | creates a DOM element for a single module |


## Options that affect this class

| property                  | type     |
|---------------------------|----------|
| `$drawLightModules`       | `bool`   |
| `$outputBase64`           | `bool`   |
| `xmlStylesheet`           | `string` |
