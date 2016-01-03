# Avatar
Yet another avatar architecture for MediaWiki

## Install
* Clone the respository, rename it to Avatar and copy to extensions folder
* Add wfLoadExtension('Avatar'); to your LocalSettings.php
* You are done!

## Configuration
* $wgDefaultAvatar (string), should be set to the URL of the default avatar.
* $wgAvatarThumbRes (array), default value is array(32, 64, 128, 256). Thumbnails will be created corresponding to values in this list.
* $wgMaxAvatarResolution (integer), default value is 256. This limits maximum resolution of image to be uploaded.
* $wgDefaultAvatarRes (integer), default value is 128. This is the fallback option if resolution is not specified or corresponding thumbnail does not exist.
* $wgPrefAvatarRes (integer), default value is 32. This is the resolution of avatar to be displayed in preference panel.

## How to use
* Set avatar in user preference, and then Special:Avatar/Username will be redirected to your avatar!
