<?php
namespace Avatar;

use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Skin\Skin;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\User\User;

class Hooks {

	public static function onGetPreferences(User $user, &$preferences) {
		$link = MediaWikiServices::getInstance()->getLinkRenderer()
			->makeLink(SpecialPage::getTitleFor('UploadAvatar'), wfMessage('uploadavatar')->text());

		$preferences['editavatar'] = array(
			'type' => 'info',
			'raw' => true,
			'label-message' => 'prefs-editavatar',
			'default' => Html::element('img', [
				'src' => Avatars::getLinkFor($user->getName()),
				'width' => 32,
				'alt' => '',
			]) . ' ' . $link,
			'section' => 'personal/info',
		);

		return true;
	}
	
	public static function onSidebarBeforeOutput(Skin $skin, &$sidebar): void {
		$user = $skin->getRelevantUser();
		
		if ($user) {
			$sidebar['TOOLBOX']['viewavatar'] = [
				'id' => 't-viewavatar',
				'text' => wfMessage('sidebar-viewavatar')->text(),
				'href' => SpecialPage::getTitleFor('ViewAvatar')->getLocalURL(array(
					'user' => $user->getName(),
				)),
				'active' => false,
			];
		}
	}

	public static function onSetup() {
		global $wgAvatarUploadPath, $wgAvatarUploadDirectory;

		if ($wgAvatarUploadPath === false) {
			global $wgUploadPath;
			$wgAvatarUploadPath = $wgUploadPath . '/avatars';
		}

		if ($wgAvatarUploadDirectory === false) {
			global $wgUploadDirectory;
			$wgAvatarUploadDirectory = $wgUploadDirectory . '/avatars';
		}
	}
}
