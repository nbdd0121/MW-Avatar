<?php

use MediaWiki\Actions\ActionEntryPoint;
use MediaWiki\Context\RequestContext;
use MediaWiki\EntryPointEnvironment;
use MediaWiki\MediaWikiServices;

// Prefer an explicit install root when the server resolves extension symlinks.
$wikiRoot = getenv('MW_INSTALL_PATH') ?: dirname($_SERVER['SCRIPT_FILENAME'], 3);
define('MW_ENTRY_POINT', 'index');
require $wikiRoot . '/includes/WebStart.php';

$context = RequestContext::getMain();
$request = $context->getRequest();
$request->setVal('title', 'Special:AvatarRedirect');
$request->setVal('curid', null);
$request->setVal('action', 'view');

(new ActionEntryPoint($context, new EntryPointEnvironment(), MediaWikiServices::getInstance()))->run();
