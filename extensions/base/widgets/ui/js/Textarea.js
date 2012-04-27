
function widgets_textarea_save_success(elementId, response) {
	var widget = jQuery("#" + elementId);
	deJSON = jQuery.parseJSON(response.responseText);
	if (deJSON) {
		data = deJSON.data;
		if (widget.length !== 0) {
			widget.removeClass("saving");
			if (data.error === "none") {	
				if (widget.find("textarea").hasClass("mce-full") || widget.find("textarea").hasClass("mce-small")) {
					tinyMCE.activeEditor.isNotDirty = 1;
					tinyMCE.activeEditor.setContent(data.newValue);
					tinyMCE.activeEditor.undoManager.clear();
					$(tinyMCE.activeEditor.getBody()).removeClass("mceNonEditable");
					//tinyMCE.activeEditor.getBody().click();
					tinyMCE.activeEditor.focus();
				} else if (widget.find("textarea").hasClass("plain")) {
					widget.find("textarea").val(data.newValue);
				}
				widget.removeClass("dirty");
				widget.addClass("undo");
				widget.addClass("saved");
				widget.attr("oldValue", widget.attr("value"));
				widget.attr("value", data.newValue);
				$(window).unbind('beforeunload');
			} else {
				widget.addClass("error");
				widget.removeClass("dirty");
				widget.find(".icon.error").title = data.error;
			}
		}
	}
}

(function($){
	  var sendFunction;
	
	  var methods = {
	    init : function(options) {
	    	var element = this;
	    	var value = decodeURIComponent(options.value);
	    	element.attr("value", value);
	    	element.attr("oldValue", value);
	    	sendFunction = options.sendFunction;
	    	var mce_defaults = {
	    			mode : "specific_textareas",
	    			
	    			// General options
					theme : "advanced",
					content_css : "{PATH_URL}widgets/css/tinymce.css",
					skin: "o2k7",
					remove_linebreaks: false,
				    convert_urls : false,
				    verify_html: "false",
					language: "de",
					
					// Theme options
					theme_advanced_buttons3 : "",
					theme_advanced_buttons4 : "",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					theme_advanced_statusbar_location : "none",
					theme_advanced_resizing : false,
					
					handle_event_callback: function(e) {if (e.type === "keyup"  && tinyMCE.activeEditor.isDirty()) {element.addClass("dirty");$(window).bind('beforeunload', function() {return 'LeaveMessage';});}; if(!tinyMCE.activeEditor.isDirty()) {element.removeClass("dirty")}},
					oninit : function(e) {tinyMCE.activeEditor.setContent(value), tinyMCE.activeEditor.isNotDirty = 1},
					setup :  function(e) {
						setInterval(function() {if (tinyMCE.activeEditor && tinyMCE.activeEditor.isDirty()) {element.addClass("dirty");$(window).bind('beforeunload', function() {return 'LeaveMessage';});}; if(tinyMCE.activeEditor && !tinyMCE.activeEditor.isDirty()) {element.removeClass("dirty")}}, 2000 );
					}
	    	};
	    	
	    	if (element.find("textarea").hasClass("mce-small")) {
	    		load("mce", function() {
					tinyMCE.init($.extend({
						editor_selector: "mce-small", 
						plugins : "emotions,paste,noneditable",
						// Theme options
						theme_advanced_buttons1 : "bold,italic,underline,|,bullist,numlist,|,link,unlink,|,forecolor,removeformat,|,undo,redo,pasteword",
						theme_advanced_buttons2 : ""
					}, mce_defaults));
	    		});
	    	} else if (element.find("textarea").hasClass("mce-full")) {
	    		
	    		//protect unsaved text
	    		/*
	    		$(window).unload(function(){
	    		var dirtyTextareas = jQuery('#content').find('.widget.textarea.dirty');
	    		if (dirtyTextareas.length > 0) {
	    			//$(window).unbind( “unload” );
	    			//window.onbeforeunload = state ? function() { return "text1"; } : null;
	    			//return false;
	    			
	    			if (confirm('Sollen alle nicht gespeicherte Daten gesichert werden?')) {
	    				$(dirtyTextareas[0]).textarea('save');
	    				$(dirtyTextareas[0]).addClass('saved');
	    				$(dirtyTextareas[0]).removeClass('dirty');
	    				
	    				//saveing
	    				if (element.find("textarea").hasClass("mce-full") || element.find("textarea").hasClass("mce-small")) {
			    			$(tinyMCE.activeEditor.getBody()).addClass("mceNonEditable"); 
			    			var value = tinyMCE.activeEditor.getContent(); 
		    			} else if (element.find("textarea").hasClass("plain")) {
		    				var value = element.find("textarea").val(); 
		    			}
		    			//this.addClass("saving"); 
	    				sendFunction(value);
	    			}else{
	    				//nicht speichern
	    			}
	    			
	    		}
	    		});
	    		*/
	    		
	    		//load tinymce
	    		load("mce", function() {
	    			tinyMCE.init($.extend({
						editor_selector: "mce-full", 
						plugins : 'searchreplace,paste,table,emotions,asciimath,asciisvg,media,noneditable',
						// Theme options
						theme_advanced_buttons1 : "formatselect,fontsizeselect,|, bold,italic,underline,sub,sup,|,bullist,numlist,table,tablecontrols,visualaid,|,justifyleft,justifycenter,justifyright,justifyfull,hr,|,forecolor,backcolor",
						theme_advanced_buttons2 : "undo,redo,pasteword,|,removeformat,|,search,|,charmap,|,emotions,image,media,link,unlink,|,asciimathcharmap,asciisvg",
						theme_advanced_fonts : "Times New Roman=times new roman,times,serif;Arial=arial,helvetica,sans-serif;Courier New=courier new,courier,monospace;AkrutiKndPadmini=Akpdmi-n",
						theme_advanced_blockformats : "p,pre,h1,h2,h3,h4",
						//plugins
						fix_table_elements : true,
                                     
                                                verify_html : false,

                                                AScgiloc : 'http://www.imathas.com/imathas/filter/graph/svgimg.php',
						ASdloc : pathUrl + '/styles/standard/javascript/tinymce-jquery/jscripts/tiny_mce/plugins/asciisvg/js/d.svg',
						fullscreen_new_window : true,       
						fullscreen_settings : {
							theme_advanced_path_location : "top"
						}
					}, mce_defaults));
	    		});
	    	} else if (element.find("textarea").hasClass("plain")) {
	    		element.find("textarea").val(value);
	    		element.find("textarea").bind('keyup', function() { element.addClass("dirty"); });
	    	}
	    },
	    save : function() { 
	    			var element = this;
	    			if (element.find("textarea").hasClass("mce-full") || element.find("textarea").hasClass("mce-small")) {
		    			$(tinyMCE.activeEditor.getBody()).addClass("mceNonEditable"); 
		    			//tinyMCE.activeEditor.getBody().click(); 
		    			var value = tinyMCE.activeEditor.getContent(); 
	    			} else if (element.find("textarea").hasClass("plain")) {
	    				var value = element.find("textarea").val(); 
	    			}
	    			this.addClass("saving");
	    			$(window).unbind('beforeunload');
	    			sendFunction(value);
	    		},
	    undo : function() {
	    			var element = this;
	    			if (element.find("textarea").hasClass("mce-full") || element.find("textarea").hasClass("mce-small")) {
	    				$(tinyMCE.activeEditor.getBody()).addClass("mceNonEditable");
	    				//tinyMCE.activeEditor.getBody().click(); 
	    				var value = this.attr("oldValue");
	    			} else if (element.find("textarea").hasClass("plain")) {
	    				var value = this.attr("oldValue");
	    			}
	    			this.addClass("saving");
	    			$(window).unbind('beforeunload');
	    			sendFunction(value)
	    		},
	  };

	  $.fn.textarea = function( method ) {
	    // Method calling logic
	    if ( methods[method] ) {
	      return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
	    } else if ( typeof method === 'object' || ! method ) {
	      return methods.init.apply( this, arguments );
	    } else {
	      $.error( 'Method ' +  method + ' does not exist on jQuery.textarea' );
	    }    
	  
	  };

})( jQuery );	