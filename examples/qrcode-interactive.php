<?php
/**
 * @created      18.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

require_once '../vendor/autoload.php';

try{

	$moduleValues = [
		// finder
		QRMatrix::M_FINDER_DARK    => $_POST['m_finder_dark'],
		QRMatrix::M_FINDER_DOT     => $_POST['m_finder_dot_dark'],
		QRMatrix::M_FINDER         => $_POST['m_finder_light'],
		// alignment
		QRMatrix::M_ALIGNMENT_DARK => $_POST['m_alignment_dark'],
		QRMatrix::M_ALIGNMENT      => $_POST['m_alignment_light'],
		// timing
		QRMatrix::M_TIMING_DARK    => $_POST['m_timing_dark'],
		QRMatrix::M_TIMING         => $_POST['m_timing_light'],
		// format
		QRMatrix::M_FORMAT_DARK    => $_POST['m_format_dark'],
		QRMatrix::M_FORMAT         => $_POST['m_format_light'],
		// version
		QRMatrix::M_VERSION_DARK   => $_POST['m_version_dark'],
		QRMatrix::M_VERSION        => $_POST['m_version_light'],
		// data
		QRMatrix::M_DATA_DARK      => $_POST['m_data_dark'],
		QRMatrix::M_DATA           => $_POST['m_data_light'],
		// darkmodule
		QRMatrix::M_DARKMODULE     => $_POST['m_darkmodule_dark'],
		// separator
		QRMatrix::M_SEPARATOR      => $_POST['m_separator_light'],
		// quietzone
		QRMatrix::M_QUIETZONE      => $_POST['m_quietzone_light'],
		// logo
		QRMatrix::M_LOGO           => $_POST['m_logo_light'],
	];

	$moduleValues = array_map(function($v){
		if(preg_match('/[a-f\d]{6}/i', $v) === 1){
			return in_array($_POST['output_type'], ['png', 'jpg', 'gif'])
				? array_map('hexdec', str_split($v, 2))
				: '#'.$v ;
		}
		return null;
	}, $moduleValues);


	$ecc = in_array($_POST['ecc'], ['L', 'M', 'Q', 'H'], true) ? $_POST['ecc'] : 'L';

	$options = new QROptions([
		'version'          => (int)$_POST['version'],
		'eccLevel'         => constant('chillerlan\\QRCode\\Common\\EccLevel::'.$ecc),
		'maskPattern'      => (int)$_POST['maskpattern'],
		'addQuietzone'     => isset($_POST['quietzone']),
		'quietzoneSize'    => (int)$_POST['quietzonesize'],
		'moduleValues'     => $moduleValues,
		'outputType'       => $_POST['output_type'],
		'scale'            => (int)$_POST['scale'],
		'outputBase64'     => true,
		'imageTransparent' => false,
	]);

	$qrcode = (new QRCode($options))->render($_POST['inputstring']);

	if(in_array($_POST['output_type'], ['png', 'jpg', 'gif', 'svg'])){
		$qrcode = '<img alt="qrcode" src="'.$qrcode.'" />';
	}
	elseif($_POST['output_type'] === 'text'){
		$qrcode = '<pre style="font-size: 75%; line-height: 1;">'.$qrcode.'</pre>';
	}
	elseif($_POST['output_type'] === 'json'){
		$qrcode = '<pre style="font-size: 75%; overflow-x: auto;">'.$qrcode.'</pre>';
	}

	sendResponse(['qrcode' => $qrcode]);
}
// Pokémon exception handler
catch(Throwable $e){
	header('HTTP/1.1 500 Internal Server Error');
	sendResponse(['error' => $e->getMessage()]);
}

exit;

/**
 * @param array $response
 */
function sendResponse(array $response){
	header('Content-type: application/json;charset=utf-8;');
	echo json_encode($response);
	exit;
}
