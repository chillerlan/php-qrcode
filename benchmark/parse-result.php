<?php
/**
 * Parses the CSV result into more handy JSON
 *
 * @created      26.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeBenchmark;

use function array_combine;
use function array_map;
use function array_shift;
use function explode;
use function file_get_contents;
use function file_put_contents;
use function floatval;
use function intval;
use function json_encode;
use function str_replace;
use function str_starts_with;
use function trim;
use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_PRETTY_PRINT;

require_once __DIR__.'/parse-common.php';

const SEPARATOR = '|';
const BOOLEANS  = ['has_baseline', 'env_php_xdebug', 'env_opcache_extension_loaded', 'env_opcache_enabled'];
const INTEGERS  = [
	'variant_index', 'variant_revs', 'variant_iterations', 'iteration_index', 'result_time_net',
	'result_time_revs', 'result_mem_peak', 'result_mem_real', 'result_mem_final',
];
const FLOATS    = [
	'env_sampler_nothing', 'env_sampler_md5', 'env_sampler_file_rw', 'result_time_avg',
	'result_comp_z_value', 'result_comp_deviation',
];
const ARRAYS = ['subject_groups', 'variant_params'];


function parseLine(string $line):array{
	return array_map(fn(string $str):string => trim($str, "\ \n\r\t\v\0\""), explode(SEPARATOR, $line));
}

$csv    = explode("\n", trim(file_get_contents(FILE.'.csv')));
$header = parseLine(array_shift($csv));
$parsed = array_map(fn(string $line):array => array_combine($header, parseLine($line)), $csv);
$json   = ['env' => [], 'suite' => [], 'benchmark' => []];

foreach($parsed as $i => $result){

	// booleans
	foreach(BOOLEANS as $bool){
		$result[$bool] = (bool)$result[$bool];
	}

	// integers
	foreach(INTEGERS as $int){
		$result[$int] = intval($result[$int]);
	}

	// floats
	foreach(FLOATS as $float){
		$result[$float] = floatval($result[$float]);
	}

	// arrays
	foreach(ARRAYS as $array){
		$val = trim($result[$array], '"[]');

		if($val === ''){
			$result[$array] = [];

			continue;
		}

		$val = array_map('trim', explode(',', $val));

		$result[$array] = $val;
	}

	// rename some things to avoid bloat
	$result['subject_revs']       = $result['variant_revs'];
	$result['subject_iterations'] = $result['variant_iterations'];
	$result['result_index']       = $result['iteration_index'];

	unset($result['variant_revs'], $result['variant_iterations'], $result['iteration_index'], $result['result_time_revs']);

	// add the class name
	$json['benchmark'][$result['benchmark_name']]['class'] = $result['benchmark_class'];

	foreach($result as $k => $v){

		// the environment info is only needed once
		if(str_starts_with($k, 'env_')){

			if($i === 0){
				$json['env'][str_replace('env_', '', $k)] = $v;
			}

			continue;
		}

		// suite info is needed only once
		if(str_starts_with($k, 'suite_')){

			if($i === 0){
				$json['suite'][str_replace('suite_', '', $k)] = $v;
			}

			continue;
		}

		// test subject info once per test subject
		if(str_starts_with($k, 'subject_')){

			// skip the name as it is the key
			if($k === 'subject_name'){
				continue;
			}

			// phpcs:ignore
			$json['benchmark'][$result['benchmark_name']]['subjects'][$result['subject_name']][str_replace('subject_', '', $k)] = $v;
		}

		// add variants
		if(str_starts_with($k, 'variant_')){
			// phpcs:ignore
			$json['benchmark'][$result['benchmark_name']]['subjects'][$result['subject_name']]['variants'][$result['variant_index']][str_replace('variant_', '', $k)] = $v;
		}

		// add benchmark results per variant
		if(str_starts_with($k, 'result_')){
			// phpcs:ignore
			$json['benchmark'][$result['benchmark_name']]['subjects'][$result['subject_name']]['variants'][$result['variant_index']]['results'][$result['result_index']][str_replace('result_', '', $k)] = $v;
		}

	}

}

file_put_contents(FILE.'.json', json_encode($json, (JSON_PRESERVE_ZERO_FRACTION | JSON_PRETTY_PRINT)));
