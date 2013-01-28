(function() {
        tinymce.create('tinymce.plugins.bidTooltipPlugin', {
                init : function(ed, url) {

						//classname
                        var cls = 'mcebid_tooltip';
                        

                        // Register commands
                        ed.addCommand('mcebid_tooltip', function() {

							if (ed.selection.getContent() != "" || ed.selection.getNode().tagName.toLowerCase() == "acronym") {
						
                                //OPEN POPUP
                                ed.windowManager.open({
                                        file:url+'/popup.html',
                                        width:500,
                                        height:350,
                                        inline:1
                                },
                                {
                                        plugin_url:url,
                                        some_custom_arg:'custom arg'
                                });
								
							}

                        });

                        // Register buttons
                        ed.addButton('bid_tooltip', {title : 'Textannotation', cmd : cls, image: url + '/img/icon.gif'});

                        ed.onInit.add(function() {
                                if (ed.settings.content_css !== false)
                                        ed.dom.loadCSS(url + "/css/content.css");
                        });


                        ed.onNodeChange.add(function(ed, cm, n) {
                                cm.setActive('bid_tooltip', n.nodeName.toLowerCase() == 'acronym');
                        });

                },

                getInfo : function() {
                        return {
                                longname : 'bid_tooltip',
                                author : 'Tobias Kempkensteffen',
                                authorurl : 'http://tobias-kempkensteffen.de',
                                infourl : '',
                                version : '1.0'
                        };
                }
        });

        // Register plugin
        tinymce.PluginManager.add('bid_tooltip', tinymce.plugins.bidTooltipPlugin);
})();