# Avatar 1.2.0
Yet another avatar architecture for MediaWiki

**Note.** There are API changes when upgrading 0.9.2 to 1.0.0. The change is very likely to break your site. See section below for details.

## Install
* Install php-gd, which is a dependency of this extension
* Clone the respository, rename it to Avatar and copy to extensions folder
* Add `wfLoadExtension('Avatar')`; to your LocalSettings.php
* You are done!

## Configuration
* `$wgDefaultAvatar` (string), should be set to the URL of the default avatar.
* `$wgAllowedAvatarRes` (array), default value is array(64, 128). Thumbnails will be created upon request when their size is in this list.
* `$wgMaxAvatarResolution` (integer), default value is 256. This limits maximum resolution of image to be uploaded.
* `$wgDefaultAvatarRes` (integer), default value is 128. This is the fallback option if resolution is not specified.
* `$wgVersionAvatar` (boolean), default to false. When set to true, each redirect will produce a `ver` parameter in query.
* `$wgAvatarServingMethod` (string), default to redirect. This indicates the serving method to use when user's avatar is found
	* `redirect`: Default method, create a 302 redirect to user's true avatar.
	* `readfile`: Use php's readfile to serve the file directly.
	* `accel`   : Use nginx's X-Accel-Redirect to serve the file directly.
	* `sendfile`: Use X-SendFile header to serve the file. Need lighttpd or apache with mod_xsendfile.
* `$wgAvatarLogInRC` (boolean), default to true. When set to true, avatar logs are shown in the recent changes, so it is easier to spot bad avatars and take actions. Set to false can prevent avatar changes from affecting determining active users.
* `$wgAvatarUploadPath` (string), default to "$wgUploadPath/avatars". This is the (web) path to avatars.
* `$wgAvatarUploadDirectory` (string), default to "$wgUploadDirectory/avatars". This is the storing path of avatars.
* You can set user rights: 
	* `avatarupload`: User need this right to upload ones' own avatar.
	* `avataradmin`: User need this right to delete others' avatars.

## How to use
* Set avatar in user preference, and then `$wgScriptPath/extensions/Avatar/avatar.php?user=username` will be redirected to your avatar.
* You can set alias for this php to make it shorter.
 
## Detailed API
* Uploading Avatar: No API provided yet, but one can post to `Special:UploadAvatar` (or its localized equivalent). The only form data required is `avatar`, which should be set to the data uri of the image.
* Displaying Avatar: This extension provides an entry point for MediaWiki `avatar.php`. This entry point produces result via a 302 redirect. This approach is used to maximize performance while still utilizing MediaWiki core. There are currently 4 available arguments.
    * `user` set to the user of who you want to enquery the avatar
    * `res` the preferred resolution of the avatar. Note that this is only a hint and the actual result might not be of the resolution. This parameter is valid only if `user` is set.
    * `ver` a version number which will be appended to the location field of redirection. Can be used to circumvent browser/CDN cache.
    * `nocache` if this parameter is set, then no `cache-control` header will be emitted.

## Extra resources
* If you are using Gadgets
    * If you want to display the avatar on the top-right navigation bar, you may find Gadget-ShowAvatar in example folder useful.
    * If you want to display avatars before user link, you may find Gadget-UserLinkAvatar in example folder useful.

## Upgrading from <1.0.0 to 1.0.0
* `wgScriptPath/extensions/Avatar/avatar.php?username` was changed to `wgScriptPath/extensions/Avatar/avatar.php?user=username`
* `wgScriptPath/extensions/Avatar/avatar.php?username/resolution` was changed to `wgScriptPath/extensions/Avatar/avatar.php?user=username&res=resolution`
* The change affects all Gadgets and depending extensions.
* Upgrading is easy: changing all occurrence of above url to the new fashion.
