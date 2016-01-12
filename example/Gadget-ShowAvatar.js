$('#pt-userpage').before($('<li id="pt-avatar"></li>').append($('<img/>').attr('src', mw.config.get('wgScriptPath') + '/extensions/Avatar/avatar.php?' + mw.user.id())));
