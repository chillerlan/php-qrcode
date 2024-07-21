<?php
/**
 * @created      27.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeBenchmark;

use function array_column;
use function array_key_first;
use function array_keys;
use function array_map;
use function array_sum;
use function explode;
use function implode;
use function intdiv;
use function rsort;
use function sprintf;
use function str_repeat;
use const SORT_NUMERIC;

const BUILDDIR = __DIR__.'/../.build/phpbench';
const FILE     = BUILDDIR.'/benchmark'; // without extension

/**
 * @param array<int, array<int, mixed>> $variants
 */
function parseVariants(array $variants):string{
	$data = [];

	foreach($variants as $variant){
		[$version, $ecc, $mode] = explode(',', $variant['name']);

		$data[(int)$version][$mode][$ecc] = parseVariantResults($variant['results']);
	}

	$v        = $data[array_key_first($data)];
	$modeKeys = array_keys($v);
	$eccKeys  = array_keys($v[$modeKeys[0]]);

	$modeHeaders = array_map(fn($mode) => sprintf('<th colspan="4">%s</th>', $mode), $modeKeys);
	$eccHeaders  = array_map(fn($ecc) => sprintf('<th>%s</th>', $ecc), $eccKeys);

	$table = ['<table><thead>'];
	$table[] = sprintf('<tr><th></th>%s</tr>', implode('', $modeHeaders));
	$table[] = sprintf('<tr><th>Version</th>%s</tr>', str_repeat(implode('', $eccHeaders), count($modeKeys)));
	$table[] = '</thead><tbody>';

	foreach($data as $version => $modes){

		$results = [];

		foreach($modes as $eccLevels){
			foreach($eccLevels as [$time_avg, $mem_peak]){
				$results[] = sprintf('<td>%01.3f</td>', $time_avg);
			}
		}

		$table[] = sprintf('<tr><td>%s</td>%s</tr>', $version, implode('', $results));

	}

	$table[] = '</tbody></table>';

	return implode("\n", $table);
}

/**
 * @param array<int, array<int, mixed>> $results
 *
 * @return array{0: float, 1: int}
 */
function parseVariantResults(array $results):array{
	$iterations = count($results);
	$mem_peak   = array_column($results, 'mem_peak');

	rsort($mem_peak, SORT_NUMERIC);

	return [
		(array_sum(array_column($results, 'time_avg')) / $iterations / 1000),
		intdiv($mem_peak[0], 1024),
	];
}
