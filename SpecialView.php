<?php
namespace Avatar;

use MediaWiki\Exception\PermissionsError;
use MediaWiki\Html\Html;
use MediaWiki\Logging\ManualLogEntry;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\User\User;

class SpecialView extends SpecialPage {

	public function __construct() {
		parent::__construct('ViewAvatar');
	}

	public function execute($par) {
		$output = $this->getOutput();
		if ($par) {
			$output->redirect($this->getPageTitle()->getLinkURL(['user' => $par]));
			return;
		}

		$this->setHeaders();
		$this->outputHeader();
		$request = $this->getRequest();
		$username = $request->getText('user');
		$user = $username === '' ? null : MediaWikiServices::getInstance()
			->getUserFactory()->newFromName($username);
		$userExists = $user && $user->isRegistered();
		$canDelete = $this->getAuthority()->isAllowed('avataradmin');

		if ($request->wasPosted() && $request->getBool('delete')) {
			if (!$canDelete) {
				throw new PermissionsError('avataradmin');
			}
			$this->checkReadOnly();
			if (!$this->getUser()->matchEditToken($request->getVal('wpEditToken'), 'avatar-delete', $request)) {
				$output->addWikiMsg('sessionfailure');
			} else {
				if ($userExists && Avatars::deleteAvatar($user)) {
					$this->logDeletion($user, $request->getText('reason'));
				}
				$output->redirect($this->getPageTitle()->getLinkURL(['user' => $username]), 303);
				return;
			}
		}

		$output->addModules('mediawiki.userSuggest');
		$this->showForm($username);
		if (!$userExists) {
			if ($username !== '') {
				$output->addWikiMsg('viewavatar-nouser');
			}
			return;
		}
		if (!Avatars::hasAvatar($user)) {
			$output->addWikiMsg('viewavatar-noavatar');
			return;
		}

		$image = Html::element('img', [
			'src' => Avatars::getLinkFor($user->getName(), 'original') . '&nocache&ver=' . dechex(time()),
			'alt' => $user->getName(),
			'height' => 400,
		]);
		$output->addHTML(Html::rawElement('p', [], $image));
		if ($canDelete) {
			$this->showDeleteForm($user->getName());
		}
	}

	private function logDeletion(User $user, string $reason) {
		$logEntry = new ManualLogEntry('avatar', 'delete');
		$logEntry->setPerformer($this->getUser());
		$logEntry->setTarget($user->getUserPage());
		$logEntry->setComment($reason);
		$logId = $logEntry->insert();
		$logEntry->publish($logId, $this->getConfig()->get('AvatarLogInRC') ? 'rcandudp' : 'udp');
	}

	private function showForm(string $username) {
		$html = Html::hidden('title', $this->getPageTitle()->getPrefixedText());
		$html .= Html::label($this->msg('viewavatar-username')->text(), 'avatar-username');
		$html .= Html::input('user', $username, 'text', [
			'id' => 'avatar-username',
			'class' => 'mw-autocomplete-user',
			'size' => 45,
			'required' => true,
		]);
		$html .= ' ' . Html::submitButton($this->msg('viewavatar-submit')->text());
		$this->addForm($html, 'get', 'viewavatar-legend');
	}

	private function showDeleteForm(string $username) {
		$html = Html::hidden('title', $this->getPageTitle()->getPrefixedText());
		$html .= Html::hidden('delete', '1');
		$html .= Html::hidden('user', $username);
		$html .= Html::hidden('wpEditToken', $this->getUser()->getEditToken('avatar-delete', $this->getRequest()));
		$html .= Html::label($this->msg('viewavatar-delete-reason')->text(), 'avatar-delete-reason');
		$html .= Html::input('reason', '', 'text', ['id' => 'avatar-delete-reason', 'size' => 45]);
		$html .= ' ' . Html::submitButton($this->msg('viewavatar-delete-submit')->text());
		$this->addForm($html, 'post', 'viewavatar-delete-legend');
	}

	private function addForm(string $html, string $method, string $legend) {
		$legendId = $legend . '-label';
		$heading = Html::element('legend', ['id' => $legendId], $this->msg($legend)->text());
		$fieldset = Html::rawElement('fieldset', [], $heading . $html);
		$this->getOutput()->addHTML(Html::rawElement('form', [
			'action' => $this->getConfig()->get('Script'),
			'method' => $method,
			'aria-labelledby' => $legendId,
		], $fieldset));
	}
}
