<?php

$autoload = spl_autoload_register('search_any_class');

function search_any_class($className)
{
	$fileName = search_module_class($className);

	// If requested class exists, load it, otherwise throw an exception
	if ($fileName != '' && file_exists($fileName)) {
		require_once $fileName;
	} else {
		// TODO autosend error report to site administrator, show polite FailWhale
		throw new Exception("Unknown class: $className", 1);
	}
}

function search_module_class($className)
{
	foreach (glob(__DIR__ . '/modules/*') as $module) {
		$filename = "$module/classes/$className.php";
		if (file_exists($filename)) {
			return $filename;
		}
	}

	return '';
}

