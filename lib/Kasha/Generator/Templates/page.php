<?php

/**	@var Kasha/Page */
$page->add('title', TextProcessor::doTemplate('%MODULE%', '%NAME%.title', $pageParams));

// master page placeholders
$page->add('bodyId', '');
$page->add('bodyClasses', '');
$page->add('header', PageHelper::generateHeader());
$page->add('footer', PageHelper::generateFooter());
$page->add('main', TextProcessor::doTemplate('%MODULE%', '%NAME%', $pageParams));

// stylesheets
//$page->addStylesheetFile('.css');

// javascripts
//$page->addJavascriptFile('.js'); // head js file
//$page->addJavascriptFile('.js', true); // body js file
$page->addScriptSnippet(TextProcessor::doTemplate('%MODULE%', '%NAME%.js', $pageParams), true);

