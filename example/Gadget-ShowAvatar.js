var img = $('<img>').attr('src', mw.config.get('wgScriptPath') + '/extensions/Avatar/avatar.php?user=' + mw.user.id());
var link = $('<a>').attr('href', mw.util.getUrl('Special:UploadAvatar')).append(img);
$('#pt-userpage').before($('<li id="pt-avatar"></li>').append(link));
