jQuery( document ).ready(function() {
	jQuery('a.toggle-settings').bind('click', function(e) {
		var btn = jQuery(this);
		jQuery(this).parent().parent().find(".inside").slideToggle("slow", function() {
			if(jQuery(btn).html() == '(anzeigen)') {
				jQuery(btn).html('(ausblenden)');
			} else {
				jQuery(btn).html('(anzeigen)');
			}
		});
		e.preventDefault();
	});
});
console.log('load');
