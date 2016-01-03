<?php
namespace Avatar;

class Avatar {

	public static function onGetPreferences($user, &$preferences) {
		$link = \Linker::link(\SpecialPage::getTitleFor("AvatarUpload"), wfMsg('avatarupload'));

		$preferences['editavatar'] = array(
			'type' => 'info',
			'raw' => true,
			'label-message' => 'prefs-editavatar',
			'default' => $link,
			'section' => 'personal/info',
		);

		return true;
	}
}