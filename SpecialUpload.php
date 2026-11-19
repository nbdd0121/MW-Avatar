<?php
namespace Avatar;

use MediaWiki\Exception\PermissionsError;
use MediaWiki\Exception\UserBlockedError;
use MediaWiki\Html\Html;
use MediaWiki\Logging\ManualLogEntry;
use MediaWiki\SpecialPage\SpecialPage;

class SpecialUpload extends SpecialPage {

	public function __construct() {
		parent::__construct('UploadAvatar');
	}

	public function execute($par) {
		$this->requireLogin('prefsnologintext2');

		$this->setHeaders();
		$this->outputHeader();
		$request = $this->getRequest();

		$user = $this->getUser();
		$block = $user->getBlock();
		if ($block) {
			throw new UserBlockedError($block);
		}

		if (!$this->getAuthority()->isAllowed('avatarupload')) {
			throw new PermissionsError('avatarupload');
		}

		$output = $this->getOutput();
		$output->addJsConfigVars([
			'wgMaxAvatarResolution' => $this->getConfig()->get('MaxAvatarResolution'),
			'wgAvatarCurrentPreviewUrl' => Avatars::getLinkFor($user->getName(), 'original')
				. '&nocache&ver=' . dechex(time()),
		]);
		$output->addModules('ext.avatar.upload');

		if ($request->wasPosted()) {
			$this->checkReadOnly();
			if (!$user->matchEditToken($request->getVal('wpEditToken'), 'avatar-upload', $request)) {
				$this->displayMessage($this->msg('sessionfailure')->text());
			} elseif ($this->processUpload()) {
				$output->redirect(SpecialPage::getTitleFor('Preferences')->getLinkURL(), 303);
				return;
			}
		} else {
			$this->displayMessage('');
		}
		$this->displayForm();
	}

	private function displayMessage($msg) {
		$this->getOutput()->addHTML(Html::element('div', ['class' => 'error', 'id' => 'errorMsg'], $msg));
	}

	private function processUpload() {
		$request = $this->getRequest();
		$dataurl = $request->getRawVal('avatar');
		if (!$dataurl || parse_url($dataurl, PHP_URL_SCHEME) !== 'data') {
			$this->displayMessage($this->msg('avatar-notuploaded')->text());
			return false;
		}

		$info = @getimagesize($dataurl);
		if (!$info || !in_array($info[2], [IMAGETYPE_GIF, IMAGETYPE_PNG, IMAGETYPE_JPEG], true)) {
			$this->displayMessage($this->msg('avatar-invalid')->text());
			return false;
		}
		[$width, $height] = $info;
		$config = $this->getConfig();
		$maxResolution = $config->get('MaxAvatarResolution');

		if ($width !== $height) {
			$this->displayMessage($this->msg('avatar-notsquare')->text());
			return false;
		}
		if ($width < 32) {
			$this->displayMessage($this->msg('avatar-toosmall')->text());
			return false;
		}
		if ($width > $maxResolution) {
			$this->displayMessage($this->msg('avatar-toolarge')->text());
			return false;
		}
		$image = Thumbnail::open($dataurl, $info);
		if ($image === null) {
			$this->displayMessage($this->msg('avatar-invalid')->text());
			return false;
		}

		$user = $this->getUser();
		$uploadDir = $config->get('AvatarUploadDirectory') . '/' . $user->getId() . '/';
		if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
			throw new \RuntimeException('Could not create the avatar upload directory');
		}
		$image->createThumbnail($maxResolution, $uploadDir . 'original.png');
		foreach (glob($uploadDir . '[0-9]*.png') ?: [] as $file) {
			unlink($file);
		}
		$defaultResolution = $config->get('DefaultAvatarRes');
		$image->createThumbnail($defaultResolution, $uploadDir . $defaultResolution . '.png');

		$logEntry = new ManualLogEntry('avatar', 'upload');
		$logEntry->setPerformer($user);
		$logEntry->setTarget($user->getUserPage());
		$logId = $logEntry->insert();
		$logEntry->publish($logId, $config->get('AvatarLogInRC') ? 'rcandudp' : 'udp');

		return true;
	}

	public function displayForm() {
		$html = Html::hidden('avatar', '');
		$html .= Html::hidden('wpEditToken', $this->getUser()->getEditToken('avatar-upload', $this->getRequest()));
		$html .= Html::element('input', [
			'id' => 'avatar-file',
			'type' => 'file',
			'accept' => 'image/*',
			'aria-label' => $this->msg('uploadavatar-selectfile')->text(),
		]);
		$html .= ' ' . Html::submitButton($this->msg('uploadavatar-submit')->text());
		$html = Html::rawElement('form', [
			'id' => 'avatar-upload-form',
			'action' => $this->getPageTitle()->getLinkURL(),
			'method' => 'post',
		], $html);

		$this->getOutput()->addWikiMsg('uploadavatar-notice');
		$this->getOutput()->addHTML($html);
	}

	public function isListed() {
		return false;
	}
}
