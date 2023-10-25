<?php
/**
 * This configuration will be read and overlaid on top of the
 * default configuration. Command-line arguments will be applied
 * after this file is read.
 */
return [
	// Supported values: `'5.6'`, `'7.0'`, `'7.1'`, `'7.2'`, `'7.3'`,
	// `'7.4'`, `null`.
	// If this is set to `null`,
	// then Phan assumes the PHP version which is closest to the minor version
	// of the php executable used to execute Phan.
	//
	// Note that the **only** effect of choosing `'5.6'` is to infer
	// that functions removed in php 7.0 exist.
	// (See `backward_compatibility_checks` for additional options)
	'target_php_version' => null,
	'minimum_target_php_version' => '7.4',

	// A list of directories that should be parsed for class and
	// method information. After excluding the directories
	// defined in exclude_analysis_directory_list, the remaining
	// files will be statically analyzed for errors.
	//
	// Thus, both first-party and third-party code being used by
	// your application should be included in this list.
	'directory_list' => [
		'examples',
		'src',
		'tests',
		'vendor',
		'.phan/stubs'
	],

	// A regex used to match every file name that you want to
	// exclude from parsing. Actual value will exclude every
	// "test", "tests", "Test" and "Tests" folders found in
	// "vendor/" directory.
	'exclude_file_regex' => '@^vendor/.*/(tests?|Tests?)/@',

	// A directory list that defines files that will be excluded
	// from static analysis, but whose class and method
	// information should be included.
	//
	// Generally, you'll want to include the directories for
	// third-party code (such as "vendor/") in this list.
	//
	// n.b.: If you'd like to parse but not analyze 3rd
	//       party code, directories containing that code
	//       should be added to both the `directory_list`
	//       and `exclude_analysis_directory_list` arrays.
	'exclude_analysis_directory_list' => [
		'vendor/',
		'.phan/stubs'
	],
	'suppress_issue_types' => [
		'PhanAccessMethodInternal',
		'PhanAccessOverridesFinalConstant',
		'PhanDeprecatedClass',
		'PhanDeprecatedClassConstant',
	],
];
