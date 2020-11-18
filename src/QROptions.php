<?php
/**
 * Class QROptions
 *
 * @filesource   QROptions.php
 * @created      08.12.2015
 * @package      chillerlan\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode;

use chillerlan\Settings\SettingsContainerAbstract;

/**
 * @property int    $version
 * @property int    $versionMin
 * @property int    $versionMax
 * @property int    $eccLevel
 * @property int    $maskPattern
 * @property bool   $addQuietzone
 * @property bool   $quietzoneSize
 *
 * @property string $dataMode
 * @property string $outputType
 * @property string $outputInterface
 * @property string $cachefile
 *
 * @property string $eol
 * @property int    $scale
 *
 * @property string $cssClass
 * @property string $svgOpacity
 * @property string $svgDefs
 * @property int    $svgViewBoxSize
 *
 * @property string $textDark
 * @property string $textLight
 *
 * @property string $markupDark
 * @property string $markupLight
 *
 * @property bool   $returnResource
 * @property bool   $imageBase64
 * @property bool   $imageTransparent
 * @property array  $imageTransparencyBG
 * @property int    $pngCompression
 * @property int    $jpegQuality
 *
 * @property string $imagickFormat
 * @property string $imagickBG
 *
 * @property string $fpdfMeasureUnit
 *
 * @property array  $moduleValues
 */
class QROptions extends SettingsContainerAbstract{
	use QROptionsTrait;
}
