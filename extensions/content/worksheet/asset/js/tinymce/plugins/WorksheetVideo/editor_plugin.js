/**
 * editor_plugin.js
 *
 * TinyMCE extension for worksheet extension
 * Copyright: Tobias Kempkensteffen <tobias.kempkensteffen@gmail.com>
 */

var editor_plugin_name = 'WorksheetVideo';

(function() {

	tinymce.create('tinymce.plugins.'+editor_plugin_name+'Plugin', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('mce'+editor_plugin_name, function() {
				
				
				
				
				var re = new RegExp('<!-- Worksheet_\\[([^\\]]*)\\]\\[([^\\]]*)\\]\\[([^\\]]*)\\] -->', "g");
				var m = re.exec(ed.selection.getContent());

			  	if (m == null) {
					var param = "";
			  	} else {
					var param = m[1];
				}
				
				var popup_url = url + '/plugin.php?param=a'+escape(param);
				
				//open popup
				ed.windowManager.open({
					file : popup_url,
					width : 700,
					height : 400,
					inline : 1
				}, {
					plugin_url : url // Plugin absolute URL
				});
				
			});

			// Register button
			ed.addButton(editor_plugin_name, {
				title : 'Video einfügen',
				cmd : 'mce'+editor_plugin_name,
				image : url + '/img/icon.gif'
			});

			// Add a node change handler, selects the button in the UI when a image is selected
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive(editor_plugin_name, n.nodeName == 'IMG');
			});

		
	
			ed.onInit.add(function() {

	                if (ed.settings.content_css !== false)
	                        ed.dom.loadCSS(url + "/css/content.css");

	                if (ed.theme.onResolveName) {
	                        ed.theme.onResolveName.add(function(th, o) {
	                                if (o.node.nodeName == 'IMG' && ed.dom.hasClass(o.node, editor_plugin_name))
	                                        o.name = editor_plugin_name;
	                        });
	                }

	        });



	        ed.onClick.add(function(ed, e) {
	                e = e.target;

	                if (e.nodeName === 'IMG' && ed.dom.hasClass(e, 'mce'+editor_plugin_name)) {
						
						ed.selection.select(e);
	
					}
	
	        });


	        ed.onNodeChange.add(function(ed, cm, n) {
					
	                cm.setActive(editor_plugin_name, n.nodeName === 'IMG' && ed.dom.hasClass(n, 'mce'+editor_plugin_name));
	
	        });

	        ed.onBeforeSetContent.add(function(ed, o) {
	        //called before the contents gets serialized and placed in the editor

	                 //find placeholder <!-- simplexmodule[parameter1][parameter2] -->
	                 var re = new RegExp('<!-- Worksheet_\\[([^\\]]*)\\]\\[([^\\]]*)\\]\\[([^\\]]*)\\] -->', "g");

	                 //replace placeholder with <img>-tag, so that there is a visible element in the editor
	                 o.content = o.content.replace(re,'<img src="' + url + '/img/test.png" class="mce'+editor_plugin_name+'" alt="$1" width="$2" height="$3" style="width: $2px; height: $3px;" />');
																												//mceItemNoResize

	        });


			//This event gets executed after a HTML fragment has been serialized into a HTML string. This event enables you to do modifications to the HTML string like regexp replaces etc.
	        ed.onPostProcess.add(function(ed, o) {
	        
	                if (o.get)
	                        o.content = o.content.replace(/<img[^>]+>/g, function(im) {
	                   //replace <img>-tag with placeholder

	                                if (im.indexOf('class="mce'+editor_plugin_name+'"') !== -1) {

										var re = new RegExp('alt="([^"]+)"');
										var m = re.exec(im);

										if (m == null) {
											var alt = "";
										} else {
											var alt = m[1];
										}
										
										var re = new RegExp('width="([^"]+)"');
										var m = re.exec(im);

										if (m == null) {
											var width = "";
										} else {
											var width = m[1];
										}
										
										var re = new RegExp('height="([^"]+)"');
										var m = re.exec(im);

										if (m == null) {
											var height = "";
										} else {
											var height = m[1];
										}
	
										
										
										//replace with placeholder:
										im = '<!-- Worksheet_['+alt+']['+width+']['+height+'] -->';

										return im;

									} else {
										return im;
									}

	                        });
	        });



			},
			

			/**
			 * Creates control instances based in the incomming name. This method is normally not
			 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
			 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
			 * method can be used to create those.
			 *
			 * @param {String} n Name of the control to create.
			 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
			 * @return {tinymce.ui.Control} New control instance or null if no control was created.
			 */
			createControl : function(n, cm) {
				return null;
			},

			/**
			 * Returns information about the plugin as a name/value array.
			 * The current keys are longname, author, authorurl, infourl and version.
			 *
			 * @return {Object} Name/value array containing information about the plugin.
			 */
			getInfo : function() {
				return {
					longname : editor_plugin_name+' Plugin',
					author : 'Tobias Kempkensteffen',
					authorurl : '',
					infourl : '',
					version : "1.0"
				};
			}
			
			
			
	});

	// Register plugin
	tinymce.PluginManager.add(editor_plugin_name, tinymce.plugins.WorksheetVideoPlugin);
})();