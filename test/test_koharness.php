#!/usr/bin/env php
<?php
/**
 * A very basic integration test script for koharness
 *
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @copyright 2014 Kohana Team
 * @licence   http://kohanaframework.org/license
 */
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');
$basedir = sys_get_temp_dir().'/'.uniqid('koharness-test').'/';
create_test_working_directory($basedir);
create_koharness_configuration($basedir);
run_koharness($basedir);
verify($basedir);
exit;

function create_test_working_directory($basedir)
{
	echoline("Creating working directory in $basedir");
	assert_true(mkdir($basedir, 0777, TRUE), "Directory created");
	register_shutdown_function(function () use ($basedir) {
		`rm -rf $basedir`;
	});

	$subdirs = array(
		'vendor/kohana/core',
		'vendor/kohana/module1'
	);
	foreach ($subdirs as $subdir)
	{
		assert_true(mkdir($basedir.$subdir, 0777, TRUE), "Created subdirectory $subdir");
	}
	assert_true($koharness_path = realpath(__DIR__.'/../'), 'Found koharness package folder');
	assert_true(symlink($koharness_path, $basedir.'vendor/kohana/koharness'), "Linked $koharness_path to working dir vendors");
}

function create_koharness_configuration($basedir)
{
	$config_file = $basedir.'koharness.php';
	echoline("Creating config file in $config_file");
	$config = '<?php return '
		.var_export(array(
			'modules' => array(
				'module1' => $basedir.'vendor/kohana/module1'
			)
		), TRUE).';';

	assert_true(file_put_contents($config_file, $config), 'Wrote config to '.$config_file);
	assert_valid_php($config_file);
}

function run_koharness($basedir)
{
	assert_true($koharness_path = realpath(__DIR__.'/../koharness'), 'Found koharness script');
	$cmd = 'cd '.escapeshellarg($basedir).' && '.escapeshellcmd($koharness_path);
	echoline("Executing koharness with command $cmd");
	passthru($cmd, $return);
	assert_true($return === 0, "Koharness execution returned 0");
}

function verify($basedir)
{
	assert_valid_php($basedir.'koharness_bootstrap.php');
	assert_valid_php('/tmp/koharness/application/bootstrap.php');
	assert_link_to('/tmp/koharness/system', $basedir.'vendor/kohana/core');
	assert_link_to('/tmp/koharness/modules/module1', $basedir.'vendor/kohana/module1');
	assert_link_to('/tmp/koharness/vendor', $basedir.'vendor');
}

function echoline($string)
{
	echo $string.\PHP_EOL;
}

function assert_true($condition, $description)
{
	// Can't use PHP inbuilt assert as there is no description argument before 5.4.8
	if ( ! $condition)
	{
		throw new \Exception($description);
	}
}

function assert_valid_php($file)
{
	assert_true(file_exists($file), "$file exists");
	$cmd = 'php -l '.escapeshellarg($file).' 2>&1';
	passthru($cmd, $return);
	assert_true($return === 0, "PHP file $file is valid PHP");
}

function assert_link_to($path, $target_path)
{
	assert_true(is_link($path), "$path exists and is a symlink");
	$actual_path = readlink($path);
	assert_true($actual_path === $target_path, "$path is link to $target_path (got $actual_path)");
}

function assert_writeable_dir($path)
{
	assert_true(is_dir($path), "$path exists");
	assert_true(is_writable($path), "$path is writeable");
}
