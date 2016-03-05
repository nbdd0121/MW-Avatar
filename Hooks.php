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

	public static function onBaseTemplateToolbox(\BaseTemplate &$baseTemplate, array &$toolbox) {
		if (isset($baseTemplate->data['nav_urls']['viewavatar'])
			&& $baseTemplate->data['nav_urls']['viewavatar']) {
			$toolbox['viewavatar'] = $baseTemplate->data['nav_urls']['viewavatar'];
			$toolbox['viewavatar']['id'] = 't-viewavatar';
		}
	}

	public static function onSkinTemplateOutputPageBeforeExec(&$skinTemplate, &$tpl) {

		$user = $skinTemplate->getRelevantUser();

		if ($user) {
			$nav_urls = $tpl->get('nav_urls');

			$nav_urls['viewavatar'] = [
				'text' => wfMsg('sidebar-viewavatar'),
				'href' => \SpecialPage::getTitleFor('ViewAvatar')->getLocalURL(array(
					'user' => $user->getName(),
				)),
			];

			$tpl->set('nav_urls', $nav_urls);
		}

		return true;
	}
}