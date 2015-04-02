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
	print '  create:app [[name]]' . PHP_EOL;
	print '  create:module [appName] [[moduleName]]' . PHP_EOL;
}

function createApp($params)
{
	if (count($params) == 0) {
		$g = new AppGenerator(__DIR__);
	} else {
		$appName = $params[0];
		$g = new AppGenerator(dirname(__DIR__) . '/' . $appName);
	}
	$g->createApp();
	$fp = fopen("php://stdin", "r");
	print 'Create modules? (Y)';
	$answer = strtoupper(trim(fgets($fp)));
	if ($answer == 'Y' || $answer == '') {
		do {
			print 'module name (provide no name to quit): ';
			$moduleName = trim(fgets($fp));
			$g->createModule($moduleName);
		} while ($moduleName != '');
	}
}

function createModule($params)
{
	if (count($params) == 0) {
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
		$fp = fopen("php://stdin", "r");
		print 'Create actions? (Y)';
		$answer = strtoupper(trim(fgets($fp)));
		if ($answer == 'Y' || $answer == '') {
			do {
				print 'action name (provide no name to quit): ';
				$actionName = trim(fgets($fp));
				if ($actionName != '') {
					print 'action type ([P]age/[F]orm/[I]nline/[J]SON): ';
					$actionType = strtolower(trim(fgets($fp)));
					if (in_array($actionType, ['p', 'f', 'i', 'j'])) {
						$g->createAction($moduleName, $actionName, array('type' => $actionType));
					}
				}
			} while ($actionName != '');
		}
	}
}
