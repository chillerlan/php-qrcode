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

use chillerlan\Traits\ContainerAbstract;

/**
 * @property int    $version
 * @property int    $versionMin
 * @property int    $versionMax
 * @property int    $eccLevel
 * @property int    $maskPattern
 * @property bool   $addQuietzone
 * @property bool   $quietzoneSize
 *
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
 *
 * @property string $textDark
 * @property string $textLight
 *
 * @property bool   $imageBase64
 * @property bool   $imageTransparent
 * @property array  $imageTransparencyBG
 * @property int    $pngCompression
 * @property int    $jpegQuality
 *
 * @property array  $moduleValues
 */
class QROptions extends ContainerAbstract{
	use QROptionsTrait;
}
