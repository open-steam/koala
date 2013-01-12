var bid_tooltip_title;

jQuery(function() {
	
	jQuery("acronym").each(function(index, el) {
		
		jQuery(el).hover(function() {
			/* in */
			
			var text = jQuery(el).attr("title");
			jQuery(el).attr("title", "");
			bid_tooltip_title = text;
			
			jQuery("body").append('<div id="bid_tooltip">'+text+'</div>');
			
			jQuery("#bid_tooltip").css("left", jQuery(el).offset().left+jQuery(el).outerWidth()/2);
			jQuery("#bid_tooltip").css("top", jQuery(el).offset().top+jQuery(el).outerHeight());
			
		}, function() {
			/* out */
			jQuery("#bid_tooltip").remove();
			jQuery(el).attr("title", bid_tooltip_title);
		});
		
	});
	
});