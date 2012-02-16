(function( $ ){
	
	// Default configuration properties.
	var defaults = {

	};
	
	
	/**
	 * The worksheetPreventChanges object.
	 *
	 * @constructor
	 * @class worksheetPreventChanges
	 * @param e {HTMLElement} The element
	 * @param o {Object} A set of key/value pairs to set as configuration properties.
	 */
  	$.worksheetPreventChanges = function(e, o) {
	
		this.options = $.extend({}, defaults, o || {});

		this.el = e;
		
		var self = this;
		
		
		/* initial setup */
		this.setup = function() {
			
			$(self.el).find("input[type=checkbox]").bind("click", function(e) {

				this.blur();
				e.preventDefault();
				
			});
			
			$(self.el).find("input[type=input], textarea, input[type=password]").bind("focus", function(e) {
				
				this.blur();
				
			});
			
		};
		


		/* call setup function */
		this.setup();
		
	};

	var $ts = $.worksheetPreventChanges;

	/**
	 * Prevents the elements form fileds to be changed
	 *
	 * @example $("#myelement").worksheetPreventChanges();
	 * @method worksheetPreventChanges
	 * @return jQuery
	 * @param o {Hash|String} A set of key/value pairs to set as configuration properties or a method name to call on a formerly created instance.
	 */
	
	$.fn.worksheetPreventChanges = function(o) {
		if (typeof o == 'string') {
			var instance = $(this).data('worksheetPreventChanges'), args = Array.prototype.slice.call(arguments, 1);
			return instance[o].apply(instance, args);
		} else {
			return this.each(function() {
				var instance = $(this).data('worksheetPreventChanges');
				if (instance) {
					if (o) {
						$.extend(instance.options, o);
					}
					instance.reload();
				} else {
					$(this).data('worksheetPreventChanges', new $ts(this, o));
				}
			});
		}
	};
	
})( jQuery );