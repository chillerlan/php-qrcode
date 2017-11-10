<?php
/**
 *
 * @filesource   authenticator.php
 * @created      10.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

require_once '../vendor/autoload.php';

use chillerlan\{
	GoogleAuth\Authenticator,
	QRCode\QRCode,
	QRCode\Output\QRMarkup
};

$authenticator = new Authenticator;

$secret = $authenticator->createSecret(); // -> userdata
$data   = $authenticator->getUri($secret, 'label', 'example.com');

// markup - svg
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"/></head><body><div>'.(new QRCode($data, new QRMarkup))->output().'</div></body>';


