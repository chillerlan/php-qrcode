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
