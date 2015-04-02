<?php

use Kasha\Runtime;
use Kasha\TextProcessor;
use Kasha\Validator;

$url = Util::lavnn('_nextUrl', $_REQUEST, '');

$validator = new Validator($_REQUEST);
$validator->removeFields(['f', 'PHPSESSID', 'SQLiteManager_currentLangue', '_nextUrl']);
//$validator->checkMetadata($model->getMetadata(), ['id']);
$request = $validator->getFields();
$errors = $validator->getErrors();
if (count($errors) > 0) {
	$_SESSION['form']['data'] = $request;
	$_SESSION['form']['errors'] = $errors;
} else {
//	$updateId = $model->load($id)->update($request);
	if ($updateId == $id) {
		$_SESSION['flash'] = TextProcessor::doTemplate('%MODULE%', 'flash.%NAME%.success');
	} else {
		$_SESSION['error'] = TextProcessor::doTemplate('%MODULE%', 'error.%NAME%.failure');
	}
}

Runtime::redirect($url);
