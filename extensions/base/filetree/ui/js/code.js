// jQuery File Tree Plugin (heavily modified for bid-owl use)
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// Visit http://abeautifulsite.net/notebook.php?article=58 for more information
//
// Usage: $('.fileTreeDemo').fileTree( options, callback )
//
// Options:  root           - root folder to display; default = /
//           script         - location of the serverside AJAX file to use; default = jqueryFileTree.php
//           folderEvent    - event to trigger expand/collapse; default = click
//           expandSpeed    - default = 500 (ms); use -1 for no animation
//           collapseSpeed  - default = 500 (ms); use -1 for no animation
//           expandEasing   - easing function to use on expand (optional)
//           collapseEasing - easing function to use on collapse (optional)
//           multiFolder    - whether or not to limit the browser to one subfolder at a time
//           loadMessage    - Message to display while initial tree loads (can be HTML)
//
// History:
//
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// TERMS OF USE
// 
// This plugin is dual-licensed under the GNU General Public License and the MIT License and
// is copyright 2008 A Beautiful Site, LLC. 
//
if(jQuery) (function($){
        
	$.extend($.fn, {
		fileTree: function(o, h) {
			// Defaults
			if( !o ) var o = {};
			if( o.root == undefined ) o.root = '/';
			if( o.script == undefined ) o.script = 'jqueryFileTree.php';
			if( o.folderEvent == undefined ) o.folderEvent = 'click';
			if( o.expandSpeed == undefined ) o.expandSpeed= 500;
			if( o.collapseSpeed == undefined ) o.collapseSpeed= 500;
			if( o.expandEasing == undefined ) o.expandEasing = null;
			if( o.collapseEasing == undefined ) o.collapseEasing = null;
			if( o.multiFolder == undefined ) o.multiFolder = true;
			if( o.loadMessage == undefined ) o.loadMessage = 'Loading...';
			
			$(this).each( function() {
				
				function showTree(c, t) {
					$(c).addClass('wait');
					$(".jqueryFileTree.start").remove();
					$.post(o.script, { dir: t, command: 'Index', namespace: 'FileTree' }, function(data) {
						$(c).find('.start').html('');
						$(c).removeClass('wait').append(jQuery.parseJSON(data).html);
						if( o.root == t ) $(c).find('ul:hidden').show(); else $(c).find('ul:hidden').slideDown({ duration: o.expandSpeed, easing: o.expandEasing });
						bindTree(c);
					});
				}
				
				function bindTree(t) {
					$(t).find('li').bind(o.folderEvent, function(event) {
                                                if (this == event.target) {
                                                    if( $(this).hasClass('directory') ) {
                                                            if( $(this).hasClass('collapsed') ) {
                                                                    // Expand
                                                                    if( !o.multiFolder ) {
                                                                            $(this).parent().find('ul').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
                                                                            $(this).parent().find('li.directory').removeClass('expanded').addClass('collapsed');
                                                                    }
                                                                    $(this).find('ul').remove(); // cleanup
                                                                    showTree( $(this), escape($(this).find('a').attr('rel').match( /.*\// )) );
                                                                    $(this).removeClass('collapsed').addClass('expanded');
                                                            } else if ( $(this).hasClass('expanded') ) {
                                                                    // Collapse
                                                                    $(this).find('ul').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
                                                                    $(this).removeClass('expanded').addClass('collapsed');
                                                            }
                                                    } else {
                                                            h($(this).find('a').attr('rel'));
                                                    }
                                                } else {
                                                    window.location = $(this).find('a').attr('href');
                                                }
						return false;
					});
					// Prevent A from triggering the # on non-click events
                                        // if( o.folderEvent.toLowerCase != 'click' ) $(t).find('LI A').bind('click', function() { return false; });
				}
				// Loading message
				$(this).html('<ul class="jqueryFileTree start"><li class="wait">' + o.loadMessage + '<li></ul>');
				// Get the initial file list
				showTree( $(this), escape(o.root) );
			});
		}
	});
	
})(jQuery);

$(document).ready(function() {
	$('#treeDialog').dialog({ 
            title: 'Navigationsbaum', 
            autoOpen: false,
            position: filetreePosition,
            width: filetreeWidth,
            height: filetreeHeight,
            open: function() { 
                $(this).parent().css('z-index', '100');
                $(this).closest('.ui-dialog').css({position: 'absolute', left: filetreePosition[0], top: filetreePosition[1]});
                if (filetreeVisible != 1) {
                    filetreeVisible = 1;
                    
                    var params = {};
                    params.action = 'open';
                    sendRequest('UpdateDialog', params, '', 'data', '', '', 'FileTree'); 
                }
            },
            close: function() { 
                filetreeVisible = 0;
                
                var params = {};
                params.action = 'close';
                sendRequest('UpdateDialog', params, '', 'data', '', '', 'FileTree'); 
            },
            dragStop: function(event, ui) { 
                filetreePosition = [ ui.position.left, ui.position.top ];
                    
                var params = {};
                params.action = 'drag';
                params.top = ui.position.top;
                params.left = ui.position.left;
                sendRequest('UpdateDialog', params, '', 'data', '', '', 'FileTree'); 
            },
            resizeStop: function(event, ui) { 
                filetreeWidth = ui.size.width;
                filetreeHeight = ui.size.height;
                
                var params = {};
                params.action = 'resize';
                params.width = ui.size.width;
                params.height = ui.size.height;
                sendRequest('UpdateDialog', params, '', 'data', '', '', 'FileTree'); 
            }
        });
        
        if (filetreeVisible == 1) {
            if ($('#fileTree').html() == '') {
                    $('#fileTree').fileTree({
                        root: 'root' + filetreeCurrentID,
                        script: 'FileTree'
                    }, function(file) {
                        alert(file);
                    });
            };
            $('#treeDialog').dialog('open');
        }
});