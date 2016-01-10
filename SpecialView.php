<?php
namespace Avatar;

class SpecialView extends \SpecialPage {

	public function __construct() {
		parent::__construct('ViewAvatar');
	}

	public function execute($par) {
		// Shortcut by using $par
		if ($par) {
			$this->getOutput()->redirect($this->getTitle()->getLinkURL(array(
				'user' => $par,
			)));
			return;
		}

		$this->setHeaders();
		$this->outputHeader();

		// Parse options
		$opt = new \FormOptions;
		$opt->add('user', '');
		$opt->add('delete', '');
		$opt->fetchValuesFromRequest($this->getRequest());

		// Parse user
		$user = $opt->getValue('user');
		$userObj = \User::newFromName($user);
		$userExists = $userObj && $userObj->getId() !== 0;

		// If current task is delete and user is not allowed
		$canDoAdmin = $this->getUser()->isAllowed('avataradmin');
		if ($opt->getValue('delete')) {
			if (!$canDoAdmin) {
				throw new \PermissionsError('avataradmin');
			}
			// Delete avatar if the user exists
			if ($userExists) {
				if (Avatars::deleteAvatar($userObj)) {
					$logEntry = new \ManualLogEntry('avatar', 'delete');
					$logEntry->setPerformer($this->getUser());
					$logEntry->setTarget($userObj->getUserPage());
					$logId = $logEntry->insert();
					$logEntry->publish($logId, 'rcandudp');
				}
			}
		}

		$this->getOutput()->addModules(array('mediawiki.userSuggest'));
		$this->showForm($user);

		if ($userExists) {
			$haveAvatar = Avatars::hasAvatar($userObj);

			if ($haveAvatar) {
				$html = \Xml::tags('img', array(
					'src' => parent::getTitleFor('Avatar/' . $user . '/original')->getLinkURL(),
					'height' => 400,
				), '');
				$this->getOutput()->addHTML($html);

				// Add a delete button
				if ($canDoAdmin) {
					global $wgScript;
					$html = \Html::hidden('title', $this->getTitle());
					$html .= \Html::hidden('user', $user);
					$html .= \Html::hidden('delete', 'true');
					$html .= \Xml::submitButton($this->msg('viewavatar-delete')->text());
					$html = \Xml::tags('form', array('action' => $wgScript, 'method' => 'get'), $html);
					$this->getOutput()->addHTML($html);
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
		$title = parent::getTitleFor('ViewAvatar');
		$html = \Html::hidden('title', $this->getTitle());

		$html .= \Xml::inputLabel(
			$this->msg('viewavatar-username')->text(),
			'user',
			'',
			45,
			$user,
			array('class' => 'mw-autocomplete-user') # This together with mediawiki.userSuggest will give us an auto completion
		);

		$html .= ' ';

		// Submit button
		$html .= \Xml::submitButton($this->msg('viewavatar-submit')->text());

		// Fieldset
		$html = \Xml::fieldset($this->msg('viewavatar-legend')->text(), $html);

		// Wrap with a form
		$html = \Xml::tags('form', array('action' => $wgScript, 'method' => 'get'), $html);

		$this->getOutput()->addHTML($html);
	}
}