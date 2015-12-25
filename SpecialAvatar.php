<?php

class SpecialAvatar extends SpecialPage {

	public function __construct() {
		parent::__construct('Avatar');
	}

	public function execute( $par ) {
		global $wgDefaultAvatar;
		$path = $wgDefaultAvatar;
		$user = User::newFromName($par);
		if($user && $user->getId()) {
			$option = $user->getOption('avatar-url');
			if($option) {
				$path = $option;
			}
		}

		$this->getOutput()->disable();
		$response = $this->getRequest()->response();

		// We use send custom header, in order to control cache
		$response->statusHeader('302');
		$response->header('Content-Type: text/html; charset=utf-8');
		$response->header('Cache-Control: public, max-age=86400');
 		$response->header('Location: ' . $path);
	}

	public function isCached() {
		return true;
	}

	public function isListed() {
		return false;
	}
}