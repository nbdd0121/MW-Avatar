$('.mw-userlink').each(function(_, item) {
	item = $(item);
	item.prepend($('<img/>').addClass('userlink-avatar').attr('src', mw.config.get('wgScriptPath') + '/extensions/Avatar/avatar.php?user=' + item.text()));
});
