<?php
/**
 *
 * @filesource   authenticator.php
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeExamples;

require_once '../vendor/autoload.php';

header('Content-type: image/svg+xml');

echo (new MyAuthenticatorClass)->getQRCode();
