<?php
namespace Avatar;

use MediaWiki\Html\FormOptions;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Xml\Xml;

class SpecialView extends \SpecialPage {

	public function __construct() {
		parent::__construct('ViewAvatar');
	}

	public function execute($par) {
		// Shortcut by using $par
		if ($par) {
			$this->getOutput()->redirect($this->getPageTitle()->getLinkURL(array(
				'user' => $par,
			)));
			return;
		}

		$this->setHeaders();
		$this->outputHeader();

		// Parse options
		$opt = new FormOptions;
		$opt->add('user', '');
		$opt->add('delete', '');
		$opt->add('reason', '');
		$opt->fetchValuesFromRequest($this->getRequest());

		// Parse user
		$user = $opt->getValue('user');
		$userObj = MediaWikiServices::getInstance()->getUserFactory()->newFromName($user);
		$userExists = $userObj && $userObj->getId() !== 0;

		// If current task is delete and user is not allowed
		$canDoAdmin = MediaWikiServices::getInstance()->getPermissionManager()->userHasRight($this->getUser(), 'avataradmin');
		if ($opt->getValue('delete')) {
			if (!$canDoAdmin) {
				throw new \PermissionsError('avataradmin');
			}
			// Delete avatar if the user exists
			if ($userExists) {
				if (Avatars::deleteAvatar($userObj)) {
					global $wgAvatarLogInRC;

					$logEntry = new \ManualLogEntry('avatar', 'delete');
					$logEntry->setPerformer($this->getUser());
					$logEntry->setTarget($userObj->getUserPage());
					$logEntry->setComment($opt->getValue('reason'));
					$logId = $logEntry->insert();
					$logEntry->publish($logId, $wgAvatarLogInRC ? 'rcandudp' : 'udp');
				}
			}
		}

		$this->getOutput()->addModules(array('mediawiki.userSuggest'));
		$this->showForm($user);

		if ($userExists) {
			$haveAvatar = Avatars::hasAvatar($userObj);

			if ($haveAvatar) {
				$html = Xml::tags('img', array(
					'src' => Avatars::getLinkFor($user, 'original') . '&nocache&ver=' . dechex(time()),
					'height' => 400,
				), '');
				$html = Xml::tags('p', array(), $html);
				$this->getOutput()->addHTML($html);

				// Add a delete button
				if ($canDoAdmin) {
					$this->showDeleteForm($user);
				}
			} else {
				$this->getOutput()->addWikiMsg('viewavatar-noavatar');
			}
		} else if ($user) {
			$this->getOutput()->addWikiMsg('viewavatar-nouser');
		}
	}

	private function showForm($user) {
		global $wgScript;

		// This is essential as we need to submit the form to this page
		$html = Html::hidden('title', $this->getPageTitle());

		$html .= Html::label($this->msg('viewavatar-username')->text(), 'ext-avatar-username');
		$html .= Html::input('user', $user, 'text', [
			'class' => 'mw-autocomplete-user',
			'size' => 45,
			'id' => 'ext-avatar-username', # This together with mediawiki.userSuggest will give us an auto completion
		]);

		$html .= ' ';

		// Submit button
		$html .= Html::submitButton($this->msg('viewavatar-submit')->text());

		// Warp Fieldset
		$legend = Html::element('legend', [], $this->msg('viewavatar-legend')->text());
		$html = Html::openElement('fieldset') . $legend . $html . Html::closeElement('fieldset');

		// Wrap with a form
		$html = Xml::tags('form', array('action' => $wgScript, 'method' => 'get'), $html);

		$this->getOutput()->addHTML($html);
	}

	private function showDeleteForm($user) {
		global $wgScript;

		// This is essential as we need to submit the form to this page
		$html = Html::hidden('title', $this->getPageTitle());
		$html .= Html::hidden('delete', 'true');
		$html .= Html::hidden('user', $user);

		$html .= Html::label($this->msg('viewavatar-delete-reason')->text(), 'ext-avatar-reason');
		$html .= Html::input('reason', '', 'text', ['size' => 45, 'id' => 'ext-avatar-reason']);

		$html .= ' ';

		// Submit button
		$html .= Html::submitButton($this->msg('viewavatar-delete-submit')->text());

		// Warp Fieldset
		$legend = Html::element('legend', [], $this->msg('viewavatar-delete-legend')->text());
		$html = Html::openElement('fieldset') . $legend . $html . Html::closeElement('fieldset');

		// Wrap with a form
		$html = Xml::tags('form', array('action' => $wgScript, 'method' => 'get'), $html);

		$this->getOutput()->addHTML($html);
	}
}
