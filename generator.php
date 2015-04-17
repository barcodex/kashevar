<?php

require_once "vendor/autoload.php";

use Kasha\Generator\AppGenerator;

//print_r($argv);
//
if ($argc == 1) {
	printUsage();
} elseif ($argv[1] == '--help') {
	printHelp();
} else {
	$scriptName = array_shift($argv);
	$commandName = array_shift($argv);
	switch ($commandName) {
		case 'create:app':
			createApp($argv);
			break;
		case 'create:module':
			createModule($argv);
			break;
		case 'create:action':
			createAction($argv);
			break;
		default:
			printUsage();
			break;
	}
}

function printUsage()
{
	print 'Usage: php generator.php [params]' . PHP_EOL;
	print '  --help for help' . PHP_EOL;
}

function printHelp()
{
	printUsage();
	print '  [params] can be one of the following:' . PHP_EOL;
	print '  create:app [[appName]]' . PHP_EOL;
	print '  create:module [moduleName] [[appName]]' . PHP_EOL;
	print '  create:action [actionType:actionName] [moduleName] [[appName]]' . PHP_EOL;
}

function createApp($params)
{
	if (count($params) == 0) {
		$g = new AppGenerator(__DIR__);
	} else {
		$appName = $params[0];
		$g = new AppGenerator(dirname(__DIR__) . '/' . $appName);
	}
	$g->setConfig(__DIR__ . '/config.json');
	$g->createApp();
}

function createModule($params)
{
	if (count($params) < 1) {
		printHelp();
	} else {
		// first parameter is expected to be a module name
		$moduleName = $params[0];
		if (count($params) == 1) {
			$g = new AppGenerator(__DIR__);
		} else { // we have at least two parameters, use second as app name
			$appName = $params[1];
			$g = new AppGenerator(dirname(__DIR__) . '/' . $appName);
		}
		$g->createModule($moduleName);
	}
}

function createAction($params)
{
	if (count($params) < 2) {
		printHelp();
	} else {
		list($actionType, $actionName) = explode(':', $params[0], 0); // expected to be "type:name", e.g. "f:save"
		if ($actionName == '') {
			printHelp();
		} else {
			// second parameter is expected to be a module name
			$moduleName = $params[1];
			if (count($params) == 2) {
				$g = new AppGenerator(__DIR__);
			} else { // third parameter (if given) is an app name
				$appName = $params[2];
				$g = new AppGenerator(dirname(__DIR__) . '/' . $appName);
			}
			$g->createAction($moduleName, $actionName, array('type' => $actionType));
		}
	}
}
