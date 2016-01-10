$('.mw-userlink').each(function(_, item) {
	item = $(item);
	item.prepend($('<img/>').addClass('userlink-avatar').attr('src', mw.util.getUrl('Special:Avatar/' + item.text())));
});
