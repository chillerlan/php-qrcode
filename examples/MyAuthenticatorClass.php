<?php
/**
 * Class MyAuthenticatorClass
 *
 * @filesource   MyAuthenticatorClass.php
 * @created      24.12.2017
 * @package      chillerlan\QRCodeExamples
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeExamples;

use chillerlan\QRCode\{QRCode, QROptions, Traits\QRAuthenticator};

/**
 * using the QRAuthenticator trait
 */
class MyAuthenticatorClass{
	use QRAuthenticator;

	public function getQRCode(){
		// data fetched from wherever
		$this->authenticatorSecret = 'SECRETTEST234567';
		$this->qrOptions = new QROptions(['outputType' => QRCode::OUTPUT_MARKUP_SVG]); // set options if needed
		$label = 'my label';
		$issuer = 'example.com';

		return $this->getURIQRCode($label, $issuer);
	}

}
