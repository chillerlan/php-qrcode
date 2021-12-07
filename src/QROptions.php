<?php
/**
 * Class QROptions
 *
 * @created      08.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode;

use chillerlan\Settings\SettingsContainerAbstract;

/**
 * The QRCode settings container
 *
 * @property int         $version
 * @property int         $versionMin
 * @property int         $versionMax
 * @property int         $eccLevel
 * @property int         $maskPattern
 * @property bool        $addQuietzone
 * @property int         $quietzoneSize
 * @property string      $outputType
 * @property string|null $outputInterface
 * @property string|null $cachefile
 * @property string      $eol
 * @property int         $scale
 * @property string      $cssClass
 * @property float       $svgOpacity
 * @property string      $svgDefs
 * @property int         $svgViewBoxSize
 * @property string      $svgPreserveAspectRatio
 * @property string      $svgWidth
 * @property string      $svgHeight
 * @property bool        $svgConnectPaths
 * @property array       $svgExcludeFromConnect
 * @property bool        $svgDrawCircularModules
 * @property float       $svgCircleRadius
 * @property array       $svgKeepAsSquare
 * @property string      $textDark
 * @property string      $textLight
 * @property string      $markupDark
 * @property string      $markupLight
 * @property bool        $returnResource
 * @property bool        $imageBase64
 * @property bool        $imageTransparent
 * @property array       $imageTransparencyBG
 * @property int         $pngCompression
 * @property int         $jpegQuality
 * @property string      $imagickFormat
 * @property string|null $imagickBG
 * @property string      $fpdfMeasureUnit
 * @property array|null  $moduleValues
 * @property bool        $readerUseImagickIfAvailable
 * @property bool        $readerGrayscale
 * @property bool        $readerIncreaseContrast
 */
class QROptions extends SettingsContainerAbstract{
	use QROptionsTrait;
}
