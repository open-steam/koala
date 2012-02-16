(function( $ ){
	
	// Default configuration properties.
	var defaults = {
		classMarkup : "worksheet_markup",
		classMarkupEmpty : "worksheet_markupEmpty",
		classMarkupTemp: "worksheet_markupTemp",
		editBoxTitle: "Anmerkung",
		editBoxClass: "worksheet_markup_editBox",
		editBoxMarginTop: 2,
		onChange: undefined,
		readOnly: false
	};
	
	
	/**
	 * The worksheetMarkup object.
	 *
	 * @constructor
	 * @class worksheetMarkup
	 * @param e {HTMLElement} The element
	 * @param o {Object} A set of key/value pairs to set as configuration properties.
	 */
  	$.worksheetMarkup = function(e, o) {
	
		this.options = $.extend({}, defaults, o || {});

		this.el = e;
		
		this.editBox = undefined;
		
		
		var self = this;
		
		/* initial setup */
		this.setup = function() {

			/* add new popup box for editing the markup */
			self.editBox = document.createElement('div');
			$(self.editBox).addClass(self.options.editBoxClass);
			
			if (self.options.readOnly) {
				$(self.editBox).append(self.options.editBoxTitle+':<br /><div class="worksheetMarkup_content"></div>');
			} else {
				$(self.editBox).append(self.options.editBoxTitle+':<br /><textarea class="worksheetMarkup_content"></textarea>');
			}
			
			$("body").append(self.editBox);
			
			if (!self.options.readOnly) {
			
				$(self.el).bind('mouseup', function() {

					self.addMarkup();

				});

			
				$(self.editBox).children(".worksheetMarkup_content").bind("focusout", function() {

					self.hideBox();

				});
				
			} else {
				
				$("body").bind("mouseup", function(e) {

					if (!$(self.editBox).is(":hidden")) {
						
						if (e.target.className != self.options.editBoxClass && !$(e.target).parent().hasClass(self.options.editBoxClass)) {
							self.hideBox();
						}
					
					}
					
				});
				
			}
			
			$(self.el).find("."+self.options.classMarkup).each(function() {
			
				$(this).click(function(el) {

					self.editMarkup(this);

				});
				
			});
			
		};
		
		
		this.hideBox = function() {

			$(self.editBox).children(".worksheetMarkup_content").unbind("keyup");

			$(self.editBox).children(".worksheetMarkup_content").val("");
			$(self.editBox).hide();

			if (!self.options.readOnly) {
			
				$("."+self.options.classMarkupEmpty).each(function(index) {

					$(this).replaceWith($(this).html());

				});
				
			}
			
		}
		
		
		this.addMarkup = function() {
			
			if (self.options.readOnly) return false;
			
			var sel = window.getSelection ? window.getSelection() : document.selection.createRange(); // FF : IE

			if (sel != undefined && sel != "") {

				var newClasses = self.options.classMarkup+' '+self.options.classMarkupTemp;

			    if (sel.getRangeAt) { // thats for FF

					var range = sel.getRangeAt(0);
			    	var newNode = document.createElement("span");

			    	newNode.setAttribute('class', newClasses);
			    	range.surroundContents(newNode);

			    } else { //and thats for IE7

			    	sel.pasteHTML('<span class="'+newClasses+'">'+sel.htmlText+'</span>');

			    }

				var markupEl = $("."+self.options.classMarkupTemp);
				markupEl.removeClass(self.options.classMarkupTemp);

				markupEl.click(function(el) {

					self.editMarkup(this);

				});
				
				self.editMarkup(markupEl);
				
			}
			
		};
		
		
		this.editMarkup = function(markup) {

			var posLeft = $(markup).offset().left;
			var posTop = $(markup).offset().top;

			posTop = posTop+$(markup).height()+self.options.editBoxMarginTop;

			$(self.editBox).css("left", posLeft+"px");
			$(self.editBox).css("top", posTop+"px");

			
			if (self.options.readOnly) {
				$(self.editBox).children(".worksheetMarkup_content").html($(markup).attr("title"));
			} else {
				$(self.editBox).children(".worksheetMarkup_content").val($(markup).attr("title"));
			}

			if (!self.options.readOnly) {
			
				$(self.editBox).children(".worksheetMarkup_content").bind("keyup", function() {

					var str = jQuery.trim($(self.editBox).children(".worksheetMarkup_content").val());

					$(markup).attr("title", str);

					if (str == "") {
						$(markup).addClass(self.options.classMarkupEmpty);
					} else {
						$(markup).removeClass(self.options.classMarkupEmpty);
					}

					if (self.options.onChange != undefined) {
						self.options.onChange();
					}

				});

				$(self.editBox).children(".worksheetMarkup_content").keyup();
			
			}

			$(self.editBox).show();
			
			if (!self.options.readOnly) {
				$(self.editBox).children(".worksheetMarkup_content").focus();
			}
			
		};
		

		/* call setup function */
		this.setup();
		
	};

	var $ts = $.worksheetMarkup;

	/**
	 * Creates a worksheetMarkup element
	 *
	 * @example $("#myelement").worksheetMarkup();
	 * @method worksheetMarkup
	 * @return jQuery
	 * @param o {Hash|String} A set of key/value pairs to set as configuration properties or a method name to call on a formerly created instance.
	 */
	
	$.fn.worksheetMarkup = function(o) {
		if (typeof o == 'string') {
			var instance = $(this).data('worksheetMarkup'), args = Array.prototype.slice.call(arguments, 1);
			return instance[o].apply(instance, args);
		} else {
			return this.each(function() {
				var instance = $(this).data('worksheetMarkup');
				if (instance) {
					if (o) {
						$.extend(instance.options, o);
					}
					instance.reload();
				} else {
					$(this).data('worksheetMarkup', new $ts(this, o));
				}
			});
		}
	};
	
})( jQuery );