<?php

$output['result'] = 0;
$id = Util::lavnn('id', $_REQUEST, 0);
if ($id > 0) {
	$output['result'] = 1; // success
} else {
	$output['error'] = 'id.missing';
}
