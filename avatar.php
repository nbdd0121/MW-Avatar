<?php

use MediaWiki\Actions\ActionEntryPoint;
use MediaWiki\Context\RequestContext;
use MediaWiki\EntryPointEnvironment;
use MediaWiki\MediaWikiServices;

// Resolve from the served path so an extension symlink still uses the wiki's core.
$wikiRoot = dirname($_SERVER['SCRIPT_FILENAME'], 3);
define('MW_ENTRY_POINT', 'index');
require $wikiRoot . '/includes/WebStart.php';

$context = RequestContext::getMain();
$request = $context->getRequest();
$request->setVal('title', 'Special:AvatarRedirect');
$request->setVal('curid', null);
$request->setVal('action', 'view');

(new ActionEntryPoint($context, new EntryPointEnvironment(), MediaWikiServices::getInstance()))->run();
