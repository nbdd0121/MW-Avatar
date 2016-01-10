# Avatar
Yet another avatar architecture for MediaWiki

## Install
* Install php-gd, which is a dependency of this extension
* Clone the respository, rename it to Avatar and copy to extensions folder
* Add wfLoadExtension('Avatar'); to your LocalSettings.php
* You are done!

## Configuration
* $wgDefaultAvatar (string), should be set to the URL of the default avatar.
* $wgAllowedAvatarRes (array), default value is array(64, 128). Thumbnails will be created upon request when their size is in this list.
* $wgMaxAvatarResolution (integer), default value is 256. This limits maximum resolution of image to be uploaded.
* $wgDefaultAvatarRes (integer), default value is 128. This is the fallback option if resolution is not specified.
* You can set user rights: 
	* avataradmin: User need this right to delete others' avatars.

## How to use
* Set avatar in user preference, and then Special:Avatar/Username will be redirected to your avatar!

## Extra resources
* If you are using Gadgets
    * If you want to display the avatar on the top-right navigation bar, you may find Gadget-ShowAvatar in example folder useful.
    * If you want to display avatars before user link, you may find Gadget-UserLinkAvatar in example folder useful.

