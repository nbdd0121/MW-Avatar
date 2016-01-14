<?php
namespace Avatar;

class Hooks {

	public static function onGetPreferences(\User $user, &$preferences) {
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

	public static function onSkinBuildSidebar(\Skin $skin, &$bar) {
		$relevUser = $skin->getRelevantUser();
		if ($relevUser) {
			$bar['sidebar-section-extension'][] =
			array(
				'text' => wfMsg('sidebar-viewavatar'),
				'href' => \SpecialPage::getTitleFor('ViewAvatar')->getLocalURL(array(
					'user' => $relevUser->getName(),
				)),
				'id' => 'n-viewavatar',
				'active' => '',
			);
		}
		return true;
	}
}