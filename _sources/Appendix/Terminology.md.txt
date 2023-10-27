# Terminology

## QR Code

A [*QR code*](https://en.wikipedia.org/wiki/QR_code) (quick-response code) is a type of two-dimensional matrix barcode, invented
in 1994 by Japanese company [Denso Wave](https://www.qrcode.com/en/faq.html#patentH2Title) for labelling automobile parts.
The QR labelling system was applied beyond the automobile industry due to its fast readability and greater storage capacity
compared to standard UPC barcodes.
QR Codes, more specifically, the popular *Model 2*, are internationally standardized in the ISO/IEC 18004.


## Matrix

A QR symbol is arranged in a *matrix* consisting of an array of nominally square modules arranged in an overall square pattern.

For ease of reference, module positions are defined by their row and column coordinates in the symbol, in the form `(x, y)`
where `x` designates the column (counting from left to right) and `y` the row (counting from the top downwards) in which
the module is located, with counting commencing at 0. Module `(0, 0)` is therefore located in the upper left corner of the symbol.


### Module

A *module* represents a single square "pixel" in the matrix (not to confuse with pixels in a raster image or screen).
A dark module represents a binary one and a light module represents a binary zero.


### Version

The *version* of a QR symbol determines the side length of the matrix (and therefore the maximum capacity of code words),
ranging from 21×21 modules (441 total) at version 1 to 177×177 modules (31329 total) at version 40.
The module count increases in steps of 4 and can be calculated by `4 * version + 17`.

The maximum capacity for each version, mode and ECC level can be found in [this table (qrcode.com)](https://www.qrcode.com/en/about/version.html).


## Function Patterns

### Finder Pattern

The *Finder Pattern* shall consist of three identical Position Detection Patterns located at the upper left, upper right
and lower left corners of the symbol.

Each Position Detection Pattern may be viewed as three superimposed concentric squares and is constructed of dark 7×7 modules,
light 5×5 modules and dark 3×3 modules.

The symbol is preferentially encoded so that similar patterns have a low probability of being encountered elsewhere in the symbol,
enabling rapid identification of a possible QR Code symbol in the field of view. Identification of the three Position Detection
Patterns comprising the finder pattern then unambiguously defines the location and orientation of the symbol in the field of view.

<p align="center">
	<img alt="Finder pattern" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQkAAAEJCAYAAACHaNJkAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAAE3ElEQVR42u3d200cSxRAUWM5I5MCMREDMZFCOyb85x/am0HV04+atT4RFzN1R1tHR9XD07IsHz8A/uPn0b8AcG4iASSRAJJIAEkkgCQSQBIJIIkEkEQCSCIBJJEAkkgASSSAJBJAEgkgiQSQRAJIIgEkkQDSr61/4O/n56Nf0z9/luXT197e3j597fX19ehflZO69f1y9vf9CJMEkEQCSCIBJJEA0uaLy1Xv7/f/N15ednkpzGvzpfYk73uTBJBEAkgiASSRANI+i8s1IwuXPRZCcA8XfN+bJIAkEkASCSCJBJCOW1zCBfhoAZME8AWRAJJIAEkkgPRwi8tHWzqtLd62dsUz3eNcZmGSAJJIAEkkgCQSQDpucXnQY69u0I293lkWfof9P7/gxxyYJIAkEkASCSCJBJD2WVxe8A/n3LrgPPv3bf16t/55s3zfqgu+79eYJIAkEkASCSCJBJCelmX5OPqX4H7OvuDk/EwSQBIJIIkEkEQCSJvfuLz1UeJbl10jjyZbqK2b5XHvsxu51Tli6/e9SQJIIgEkkQCSSADJjUsgmSSAJBJAEgkgiQSQpvmr4h5hhvswSQBJJIAkEkASCSC5cQkkkwSQRAJIIgEkkQCSG5dAMkkASSSAJBJAEgkguXEJJJMEkEQCSCIBJJEAkhuXQDJJAEkkgCQSQBIJILlxCSSTBJBEAkgiASSRAJIbl0AySQBJJIAkEkASCSC5cQkkkwSQRAJIIgEkkQCSG5dAMkkASSSAJBJAEgkguXEJJJMEkEQCSCIBJJEA0ulvXK7dpNzazDcznd8Y52eSAL4gEkASCSCJBJBOv7hcM7Lo2WMRdXbOb8yjnZ9JAkgiASSRAJJIAOmSi8s1PuNyjPMbM/P5mSSAJBJAEgkgiQSQpllczrIkOorzGzPz+ZkkgCQSQBIJIIkEkC65uLzi47Zn4vzGPNr5mSSAJBJAEgkgiQSQ/FVxIJkkgCQSQBIJIIkEkC5543LNzJ8xCEcySQBJJIAkEkASCSC5cQkkkwSQRAJIIgEkkQCSG5dAMkkASSSAJBJAEgkguXEJJJMEkEQCSCIBJJEAkhuXQDJJAEkkgCQSQBIJILlxCSSTBJBEAkgiASSRAJIbl0AySQBJJIAkEkASCSC5cQkkkwSQRAJIIgEkkQDS6W9c3nqT0o1LuA+TBJBEAkgiASSRANIui8u1peLWLCnhPkwSQBIJIIkEkEQCSJsvLkduPo4sON24XGdpPMb5mSSAL4gEkEQCSCIBpM0XlyNLmD3+WwvOsde7xyLv7B7t/EwSQBIJIIkEkEQCSJsvLn8/Px/9mv75syyfvvZoS8pbWeiOmfn8TBJAEgkgiQSQRAJI+/xxnvf3+/8bLy83fdvMC6YRzmDMzOdnkgCSSABJJIAkEkA67q+K37hoXLXHInRiV3xc+Uwe7fxMEkASCSCJBJBEAkhPy7J8bPkDVx8VX1s0br24XPl5a4+KA99jkgCSSABJJIAkEkASCSCJBJBEAkgiASSRANJxj4of9Li3z7iE7zFJAEkkgCQSQBIJIO2zuBx5LBw4lEkCSCIBJJEAkkgAafPPuATmYpIAkkgASSSAJBJAEgkgiQSQRAJIIgEkkQCSSABJJIAkEkASCSCJBJBEAkgiASSRAJJIAEkkgPQXNLQeTUTR7xsAAAAASUVORK5CYII=">
</p>


### Alignment Pattern

The *Alignment Pattern* is a fixed reference pattern in defined positions, which enables the decode software to
resynchronise the coordinate mapping of the modules in the event of moderate amounts of distortion of the image.

Each Alignment Pattern may be viewed as three superimposed concentric squares and is constructed of dark 5×5
modules, light 3×3 modules and a single central dark module.

The number of Alignment Patterns depends on the symbol version, and they shall be placed in all Model 2 symbols of
version 2 or larger in positions defined in the specification.

<p align="center">
	<img alt="Alignment Pattern" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQkAAAEJCAYAAACHaNJkAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAAE+0lEQVR42u3dwXHiWBRAUTw1GZkUiIkYiIkU5Jg8u9m0+oIt4EvonKWrbePf1K1Xrz7wMU3T9wHgL/4Z/QCAdRMJIIkEkEQCSCIBJJEAkkgASSSAJBJAEgkgiQSQRAJIIgEkkQCSSABJJIAkEkASCSCJBJD+ffQPvFwuo/+m/53P57se39y/g8Ph/ufL2p/3S5gkgCQSQBIJIIkEkB6+uJzzisXgmhZHbNOjl9rv8rw3SQBJJIAkEkASCSC9ZHE5Z8nCxQ1JtmqLz3uTBJBEAkgiASSRANKwxSVsgbcWMEkAN4gEkEQCSCIBpN0tLve2dPo8Hp/+O76mafSf+WPeWuB+JgkgiQSQRAJIIgGkYYvLUQtEN+gOh8P1+vvvPZ1GP/qHGPV/vsXnmkkCSCIBJJEAkkgA6SWLyy3eblvyadJr+nd3m1tILlhwrv1cXnHOW3zezzFJAEkkgCQSQBIJIH1M0/Q9+kHwPLMvFX/wjcstvlSc+5kkgCQSQBIJIIkEkB5+4/LeW2avuLW2xZflvsSbvNx77Zbc6lzi0c97kwSQRAJIIgEkkQCSG5dAMkkASSSAJBJAEgkgvc2nivvQHXgOkwSQRAJIIgEkkQCSG5dAMkkASSSAJBJAEgkguXEJJJMEkEQCSCIBJJEAkhuXQDJJAEkkgCQSQBIJILlxCSSTBJBEAkgiASSRAJIbl0AySQBJJIAkEkASCSC5cQkkkwSQRAJIIgEkkQCSG5dAMkkASSSAJBJAEgkguXEJJJMEkEQCSCIBJJEAkhuXQDJJAEkkgCQSQBIJIK3+xuXn8fj03/E1TaP/zKdxfss4P5MEcINIAEkkgCQSQFr94nLW9fr77z2dRj/68ZzfMjs7P5MEkEQCSCIBJJEA0jYXl3PmFkJLFkx74/yWeePzM0kASSSAJBJAEgkgvc/i8k2WRMM4v2Xe+PxMEkASCSCJBJBEAkjbXFxu8OW2q+L8ltnZ+ZkkgCQSQBIJIIkEkHyqOJBMEkASCSCJBJBEAkjbvHE543K5/PG18/k8+mHB5pkkgCQSQBIJIIkEkNy4BJJJAkgiASSRAJJIAMmNSyCZJIAkEkASCSCJBJDcuASSSQJIIgEkkQCSSADJjUsgmSSAJBJAEgkgiQSQ3LgEkkkCSCIBJJEAkkgAyY1LIJkkgCQSQBIJIIkEkNy4BJJJAkgiASSRAJJIAGn1Ny7vvUnpxiU8h0kCSCIBJJEAkkgA6SWLy7ml4qNZUsJzmCSAJBJAEgkgiQSQHr64XHLzccmC043LeZ/H49N/x9c0jf4zn8b5mSSAG0QCSCIBJJEA0u7e43JvC87Zxdv1+vsfeDr98aW1L96WcH4mCeAGkQCSSABJJID0khuXo8wtJN95SbnIzEJt0YJub974/EwSQBIJIIkEkEQCSC95j8tXLAvvXZju7cbl3d5kyTbMG5+fSQJIIgEkkQCSSABp2KeKL7mZadG40NztQO63s/MzSQBJJIAkEkASCSANW1yOsrel59rfP3HtnJ9JArhBJIAkEkASCSCJBJBEAkgiASSRAJJIAGnYjctRNx+9xyX8jEkCSCIBJJEAkkgA6SWLyzV90jjwMyYJIIkEkEQCSCIBpI9pmr5HPwhgvUwSQBIJIIkEkEQCSCIBJJEAkkgASSSAJBJAEgkgiQSQRAJIIgEkkQCSSABJJIAkEkASCSCJBJD+AxtDFTzroADgAAAAAElFTkSuQmCC">
</p>


### Timing Pattern

The horizontal and vertical Timing Patterns respectively consist of a one module wide row or column of alternating
dark and light modules, commencing and ending with a dark module. The horizontal Timing Pattern runs across
row 6 of the symbol between the separators for the upper Position Detection Patterns; the vertical Timing Pattern
similarly runs down column 6 of the symbol between the separators for the left-hand Position Detection Patterns.
They enable the symbol density and version to be determined and provide datum positions for determining module
coordinates.

<p align="center">
	<img alt="Timing Pattern" src="data:image/png;base64,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">
</p>


### Separators

A pattern of all light modules, one module wide, separating the Position Detection Patterns from the rest of the symbol.

<p align="center">
	<img alt="Separators" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQkAAAEJCAYAAACHaNJkAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAAFCklEQVR42u3dy1EkRxRAUaGQA2OLfMEEXMAGXGgTxpaSLZiAdtpQutRMFvU9Z0kghk5V3HjxIrt5mqbp4w+A//Hn3r8AcGwiASSRAJJIAEkkgCQSQBIJIIkEkEQCSCIBJJEAkkgASSSAJBJAEgkgiQSQRAJIIgEkkQDSX2v/wLe3t71f039+vrx8+trz4/Hpa6+vr3v/qhzU3PO89Lnay9rPs0kCSCIBJJEAkkgAafXF5ZwtFoNHWphyTnPP0Oyz+/6+6Odd5bk3SQBJJIAkEkASCSBtsricM7JwcUOSszrjc2+SAJJIAEkkgCQSQNptcQlnsPSt4ldmkgCSSABJJIAkEkC63eLybrc1t3gr8RnP1EcLLGeSAJJIAEkkgCQSQNptcbnXsmvx5xhe2MjrvcrCb+j/+cLPuFz9392JSQJIIgEkkQCSSABpk8XlGZddSxecR/++tV/v2j/vjN+39K3iZ3zu55gkgCQSQBIJIIkEkJ6mafpY8wf+PXAbbQv//Pix96+wqaMvOM/obs+4SQJIIgEkkQCSSABp9RuXz4/Hou9buuw6419hPrqr3ATcy9LF4MitzhFrP/cmCSCJBJBEAkgiAaTVb1wC12KSAJJIAEkkgCQSQLrMXxX3Fmb4HiYJIIkEkEQCSCIBJDcugWSSAJJIAEkkgCQSQHLjEkgmCSCJBJBEAkgiASQ3LoFkkgCSSABJJIAkEkBy4xJIJgkgiQSQRAJIIgEkNy6BZJIAkkgASSSAJBJAcuMSSCYJIIkEkEQCSCIBJDcugWSSAJJIAEkkgCQSQHLjEkgmCSCJBJBEAkgiASQ3LoFkkgCSSABJJIAkEkA6/I3LuZuUa7vyzUznN8b5mSSAL4gEkEQCSCIBpMMvLueMLHq2WEQdnfMbc7fzM0kASSSAJBJAEgkgnXJxOcdnXI5xfmOufH4mCSCJBJBEAkgiAaTLLC6vsiTai/Mbc+XzM0kASSSAJBJAEgkgnXJxeca32x6J8xtzt/MzSQBJJIAkEkASCSD5q+JAMkkASSSAJBJAEgkgnfLG5Zwrf8Yg7MkkASSRAJJIAEkkgOTGJZBMEkASCSCJBJBEAkhuXALJJAEkkQCSSABJJIDkxiWQTBJAEgkgiQSQRAJIblwCySQBJJEAkkgASSSA5MYlkEwSQBIJIIkEkEQCSG5cAskkASSRAJJIAEkkgOTGJZBMEkASCSCJBJBEAkiHv3G59CalG5fwPUwSQBIJIIkEkEQCSJssLueWimuzpITvYZIAkkgASSSAJBJAWn1xOXLzcWTB6cblPEvjMc7PJAF8QSSAJBJAEgkgrb64/Pny8vmL7++//9/Omfl5S//d58fj09eOvjha28jr3WKRd3R3Oz+TBJBEAkgiASSRANLqi8u5xeBe5paZd1tSLuXG6pgrn59JAkgiASSRAJJIAGmTz7jcYoGz9CbblRdMI5zBmCufn0kCSCIBJJEAkkgAabe/Kj7yltkrL4m2cMa3Kx/J3c7PJAEkkQCSSABJJIC02+JyL3dbet7t9a7N+ZkkgC+IBJBEAkgiASSRAJJIAEkkgCQSQBIJIO1243Kvm2w+4xJ+jUkCSCIBJJEAkkgAaZPF5d0+ExCuxCQBJJEAkkgASSSA9DRN08fevwRwXCYJIIkEkEQCSCIBJJEAkkgASSSAJBJAEgkgiQSQRAJIIgEkkQCSSABJJIAkEkASCSCJBJBEAkj/AmllMZTO3DMoAAAAAElFTkSuQmCC">
</p>


### Quiet Zone

This is a region 4 modules wide which shall be free of all other markings, surrounding the symbol on all four sides.
Its nominal reflectance value shall be equal to that of the light modules.

<p align="center">
	<img alt="Quiet Zone" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQkAAAEJCAYAAACHaNJkAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAAE5klEQVR42u3d0W0bRxRAUSlIA64lvaQEtaAa1IJLcAusIbW4BPovCJDNFYNZze4Oz/k0aEIcEReDhyfy9X673V8A/sNvR/8AwLmJBJBEAkgiASSRAJJIAEkkgCQSQBIJIIkEkEQCSCIBJJEAkkgASSSAJBJAEgkgiQSQRAJIv+/9hH9+/370a/rb+/v7v/7t4+PjocfBy8vj75etxx3lx9vbrs/nJgEkkQCSSABJJIC0++Byy4zB4JkGR1zT3kPtVd73bhJAEgkgiQSQRAJIUwaXW0YGLjYkuaorvu/dJIAkEkASCSCJBJAOG1zCFfhoATcJ4BMiASSRAJJIAOnpBpfPNnSa8afEVzxTHy3wODcJIIkEkEQCSCIBpMMGl0cNu2zQjb3eVQZ+R/3Or/hec5MAkkgASSSAJBJAmjK4vOKwa+TbpM/0uL1f797Pt8rjHj2DK3KTAJJIAEkkgCQSQHq93273PZ/wr2/fjn5N/MPZB5zs74+fP3d9PjcJIIkEkEQCSCIBpN03Lh/dMpuxtWagtm2VTcCzG9nqHPHj7W3X53OTAJJIAEkkgCQSQLJxCYuxcQlMJRJAEgkgiQSQlvlWcX/CDF/DTQJIIgEkkQCSSADJxiUsxsYlMJVIAEkkgCQSQLJxCSQ3CSCJBJBEAkgiASQbl7AYG5fAVCIBJJEAkkgAycYlkNwkgCQSQBIJIIkEkGxcwmJsXAJTiQSQRAJIIgEkG5dAcpMAkkgASSSAJBJAsnEJi7FxCUwlEkASCSCJBJBsXALJTQJIIgEkkQCSSADJxiUsxsYlMJVIAEkkgCQSQDr9xuXWJuXeVt7MdH5jnJ+bBPAJkQCSSABJJIB0+sHllpFBz4xB1Nk5vzHPdn5uEkASCSCJBJBEAkiXHFxu8RmXY5zfmJXPz00CSCIBJJEAkkgAaZnB5SpDoqM4vzErn5+bBJBEAkgiASSRANIlB5dX/HPbM3F+Y57t/NwkgCQSQBIJIIkEkHyrOCzGt4oDU4kEkEQCSCIBpEtuXG5Z+TMG4UhuEkASCSCJBJBEAkg2LmExNi6BqUQCSCIBJJEAko1LILlJAEkkgCQSQBIJINm4hMXYuASmEgkgiQSQRAJINi6B5CYBJJEAkkgASSSAZOMSFmPjEphKJIAkEkASCSDZuASSmwSQRAJIIgEkkQCSjUtYjI1LYCqRAJJIAEkkgHT6jctHNyltXMLXcJMAkkgASSSAJBJAmjK43Boq7s2QEr6GmwSQRAJIIgEkkQDS7oPLkc3HkQGnjctthsZjnJ+bBPAJkQCSSABJJIC0++ByZAgz4/8acI693hmDvLN7tvNzkwCSSABJJIAkEkCasnF5lK0B07MNKR9loDtm5fNzkwCSSABJJIAkEkCa8hmXMwY4jw5MVx4wjXAGY1Y+PzcJIIkEkEQCSCIBpMO+VXxkM3PlIdEMZ9qKvaJnOz83CSCJBJBEAkgiAaTDBpdHebah57O93r05PzcJ4BMiASSRAJJIAEkkgCQSQBIJIIkEkEQCSIdtXB61yeYzLuH/cZMAkkgASSSAJBJAmjK4fLbPBISVuEkASSSAJBJAEgkgvd5vt/vRPwRwXm4SQBIJIIkEkEQCSCIBJJEAkkgASSSAJBJAEgkgiQSQRAJIIgEkkQCSSABJJIAkEkASCSCJBJB+AQVjRhvSjnmvAAAAAElFTkSuQmCC">
</p>


## Encoding Region

This region shall contain the symbol characters representing data, those representing error correction codewords,
the Version Information and Format Information.


### Data

This region contains the encoded data and error correction code blocks. Data bits are placed starting at the bottom-right of
the matrix and proceeding upward in a column that is 2 modules wide. When the column reaches the top, the next 2-module column
starts immediately to the left of the previous column and continues downward. Whenever the current column reaches the edge of
the matrix, move on to the next 2-module column and change direction. If a function pattern or reserved area is encountered,
the data bit is placed in the next unused module.
(see [wikipedia QR code - Encoding](https://en.wikipedia.org/wiki/QR_code#Encoding) and [thonky.com - QR Code Tutorial](https://www.thonky.com/qr-code-tutorial/module-placement-matrix#step-6-place-the-data-bits))

<p align="center">
	<img alt="Data" src="data:image/png;base64,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">
</p>


### Version Information

The Version Information is an 18 bit sequence containing 6 data bits, with 12 error correction bits calculated using the (18, 6)
[BCH code](https://en.wikipedia.org/wiki/BCH_code) which contains the version number.

<p align="center">
	<img alt="Version Information" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQkAAAEJCAYAAACHaNJkAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAAE90lEQVR42u3d200jWRRA0WY0GXUMpNCpEAMpkAopVMdCCMzf/LhmU9O3qOdanxZY+MraOjq6Nk/TNH3+APgPf+39BwDHJhJAEgkgiQSQRAJIIgEkkQCSSABJJIAkEkASCSCJBJBEAkgiASSRAJJIAEkkgCQSQBIJIP299hO+vr7u/Zr+9fLysujvm/s5ru3nx8eyH3x+fnjo9zQ9PHb09/0IkwSQRAJIIgEkkQDS6ovLOVssBo+0OOKkZpaUP97f//jprvK+N0kASSSAJBJAEgkgbbK4nDOycHFDkrM64/veJAEkkQCSSABJJIC02+ISTmHuFubMR8WvzCQBJJEAkkgASSSAdLvF5d1ua27xUeJTnuncQpJZJgkgiQSQRAJIIgGk3RaXey27/HOesdd7me8SHfjuyhFnfK+ZJIAkEkASCSCJBJA2WVyecdm1dMF59J9b+/Wu/Xx7/dyvt7dVn2/pGZyRSQJIIgEkkQCSSADpaZqmz73/CL7P0RecHJ9JAkgiASSRAJJIAGn1G5dLb5ltcWvNQm3eVW4CHt3ILdERa7/vTRJAEgkgiQSQRAJIblwCySQBJJEAkkgASSSAdJn/Ku4jzPA9TBJAEgkgiQSQRAJIblwCySQBJJEAkkgASSSA5MYlkEwSQBIJIIkEkEQCSG5cAskkASSRAJJIAEkkgOTGJZBMEkASCSCJBJBEAkhuXALJJAEkkQCSSABJJIDkxiWQTBJAEgkgiQSQRAJIblwCySQBJJEAkkgASSSA5MYlkEwSQBIJIIkEkEQCSG5cAskkASSRAJJIAEkkgHT4G5dzNynXduWbmc5vjPMzSQBfEAkgiQSQRAJIh19czhlZ9GyxiDo65zfmbudnkgCSSABJJIAkEkA65eJyju+4HOP8xlz5/EwSQBIJIIkEkEQCSJdZXF5lSbQX5zfmyudnkgCSSABJJIAkEkA65eLyjB+3PRLnN+Zu52eSAJJIAEkkgCQSQPJfxYFkkgCSSABJJIAkEkA65Y3LOVf+jkHYk0kCSCIBJJEAkkgAyY1LIJkkgCQSQBIJIIkEkNy4BJJJAkgiASSRAJJIAMmNSyCZJIAkEkASCSCJBJDcuASSSQJIIgEkkQCSSADJjUsgmSSAJBJAEgkgiQSQ3LgEkkkCSCIBJJEAkkgAyY1LIJkkgCQSQBIJIIkEkFa/cfnz42PdJ3x+fnzs/f3hoV9vbw+PuXEJ40wSQBIJIIkEkEQCSNt8VHxu+bgyS0r4HiYJIIkEkEQCSCIBpPUXlwtvSC7+3YV8x+W8uXNZ25XP2fmZJIAviASQRAJIIgGk1ReXv6fpz3954HeXLn8sOMde7xaLvKO72/mZJIAkEkASCSCJBJBWX1weaTEzt2C625JyKQvdMVc+P5MEkEQCSCIBJJEA0ibfcbnFAmfpwvTKC6YRzmDMlc/PJAEkkQCSSABJJIC0zT/nmTFyM/PKS6ItHOlW7Bnd7fxMEkASCSCJBJBEAki7LS73crel591e79qcn0kC+IJIAEkkgCQSQBIJIIkEkEQCSCIBJJEA0m43Lve6yeY7LuH/MUkASSSAJBJAEgkgbbK4vNt3AsKVmCSAJBJAEgkgiQSQnqZp+tz7jwCOyyQBJJEAkkgASSSAJBJAEgkgiQSQRAJIIgEkkQCSSABJJIAkEkASCSCJBJBEAkgiASSRAJJIAOkfq5AfPjhiBy8AAAAASUVORK5CYII=">
</p>


### Format Information

The Format Information is a 15 bit sequence containing 5 data bits, with 10 error correction bits calculated using the (15, 5) BCH code.
It contains information on the error correction level applied to the symbol and on the masking pattern used,
essential to enable the remainder of the encoding region to be decoded.

<p align="center">
	<img alt="Format Information" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQkAAAEJCAYAAACHaNJkAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAAFGElEQVR42u3d0U3cWBSA4WWVBlILtEAJ2RJogRrSSlqgBVNLSmDf9mW8P45sfG3P9z2OSDRcWb+Oji4zD9M0ffwF8D/+Hv0GgGMTCSCJBJBEAkgiASSRAJJIAEkkgCQSQBIJIIkEkEQCSCIBJJEAkkgASSSAJBJAEgkgiQSQvm39H/78+XP07/Sf19fXm9cen55uXnufptFvlYOae57nnqujP/drmCSAJBJAEgkgiQSQNl9cztl6kTLnSIsjzmnpknKpqzz3JgkgiQSQRAJIIgGkXRaXc9YsXPZYCMFXOONzb5IAkkgASSSAJBJAGra4hDPY+hbmGZkkgCQSQBIJIIkEkO5vcfn2Nvod7GqPPyU+4yLPRwssZ5IAkkgASSSAJBJAGra4HLbsen6+fe3OvpxnzdlfZeE36vk745LXJAEkkQCSSABJJIC0y+LySMuuf378uHltbpW05tukj/RzS436/67yc0vP4IxMEkASCSCJBJBEAkgP0zR9jH4Te3r8/fvmtffv30e/rS9z9AUnx2eSAJJIAEkkgCQSQNr8xuXj09OyH1z6WZNzf9o9Y+4m5a+Xl61/vUu4yk3AUeaW37Pmnt25537hM77U+8YffWCSAJJIAEkkgCQSQLq7G5fAnzFJAEkkgCQSQBIJIF3mW8X9CTN8DZMEkEQCSCIBJJEAkhuXQDJJAEkkgCQSQBIJILlxCSSTBJBEAkgiASSRAJIbl0AySQBJJIAkEkASCSC5cQkkkwSQRAJIIgEkkQCSG5dAMkkASSSAJBJAEgkguXEJJJMEkEQCSCIBJJEAkhuXQDJJAEkkgCQSQBIJILlxCSSTBJBEAkgiASSRAJIbl0AySQBJJIAkEkASCSAd/sbl3E3KrV35ZqbzW8f5mSSAT4gEkEQCSCIBpMMvLuesWfTssYg6Oue3zr2dn0kCSCIBJJEAkkgA6ZSLyzk+43Id57fOlc/PJAEkkQCSSABJJIB0mcXlVZZEozi/da58fiYJIIkEkEQCSCIBpFMuLs/457ZH4vzWubfzM0kASSSAJBJAEgkg+VZxIJkkgCQSQBIJIIkEkE5543LOlT9jEEYySQBJJIAkEkASCSC5cQkkkwSQRAJIIgEkkQCSG5dAMkkASSSAJBJAEgkguXEJJJMEkEQCSCIBJJEAkhuXQDJJAEkkgCQSQBIJILlxCSSTBJBEAkgiASSRAJIbl0AySQBJJIAkEkASCSC5cQkkkwSQRAJIIgEkkQDS4W9cLr1J6cYlfA2TBJBEAkgiASSRANIui8u5peLWLCnha5gkgCQSQBIJIIkEkDZfXK65+bhmwenG5TxL43Wcn0kC+IRIAEkkgCQSQNp8cblmCbPHv7XgXPf77rHIO7p7Oz+TBJBEAkgiASSRANIuNy5HmVsw/Xp5uXntffQbPQAL3XWufH4mCSCJBJBEAkgiAaRdPuNyjwXO4oXp8/Pta9O0xzEc2lWWbKNc+fxMEkASCSCJBJBEAkjDvlV8zc3MKy+J9nCkW7FndG/nZ5IAkkgASSSAJBJAGra4HObtbfQ72JUl7zrOzyQBfEIkgCQSQBIJIIkEkEQCSCIBJJEAkkgAadiNy2E32XzGJfwRkwSQRAJIIgEkkQDSLovLe/tMQLgSkwSQRAJIIgEkkQDSwzRNH6PfBHBcJgkgiQSQRAJIIgEkkQCSSABJJIAkEkASCSCJBJBEAkgiASSRAJJIAEkkgCQSQBIJIIkEkEQCSP8CTwEkCpt+cz0AAAAASUVORK5CYII=">
</p>


### Darkmodule

The module in position `(4 * version + 9, 8)` shall always be dark and does not form part of the Format Information.

<p align="center">
	<img alt="Darkmodule" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQkAAAEJCAYAAACHaNJkAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAAEuklEQVR42u3dS27UUBBA0QSxI9hb1pC9mTWFGRPM7UbP7c/zOUMUUGJaV6VSpft9WZavN4B/+Hb0NwCcm0gASSSAJBJAEgkgiQSQRAJIIgEkkQCSSABJJIAkEkASCSCJBJBEAkgiASSRAJJIAEkkgPR963/w8/Pz6J/pj4+Pj6e+v7Wvg7e3518vZ3/djzBJAEkkgCQSQBIJIG2+uFyzx2LwTIsjrmnrpfYsr3uTBJBEAkgiASSRANIui8s1IwsXF5Jc1RVf9yYJIIkEkEQCSCIBpMMWl3AF3lrAJAE8IBJAEgkgiQSQbre4vNvSaY9fJb7iM/XWAs8zSQBJJIAkEkASCSAdtrg8atnlgm7s551l4XfU//kVX2smCSCJBJBEAkgiAaRdFpdXXHaNfJr0mb5u6593639vlq979hlckUkCSCIBJJEAkkgA6X1Zlq+jvwle5+wLTs7PJAEkkQCSSABJJIC0+cXls1dme1ytWaitm+US8OxGrjpHbP26N0kASSSAJBJAEgkgubgEkkkCSCIBJJEAkkgAaZpPFfcrzPAaJgkgiQSQRAJIIgEkF5dAMkkASSSAJBJAEgkgubgEkkkCSCIBJJEAkkgAycUlkEwSQBIJIIkEkEQCSC4ugWSSAJJIAEkkgCQSQHJxCSSTBJBEAkgiASSRAJKLSyCZJIAkEkASCSCJBJBcXALJJAEkkQCSSABJJIDk4hJIJgkgiQSQRAJIIgEkF5dAMkkASSSAJBJAEgkgnf7icu2ScmszX2Z6fmM8P5ME8IBIAEkkgCQSQDr94nLNyKJnj0XU2Xl+Y+72/EwSQBIJIIkEkEQCSJdcXK7xHpdjPL8xMz8/kwSQRAJIIgEkkQDSNIvLWZZER/H8xsz8/EwSQBIJIIkEkEQCSJdcXF7x123PxPMbc7fnZ5IAkkgASSSAJBJA8qniQDJJAEkkgCQSQBIJIF3y4nLNzO8xCEcySQBJJIAkEkASCSC5uASSSQJIIgEkkQCSSADJxSWQTBJAEgkgiQSQRAJILi6BZJIAkkgASSSAJBJAcnEJJJMEkEQCSCIBJJEAkotLIJkkgCQSQBIJIIkEkFxcAskkASSRAJJIAEkkgOTiEkgmCSCJBJBEAkgiAaTTX1w+e0np4hJewyQBJJEAkkgASSSAtMvicm2puDVLSngNkwSQRAJIIgEkkQDS5ovLkcvHkQWni8t1lsZjPD+TBPCASABJJIAkEkDafHE5soTZ4+/++Pnzrz/7tSxbP4ZTG3nOeyzyzu5uz88kASSRAJJIAEkkgLTLxeVR1hZMd1tSPsvF6piZn59JAkgiASSRAJJIAGmX97jcY4Hz7MJ05gXTCM9gzMzPzyQBJJEAkkgASSSAdNinio9cZs68JNrDma5ir+huz88kASSRAJJIAEkkgHTY4vIod1t63u3n3ZrnZ5IAHhAJIIkEkEQCSCIBJJEAkkgASSSAJBJAOuzi8qhLNu9xCf/HJAEkkQCSSABJJIC0y+Lybu8JCDMxSQBJJIAkEkASCSC9L8vydfQ3AZyXSQJIIgEkkQCSSABJJIAkEkASCSCJBJBEAkgiASSRAJJIAEkkgCQSQBIJIIkEkEQCSCIBJJEA0m+uYiIceAR3NQAAAABJRU5ErkJggg==">
</p>


## Mode

The *mode* is the method of representing a defined character set as a bit string, with a *mode indicator*, a four-bit identifier indicating in which mode the next data sequence is encoded.

| Mode                    | Indicator | Description                                                                                                                                                              |
|-------------------------|-----------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Numeric                 | `0001`    | Numeric encoding, 10 bits per 3 digits                                                                                                                                   |
| Alphanumeric            | `0010`    | Alphanumeric encoding, 11 bits per 2 characters                                                                                                                          |
| Byte                    | `0100`    | Byte encoding, 8 bits per character                                                                                                                                      |
| Kanji                   | `1000`    | [Kanji](https://en.wikipedia.org/wiki/Kanji) encoding (Japanese, [Shift-JIS](https://en.wikipedia.org/wiki/Shift_JIS)), 13 bits per character                            |
| Hanzi<sup>*</sup>       | `1101`    | [Hanzi](https://en.wikipedia.org/wiki/Chinese_characters) encoding (simplified Chinese, [GB2312/GB18030](https://en.wikipedia.org/wiki/GB_18030)), 13 bits per character |
| Structured append       | `0011`    | used to split a message across multiple (up to 16) QR symbols                                                                                                            |
| ECI                     | `0111`    | [Extended Channel Interpretation](https://en.wikipedia.org/wiki/Extended_Channel_Interpretation) (select alternate character set or encoding)                            |
| FNC1 in first position  | `0101`    | see [Code 128](https://en.wikipedia.org/wiki/Code_128), also [zxing/issues/1373](https://github.com/zxing/zxing/issues/1373)                                             |
| FNC1 in second position | `1001`    |                                                                                                                                                                          |
| Terminator              | `0000`    | End of message                                                                                                                                                           |

<sup>*</sup> Hanzi mode is not part of the ISO specification, but the Chinese standard [GB/T 18284](https://www.chinesestandard.net/PDF/English.aspx/GBT18284-2000)


### Segment

Each segment consists of the 4 bit mode indicator followed by the data bit stream, where the content of the bit stream can vary depending on the mode:

| Mode                    | Bit stream contents                                                                                                       |
|-------------------------|---------------------------------------------------------------------------------------------------------------------------|
| Numeric                 | \[ `0001` : 4 ] \[ Character Count Indicator : variable ] \[ Data Bit Stream : 3 1⁄3 × charcount ]                        |
| Alphanumeric            | \[ `0010` : 4 ] \[ Character Count Indicator : variable ] \[ Data Bit Stream : 5 1⁄2 × charcount ]                        |
| Byte                    | \[ `0100` : 4 ] \[ Character Count Indicator : variable ] \[ Data Bit Stream : 8 × charcount ]                            |
| Kanji                   | \[ `1000` : 4 ] \[ Character Count Indicator : variable ] \[ Data Bit Stream : 13 × charcount ]                           |
| Hanzi                   | \[ `1101` : 4 ] \[ Subset Indicator : 4 ] \[ Character Count Indicator : variable ] \[ Data Bit Stream : 13 × charcount ] |
| Structured append       | \[ `0011` : 4 ] \[ Symbol Position : 4 ] \[ Total Symbols : 4 ] \[ Parity : 8 ]                                           |
| ECI                     | \[ `0111` : 4 ] \[ ECI Assignment number : variable ]                                                                     |
| FNC1 in first position  | \[ `0101` : 4 ] \[ Numeric/Alphanumeric/Byte/Kanji/Hanzi payload : variable ]                                             |
| FNC1 in second position | \[ `1001` : 4 ] \[ Application Indicator : 8 ] \[ Numeric/Alphanumeric/Byte/Kanji/Hanzi payload : variable ]              |
| Terminator              | \[ `0000` : 4 ]                                                                                                           |

The lenght of the Character Count Indicator for Numeric/Alphanumeric/Byte/Kanji/Hanzi varies, depending on the version:

| Mode         | Version 1-9 | Version 10-26 | Version 27-40 |
|--------------|-------------|---------------|---------------|
| Numeric      | 10          | 12            | 14            |
| Alphanumeric | 9           | 11            | 13            |
| Byte         | 8           | 16            | 16            |
| Kanji/Hanzi  | 8           | 10            | 12            |


### Extended Channel Interpretation (ECI)

[Extended Channel Interpretation](https://en.wikipedia.org/wiki/Extended_Channel_Interpretation) can be used to indicate an
alternate character encoding for the following Byte segment (by default, ISO-8859-1 "Latin-1").

An ECI segment starts with the 4 bit indicator `0111` followed by the ECI Assignment number (8, 16 or 24 bits),
followed by a Byte segment (`0100` ...) where the contents are encoded according to the preceding ECI ID.

The length of the ECI Assignment number depends on the given encoding ID:

| ID             | length (bits) |
|----------------|---------------|
| 0 - 127        | 8             |
| 128 - 16383    | 16            |
| 16384 - 999999 | 24            |


### Mixed Mode

Encoding modes can be mixed as needed within a QR symbol in order to optimize data usage.
Each segment of data is encoded in the appropriate mode, with the basic structure
*Mode Indicator / Character Count Indicator / Data* and followed immediately by the Mode Indicator commencing the next segment.

\[ Mode Indicator 1 ]\[ Mode bitstream 1 ]<br>
...<br>
\[ Mode Indicator n ]\[ Mode bitstream n ]<br>
...<br>
\[ `0000` End of message (Terminator) ]


## ECC (Error Correction Coding)

QR codes use [Reed–Solomon error correction](https://en.wikipedia.org/wiki/Reed%E2%80%93Solomon_error_correction) that allow QR code readers to detect and correct errors.
A detailed breakdown of the process can be found at [thonky.com - QR Code Tutorial](https://www.thonky.com/qr-code-tutorial/error-correction-coding).


### ECC Level

The number of data versus error correction bytes within each block depends on the version of the QR symbol and the error
correction level. The higher the error correction level, the less storage capacity. The following table lists the approximate
error correction capability at each of the four levels:

| Level    | Short | Capacity | Indicator |
|----------|-------|----------|-----------|
| Low      | L     | 7%       | `01`      |
| Medium   | M     | 15%      | `00`      |
| Quartile | Q     | 25%      | `11`      |
| High     | H     | 30%      | `10`      |


### Maximum data capacity

The maximum data capacity of a QR Code at version 40 for each ECC level and mode is shown in the following table:

| ECC | max. bits | Numeric | Alphanumeric | Binary | Kanji/Hanzi <sup>*</sup> |
|-----|-----------|---------|--------------|--------|--------------------------|
| L   | 23648     | 7089    | 4296         | 2953   | 1817                     |
| M   | 18672     | 5596    | 3391         | 2331   | 1435                     |
| Q   | 13328     | 3993    | 2420         | 1663   | 1024                     |
| H   | 10208     | 3057    | 1852         | 1273   | 784                      |

<sup>*</sup> Hanzi mode stores one character less than Kanji as it uses an additional subset indicator of 4 bits length.


## Data masking

Masking is the process of XORing the bit pattern in the encoding region with a masking pattern to provide a symbol with more
evenly balanced numbers of dark and light modules and reduced occurrence of patterns which would interfere with fast processing of the image.


### Evaluation

The mask pattern evaluation is done for each of the 8 mask patterns, the pattern with the lowest penalty score shall be used for the final output.
During the evaluation, 4 rules are applied to get the penalty score:

- find repetitive cells with the same color Example: 00000 or 11111 (horizontal and vertical).
- find 2×2 blocks with the same color
- find consecutive runs of 1:1:3:1:1:4 starting with black, or 4:1:1:3:1:1 starting with white
- calculate the ratio of dark cells and give increasing penalty if the ratio is far from 50%

### Mask pattern

| Pattern | Mask<sup>*</sup>                                                                      | Example                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           |
|---------|---------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `000`   | `(x + y) mod 2 = 0`                                                                   | <img alt="Mask pattern 000" style="margin: 0.25em;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGkAAABpCAYAAAA5gg06AAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAACMklEQVR42u2d0W3CMBBAk4oFmAVWYAQ6AitkBlZgBBghK4RZGIH+9cdWe5Ij5x5679M6VOqnky+HnBvP5/N7SMI0TaG4w+tVLp5O5do8h+Key1KsXa/Xrbfjl6+tv4D8j5IAKAmAkgDsaovRA7yF6MFci7s/HmVgsEioxgXZal/MJABKAqAkAEoCsIsGtjyBtxy41c9eLuVaQ8dhqHQcMu2LmQRASQCUBEBJAMKFQypaioSGjsNWmEkAlARASQCUBCB94VD9qaKlSFi549ADMwmAkgAoCYCSAIQLhx6/74f/7vFYrm3UceixL2YSACUBUBIAJQGoFg6ZbhTUvssUvAURjgse/lvti5kEQEkAlARASQDGZVnSXMeMcli54/Dc77f+l/7ETAKgJABKAqAkAGPtPQ61J/Dok/racTV6vMchytodjNpnzSQASgKgJABKAjC+57nsOKx9ayF4WK/dSfiUODMJgJIAKAmAkgCM72EoC4cOh+H37Vas3dd+P8OHxJlJAJQEQEkAlATAjgMgzkwCoCQASgKgJAB2HABxZhIAJQFQEgAlAUh/q6LLrIrkcWYSACUBUBIAJQEYnY6ZP85MAqAkAEoCoCQA6adjVkl0qNtxkGEYlIRASQCUBCD9dMwusyqSx5lJAJQEQEkAlAQg/ZCrLrMqkseZSQCUBEBJAJQEIH3hYMfBTEKgJABKAqAkAE7HBMSZSQCUBEBJAJQEADkd046DpENJAJQEQEkAfgCZwkU4vByFygAAAABJRU5ErkJggg==">                                                                                             |
| `001`   | `y mod 2 = 0`                                                                         | <img alt="Mask pattern 001" style="margin: 0.25em;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGkAAABpCAYAAAA5gg06AAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAACFklEQVR42u3d4W2CUBRAYWhcwFl0BUegI7iCM7hKHYEVcBZHsAN4k96EV949er6fplHCyQ34KjBO0/QcirhcLi+vXa/Xl9d+bremn3tfltTn9vLVewP0NyMBGAnASAC76MXoAN5a9sAcbsv5/O/bl96WxqL94iQBGAnASABGAthl/3DNN/A1B9wtVhyGYMWh0n5xkgCMBGAkACMBpE8ceqm04tCLkwRgJAAjARgJoPyJQ+h0avt+K1YctuAkARgJwEgARgJInzhs8f/9SPivinnusi2RLfaLkwRgJAAjARgJIDxxqHRFQeS+36e2OXuVRvbg32u/OEkARgIwEoCRAMZlWcpcjpl1eDyavl90IlKJkwRgJAAjARgJYIzu49D6m3pkzeWYh+Ox6U64J3/j0Hq1IrufnSQAIwEYCcBIAONznrusOETf8luvJLwLJwnASABGAjASwPgchi4nDt/T9PJa8/szvAknCcBIAEYCMBKAKw4AThKAkQCMBGAkAFccAJwkACMBGAnASADlr6oI7+PgnSNVjZEAjARgJIBdpXs2pK/SaH3nyOKcJAAjARgJwEgAPh0TwEkCMBKAkQCMBFD+6ZghVxxUjZEAjARgJADmQ64KPatiC04SgJEAjARgJIDyJw7hjyM/7OoLJwnASABGAjASQPmnY/obBycJwUgARgIwEkD5p2O64uAkIRgJwEgARgL4Bfx5hh9KaZ3CAAAAAElFTkSuQmCC">                                                                                                                                     |
| `010`   | `x mod 3 = 0`                                                                         | <img alt="Mask pattern 010" style="margin: 0.25em;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGkAAABpCAYAAAA5gg06AAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAACAUlEQVR42u3d3Y2CQBRAYdjQgLVoC5ZAC7ZADbSylmALUIsl6Nu+zE32JqMwR8/3SDCYOZmg/PbjOD66RkzTlFrveDqVC2+33EbO52LRuizFsnme9x6OPz97fwH9z0gARgIwEsAQLczuwGu0tGPO2mtcnEkARgIwEoCRAIbsijU7+podbrTd3zcPSu33y8qOizMJwEgARgIwEoCRAIwEYCQAIwEYCcBIAEYCMBKAkQCMBJA+VbHF+X2iLcbFmQRgJAAjARgJIPzh0PqFi9m7IKKd+jyO5XrJ7e41Ls4kACMBGAnASAD9sizN3I4ZCS+OvF7LFV98O2ZLnEkARgIwEoCRAProOQ7hP/UN/m1/23McvKvigxgJwEgARgLoH11XHnGo2AlnP7seDsWy4/3+9u0SP+tMAjASgJEAjASQvjjy1cJTEJfLroPRKmcSgJEAjARgJIDdfjiEh+mzRxy+jDMJwEgARgIwEoBHHACcSQBGAjASgJEAhqo7Cio+Gx1xWIP1Wn/k9BacSQBGAjASgJEAhpae2RD9mAhPaUS3Y34wZxKAkQCMBGAkgObfjukRB2cSgpEAjARgJIDm344pZxKCkQCMBGAkACMBGAnASABGAjASgJEAjARgJAAjARgJoPm3Y3pxpDMJwUgARgIwEkDzb8f04khnEoKRAIwEYCSAJ1IreE4+WeVxAAAAAElFTkSuQmCC">                                                                                                                                                                 |
| `011`   | `(x + y) mod 3 = 0`                                                                   | <img alt="Mask pattern 011" style="margin: 0.25em;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGkAAABpCAYAAAA5gg06AAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAACQElEQVR42u2d0W3CMBBAkyoLMEtZgRHCCKzADKzACGUEVgizMAIdwCfVqt3kXnnv0+IS5KfTObk4Ged5fg1JOJ/PxdjlcinGvm63Mvh+rzvJ4VAMPZal6rxb8bH1H5CfURIAJQFQEoApGowKeG+aCnPDIqE6NmCreTGTACgJgJIAKAnAVPvDlkLfUnDD2OezHOu8SMg0L2YSACUBUBIAJQGoXjhsxRqtiiFoVWTCTAKgJABKAqAkAOkXDuFV+elUF7zRXYjemEkAlARASQCUBKB64bBGf7+JjRYJa8yLmQRASQCUBEBJAMKFQ6YdBdF/CXdfzHP5u93u18fLNC9mEgAlAVASACUBGJdlSbMdMyJ8xqFzq+IRLDAyYSYBUBIAJQFQEoCp+oq+8mq7Jbb2eGvsquh9Z6JlXswkAEoCoCQASgIwvoahvOOwwnsSoqv8z2hB0Pm8xFgzCYCSACgJgJIAjK/7va5V0bloHoNnEsIWBLDQ9441kwAoCYCSACgJQLyrYoWiGT24OOz3f35eYqyZBEBJAJQEQEkANmtVHK/XYqz3Q4//JdZMAqAkAEoCoCQA7qoAxJpJAJQEQEkAlARgyvTOhqaXKSUq9LYq3hAlAVASACUBYH4dM3mh7x1rJgFQEgAlAVASAObXMW1VSDaUBEBJAJQEIP1HrqqfcUhU6G1VvCFKAqAkAEoCkH7hEJK80PeONZMAKAmAkgAoCUD6r2PaqjCTECgJgJIAKAkA8uuYtiokHUoCoCQASgLwDYUj9KHmElU3AAAAAElFTkSuQmCC">                                                                             |
| `100`   | `((y intdiv 2) + (x intdiv 3)) mod 2 = 0`                                             | <img alt="Mask pattern 100" style="margin: 0.25em;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGkAAABpCAYAAAA5gg06AAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAACOUlEQVR42u3d623CMBRAYaiyALOUFRghHYEVmIEVGAFGYIUwCyPQAXKrXmET+0Tn+xlVTZQjK0+T7TiOr00nTqfTbNn5fJ4tux6P76/kcJgtekxTar2tfLXeAP3PSABGAjASwBAtjA7gtWUPzOG2PJ8f3770tlQW7RdHEoCRAIwEYCSAIfuHJVfg1Q+4wV2DtPu96qYssV8cSQBGAjASgJEA0icOrYSPKlpv1MIcSQBGAjASgJEAuj9xCJXcNYjuVgTvOPTEkQRgJAAjARgJIH3isMTzfaIl9osjCcBIAEYCMBJAeOLQ04yCyGO3S21zOEtjHOd/l1xvq/3iSAIwEoCRAIwEsJ2mqZvpmJHwHYfb7f1/GDzmiE5EeuJIAjASgJEAjAQwZK/Us9JX/iVX75XfcYjuQtSW3adOx4QyEoCRAIwEsH1tNu/fcSg4gEdX+d/7/cfX22wqZ8F6HUkARgIwEoCRAIZWB8PoKv+a3Rbgwb9kvY4kACMBGAnASADNpmP29IuQvXMkARgJwEgARgJo9qji53KZLUu/9Ai8a+CjipUzEoCRAIwEwJxVUfkjV2meOOgvRgIwEoCRAIaefrMhPZsDePD3HYeVMxKAkQCMBMD8OmbJ7zgAOZIAjARgJAAjAXT/dczwUQXwroGPKlbOSABGAjASQPcfuXL2hSMJwUgARgIwEkD3Jw4h4F0D33FYOSMBGAnASAB+HRPAkQRgJAAjARgJoPuvY/qOgyMJwUgARgIwEsAvrlWV7ncl77wAAAAASUVORK5CYII=">                                                                                     |
| `101`   | `(x y) mod 2 + (x y) mod 3 = 0`<br>or:<br>`(x y) mod 6 = 0`                           | <img alt="Mask pattern 101" style="margin: 0.25em;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGkAAABpCAYAAAA5gg06AAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAACTElEQVR42u3d3XHCMBBFYZNxA9QCLVCCUwItUAOtkBJowdRCCeQtL95MFCTkPcn5HhkHOb6zI/ln8WaapseQxOl0Ktput983Hfc2z4vPzufz2ofjy9vaO6CfGRKAIQEYEsAYfVg6gdconZij7S7X6/MDHw5P/+lax8VKAjAkAEMCMCSAsXTDmjPw5hNuNPnXLCYq9DguVhKAIQEYEoAhARQvHFKJFgmJFhOtWUkAhgRgSACGBJB+4RCelUfPOPyRRULESgIwJABDAjAkgOKFQ4/7+8USLRJ6HBcrCcCQAAwJwJAAwoVDpo6CaF+iybp4u2lablexLz1YSQCGBGBIAIYEsJnnOU07ZiTsqjgen//C4FmIqB0zEysJwJAADAnAkADG0jP11qrGbfwgZOmVhNL9a91pYSUBGBKAIQEYEsDmMQzLKw41XQuF292228Vnu/v95eMSt7OSAAwJwJAADAmgvKuicQtk8S2ItVovE41rJQEYEoAhARgSQF07ZsWkGV72j644NB53rf+3hpUEYEgAhgRgSADxrYoO3oPuhsvHx9rHIyUrCcCQAAwJwJAA7KoYhj5XEirGtZIADAnAkAAMCWDM9JsN0e2LVL8cudKiw0oCMCQAQwIwJID0b8cM9XhwsXW3RMW4VhKAIQEYEoAhAfh2zO/YVaHfMCQAQwIwJID0L7kK2VWhbAwJwJAADAmAuXCIzvwzfV/jca0kAEMCMCQAQwL4f2/HtKtCr2BIAIYEYEgAyLdjhr/3kHzyr2ElARgSgCEBGBLAJ/xytquLK9I4AAAAAElFTkSuQmCC">                                                             |
| `110`   | `((x y) mod 2 + (x y) mod 3) mod 2 = 0`<br>or:<br>`(x y) mod 6 < 3`                   | <img alt="Mask pattern 110" style="margin: 0.25em;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGkAAABpCAYAAAA5gg06AAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAACZklEQVR42u2d7W3CMBBAScUCzFJWYIR0BFZgBlaBEVghzMIIdICc1Cu2bL/ovZ9W0gt5Otm9+GOa5/m9G4TL5bJqu16vq7bb/V417nNZUnF78dX7AeRvlARASQCUBGAfNUYdeG2KOubH4/N7T6ePb+31XswkAEoCoCQASgKwz15Y0tFX73CznX/JACNJi/diJgFQEgAlAVASgPTAoRdh53o+r9sKKgmjYyYBUBIAJQFQEoDhBw7pOQ5RdWEjgwkzCYCSACgJgJIApAcOLb7vp+MOVHFo8V7MJABKAqAkAEoCEA4cRlpRED1LuPpinnPXRX+v4FlaYCYBUBIAJQFQEoBpWZZhlmNm+X69Pr85qExEyzFHwkwCoCQASgKgJABTtI9Dtvze6z/w9D4OyXkPP8lqRfYdZCsdWcwkAEoCoCQASgIwvXe7dcUhO9Gw4Lrn4bBqCysJleMSrzOTACgJgJIAKAlA2aqKgs4wmpNwiyY9Vo7b6/eWYCYBUBIAJQFQEoB8xSFLstOM5hXUnruQptNOlNm4ZhIAJQFQEgAlAYgHDg2I5hXUPoNiK5hJAJQEQEkAlARg+FUV4QZQ2U8aEcDKhJkEQEkAlARASQD2I+3ZkN5MqcVZFSVzFyrHNZMAKAmAkgAoCcB2TsesXUloMRHSVRXbQUkAlARASQC2czqmqyqkJ0oCoCQASgIw/CFXIS0GCdnPDQ3imkkAlARASQCUBGD4gUNYrTge6wbpdYpmMq6ZBEBJAJQEQEkAhj8dM2TwfRdqxzWTACgJgJIAKAkA8nTMorMqsriqQv6DkgAoCYCSAPwCMNS/C0jBNg8AAAAASUVORK5CYII=">                         |
| `111`   | `((x y) mod 3 + (x + y) mod 2) mod 2 = 0`<br>or:<br>`(x + y + (x y) mod 3) mod 2 = 0` | <img alt="Mask pattern 111" style="margin: 0.25em;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGkAAABpCAYAAAA5gg06AAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAACd0lEQVR42u2dwY3CMBAAkxMNUAvXAiWEEmiBGmiBEqAEWgi1UAL3u09WdyscJTto5hk5Cni08mZtx/0wDK+uCKfTKdVu93xOL+7302v3e6rdYxwn187n89rd8cvX2j9A/kdJAJQEQEkANtHF7ADeQtPAHCUJ2XZRMpFkrX4xkgAoCYCSACgJwCbbsGWgn33ATVYSQqJ2QcWhUr8YSQCUBEBJAJQEIJ04lKJhWqKl4rAWRhIAJQFQEgAlAWAmDtnBP5tMNFQclsBIAqAkAEoCoCQA6cRhifn9iGgq4Hq7TRu2JBMNLNEvRhIAJQFQEgAlAQgTh0o7CiKyuyCiQT3brlK/GEkAlARASQCUBKAfx7HMdswsu+/v928OKg6P7Xbtv/QnRhIAJQFQEgAlAeij7zi0vIFn3/Ijsvdej8fpzdldFQGHYXj7tzhVIV3XKQmBkgAoCUD/ut+nFYe5dy0kP7oUVhJmfi6xnZEEQEkAlARASQD6V9e9P1XRsNDwcLlMroWLHmd+bktlYq3nGkkAlARASQCUBCBfcciSHFyjdQVzr11IUzyZMJIAKAmAkgAoCUBccVig/B5WHLJrFwpNIyzRzkgCoCQASgKgJADld1Wkv+OQBViZMJIAKAmAkgAoCUD/0adjZimeTBhJAJQEQEkAlASg/OmYTV+OLDTd4K6KD0dJAJQEQEkAyp+OGd4brYWImPsUzbnPyEg+10gCoCQASgKgJADlD7la5KyK4smEkQRASQCUBEBJAMonDumKQ/HpBndVfDhKAqAkAEoCUP50zJDiFYK5n2skAVASACUBUBKA8rsqljirwl0V0oySACgJgJIA/AAqmPkQSHu6hwAAAABJRU5ErkJggg=="> |

<sup>*</sup> where `x` = column (width) and `y` = row (height), with `x,y = 0,0` for the top left module<br>


## Reflectance

Symbols are intended to be read when either dark on light or light on dark.
The International Standard (ISO/IEC 18004) is based on dark images on a light background (example on the left),
reflectance reversal therefore means a light image on dark background (example on the right).

<p align="center">
	<img alt="Normal reflectance" style="margin: 0.25em;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAALkAAAC5CAYAAAB0rZ5cAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAAEOUlEQVR42u3dS24jNxRA0XSQPXsN3pv31JllVAGqQ4aPvn3O2JJK8gWBB9bnx9fX188/IOzP6QOA/5vIyRM5eSInT+TkiZw8kZMncvJETp7IyRM5eSInT+TkiZw8kZMncvJETp7IyRM5eX/tfsPPz8/p7/SPj4+P/3x8T69d8fS5K8c39bucsPu3t5KTJ3LyRE6eyMnbPng+2T1IPJkaKN8ey9vPvWlYvun/tsJKTp7IyRM5eSIn78jg+WRl4FgZiFaGwpVjnhpGVz53929wYpB9YiUnT+TkiZw8kZM3Nnje5MRQeOL01t1DZoWVnDyRkydy8kROnsHzX0xd8zi1G1lmJSdP5OSJnDyRkzc2eE7txK3sUD45cerp1Cm+K8d8Eys5eSInT+TkiZy8I4PnTTtxK6fBTp1+O3U6703/txVWcvJETp7IyRM5eT++vr5+Th/EtBPD6NvPPeE77lqusJKTJ3LyRE6eyMnbPnjuHqZWTkc9MTyu/AZTp/PedGrxif+HlZw8kZMncvJETt72U21PDIpvP3dqGJ16/MnTa0+cknv7DqqVnDyRkydy8kRO3vbB88Q1hTcNmW+/x9RnTJ3Ou3vXd4WVnDyRkydy8kRO3pEdz6ldt5uuybzpGZsnhtHdO98rrOTkiZw8kZMncvKO3FzoxED59rVvj29qgN79u5wYbm/a3XxiJSdP5OSJnDyRkzd2c6Gp6wxPXFs69blTr337fd+y4wm/SOTkiZw8kZN35DmeJ3YP377f1A2MVtx0fehNpwy/ZSUnT+TkiZw8kZN3ZPCc2mVcOZap32XFTacH33QqsJWcPJGTJ3LyRE7eVTueK1Z2PN++39vvtvJ3u7/vidfeNMw/sZKTJ3LyRE6eyMkbu6vtTa99+35PbtrN3W33KblTrOTkiZw8kZMncvKO3FxoaifupjvOvj3mlffbbep/uZuVnDyRkydy8kRO3pHHqTw5cU3h1Pc4ca3l1A7v7t/lBCs5eSInT+TkiZy8628udOLxHVNuOpX1O/5+b1nJyRM5eSInT+Tkje14Ttm9Kzi1s3dimH/7ubt5jif8IpGTJ3LyRE7ekWs8p9y+W3ri9Na3nzt1KrDHqcAGIidP5OSJnLyxx6nsdtM1o2+P5aabKd10N93drOTkiZw8kZMncvKODJ5PbroRzu4dwJUB8MSgeGJIPzF8v2UlJ0/k5ImcPJGTNzZ4Tnk76Jy4a+zuIXPquaW33xTKSk6eyMkTOXkiJ++3GzzfOnEt44md25Xv5jme8E2InDyRkydy8sYGz9uvKZy6sc7uU3ffHt+KqTvsvmUlJ0/k5ImcPJGTN/Ycz+9o6i60b4/lphss3cRKTp7IyRM5eSIn77d7jie/Hys5eSInT+TkiZw8kZMncvJETp7IyRM5eSInT+TkiZw8kZMncvJETp7IyRM5eSInT+Tk/Q00zDn7P29DTwAAAABJRU5ErkJggg==">
	<img alt="Reversed reflectance" style="margin: 0.25em;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAALkAAAC5CAYAAAB0rZ5cAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QA/wD/AP+gvaeTAAAEOUlEQVR42u3dS24jNxRA0XSQLXotXoNX3ZllVAGqQ4aPvn3O2JJK8gWBB9bnx8fHx88/IOzP6QOA/5vIyRM5eSInT+TkiZw8kZMncvJETp7IyRM5eSInT+TkiZw8kZMncvJETp7IyRM5eX/tfsPPz8/p7/SPr6+v/3x8T69d8fS5K8c39bucsPu3t5KTJ3LyRE6eyMnbPng+2T1IPJkaKN8ey9vPvWlYvun/tsJKTp7IyRM5eSIn78jg+WRl4FgZiFaGwpVjnhpGVz53929wYpB9YiUnT+TkiZw8kZM3Nnje5MRQeOL01t1DZoWVnDyRkydy8kROnsHzX0xd8zi1G1lmJSdP5OSJnDyRkzc2eE7txK3sUD45cerp1Cm+K8d8Eys5eSInT+TkiZy8I4PnTTtxK6fBTp1+O3U6703/txVWcvJETp7IyRM5eT8+Pj5+Th/EtBPD6NvPPeE77lqusJKTJ3LyRE6eyMnbPnjuHqZWTkc9MTyu/AZTp/PedGrxif+HlZw8kZMncvJETt72U21PDIpvP3dqGJ16/MnTa0+cknv7DqqVnDyRkydy8kRO3vbB88Q1hTcNmW+/x9RnTJ3Ou3vXd4WVnDyRkydy8kRO3pEdz6ldt5uuybzpGZsnhtHdO98rrOTkiZw8kZMncvKO3FzoxED59rVvj29qgN79u5wYbm/a3XxiJSdP5OSJnDyRkzd2c6Gp6wxPXFs69blTr337fd+y4wm/SOTkiZw8kZN35DmeJ3YP377f1A2MVtx0fehNpwy/ZSUnT+TkiZw8kZN3ZPCc2mVcOZap32XFTacH33QqsJWcPJGTJ3LyRE7eVTueK1Z2PN++39vvtvJ3u7/vidfeNMw/sZKTJ3LyRE6eyMkbu6vtTa99+35PbtrN3W33KblTrOTkiZw8kZMncvKO3FxoaifupjvOvj3mlffbbep/uZuVnDyRkydy8kRO3pHHqTw5cU3h1Pc4ca3l1A7v7t/lBCs5eSInT+TkiZy8628udOLxHVNuOpX1O/5+b1nJyRM5eSInT+Tkje14Ttm9Kzi1s3dimH/7ubt5jif8IpGTJ3LyRE7ekWs8p9y+W3ri9Na3nzt1KrDHqcAGIidP5OSJnLyxx6nsdtM1o2+P5aabKd10N93drOTkiZw8kZMncvKODJ5PbroRzu4dwJUB8MSgeGJIPzF8v2UlJ0/k5ImcPJGTNzZ4Tnk76Jy4a+zuIXPquaW33xTKSk6eyMkTOXkiJ++3GzzfOnEt44md25Xv5jme8E2InDyRkydy8sYGz9uvKZy6sc7uU3ffHt+KqTvsvmUlJ0/k5ImcPJGTN/Ycz+9o6i60b4/lphss3cRKTp7IyRM5eSIn77d7jie/Hys5eSInT+TkiZw8kZMncvJETp7IyRM5eSInT+TkiZw8kZMncvJETp7IyRM5eSInT+Tk/Q2qDCsxrG9jfAAAAABJRU5ErkJggg==">
</p>
