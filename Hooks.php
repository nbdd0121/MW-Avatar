<?php
namespace Avatar;

class Hooks {

	public static function onGetPreferences($user, &$preferences) {
		$link = \Linker::link(\SpecialPage::getTitleFor("AvatarUpload"), wfMsg('avatarupload'));

		$preferences['editavatar'] = array(
			'type' => 'info',
			'raw' => true,
			'label-message' => 'prefs-editavatar',
			'default' => '<img src="' . \SpecialPage::getTitleFor("Avatar/{$user->getName()}")->getLinkURL() . '" width="32"></img> ' . $link,
			'section' => 'personal/info',
		);

		return true;
	}
}