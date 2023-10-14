<?php
/**
 * authenticator excample
 *
 * @created      09.07.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

use chillerlan\Authenticator\{Authenticator, AuthenticatorOptionsTrait};
use chillerlan\Authenticator\Authenticators\AuthenticatorInterface;
use chillerlan\Settings\SettingsContainerAbstract;
use chillerlan\QRCode\{QRCode, QROptionsTrait};
use chillerlan\QRCode\Data\QRMatrix;

require_once __DIR__.'/../vendor/autoload.php';

// create a new options container on the fly that hosts both, authenticator and qrcode
$options = new class extends SettingsContainerAbstract{
	use AuthenticatorOptionsTrait, QROptionsTrait;
};

/*
 * AuthenticatorOptionsTrait
 *
 * @see https://github.com/chillerlan/php-authenticator
 */
$options->mode                 = AuthenticatorInterface::TOTP;
$options->digits               = 8;
$options->algorithm            = AuthenticatorInterface::ALGO_SHA512;

/*
 * QROptionsTrait
 */
$options->version              = 7;
$options->addQuietzone         = false;
$options->outputBase64         = false;
$options->svgAddXmlHeader      = false;
$options->cssClass             = 'my-qrcode';
$options->drawLightModules     = false;
$options->svgUseFillAttributes = false;
$options->drawCircularModules  = true;
$options->circleRadius         = 0.4;
$options->connectPaths         = true;
$options->keepAsSquare         = [
	QRMatrix::M_FINDER_DARK,
	QRMatrix::M_FINDER_DOT,
	QRMatrix::M_ALIGNMENT_DARK,
];
$options->svgDefs              = '
	<linearGradient id="gradient" x1="1" y2="1">
		<stop id="stop1" offset="0" />
		<stop id="stop2" offset="0.5"/>
		<stop id="stop3" offset="1"/>
	</linearGradient>';


// invoke the worker instances
$authenticator = new Authenticator($options);
$qrcode        = new QRCode($options);

// create a secret and URI, generate the QR Code
$secret = $authenticator->createSecret(24);
$uri    = $authenticator->getUri('your authenticator', 'this website');
$svg    = $qrcode->render($uri);

// dump the output
header('Content-type: text/html');

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title>QRCode Authenticator Example</title>
	<style>
		#authenticator-qrcode{
			width: 500px;
			margin: 2em auto;
		}
		#secret{
			box-sizing: border-box;
			display: inline-block;
			width: 100%;
			font-size: 20px;
		}

		/* styles for the embedded SVG QR code */
		.my-qrcode.qr-svg{
			margin-bottom: 1em;
			background: #eee; /* example for https://github.com/chillerlan/php-qrcode/discussions/199 */
		}
		.my-qrcode.qr-data-dark{
			fill: url(#gradient); /* the gradient defined in the SVG defs */
		}

		.my-qrcode > defs > #gradient > #stop1{
			stop-color: #D70071;
		}
		.my-qrcode > defs > #gradient > #stop2{
			stop-color: #9C4E97;
		}
		.my-qrcode > defs > #gradient > #stop3{
			stop-color: #0035A9;
		}
	</style>
</head>
<body>
<div id="authenticator-qrcode">
	<!-- embed the SVG directly -->
	<?php echo $svg ?>
	<!-- the input that holds the authenticator secret -->
	<input value="<?php echo $secret; ?>" id="secret" type="text" readonly="readonly" onclick="this.select();" />
	<label for="secret">secret</label>
</div>
</body>
</html>
<?php

exit;
