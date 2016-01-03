<?php
namespace Avatar;

class Avatar {

	public static function onGetPreferences($user, &$preferences) {
		global $wgPrefAvatarRes;
		$link = \Linker::link(\SpecialPage::getTitleFor("AvatarUpload"), wfMsg('avatarupload'));

		$preferences['editavatar'] = array(
			'type' => 'info',
			'raw' => true,
			'label-message' => 'prefs-editavatar',
			'default' => '<img src="' . \SpecialPage::getTitleFor("Avatar/{$user->getName()}/$wgPrefAvatarRes")->getLinkURL() . '"></img> ' . $link,
			'section' => 'personal/info',
		);

		return true;
	}
}