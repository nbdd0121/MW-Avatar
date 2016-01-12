<?php
namespace Avatar;

class Hooks {

	public static function onGetPreferences($user, &$preferences) {
		$link = \Linker::link(\SpecialPage::getTitleFor("UploadAvatar"), wfMsg('uploadavatar'));

		$preferences['editavatar'] = array(
			'type' => 'info',
			'raw' => true,
			'label-message' => 'prefs-editavatar',
			'default' => '<img src="' . Avatars::getLinkFor($user->getName()) . '" width="32"></img> ' . $link,
			'section' => 'personal/info',
		);

		return true;
	}
}