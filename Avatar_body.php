<?php

class Avatar {

	public static function onGetPreferences($user, &$preferences) {
		$preferences['avatar-url'] = array(
			'type' => 'text',
			'label-message' => 'pref-avatar-url',
			'section' => 'personal/info',
		);

		return true;
	}
}