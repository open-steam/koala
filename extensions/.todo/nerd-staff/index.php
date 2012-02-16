<?php
include_once( "../../etc/koala.conf.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

$js1 = <<< END
/*!
 * Ext JS Library 3.2.1
 * Copyright(c) 2006-2010 Ext JS, Inc.
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
var TreeTest = function(){
    // shorthand
    var Tree = Ext.tree;
    
    return {
        init : function(){
            // yui-ext tree
            var tree = new Tree.TreePanel({
                animate:true, 
                autoScroll:true,
                loader: new Tree.TreeLoader({dataUrl:'get-nodes.php'}),
                enableDD:true,
                containerScroll: true,
                border: false,
                width: 250,
                height: 300,
                dropConfig: {appendOnly:true}
            });
            
            // add a tree sorter in folder mode
            new Tree.TreeSorter(tree, {folderSort:true});
            
            // set the root node
            var root = new Tree.AsyncTreeNode({
                text: 'My Home', 
                draggable:false, // disable root node dragging
                id:'/home/root'
            });
            tree.setRootNode(root);
            
            // render the tree
            tree.render('tree');
            
            root.expand(false, /*no anim*/ false);
            
            //-------------------------------------------------------------
            
            // ExtJS tree            
            var tree2 = new Tree.TreePanel({
                animate:true,
                autoScroll:true,
                //rootVisible: false,
                loader: new Ext.tree.TreeLoader({
                    dataUrl:'get-nodes.php',
                    baseParams: {path:'/home/steam'} // custom http params
                }),
                containerScroll: true,
                border: false,
                width: 250,
                height: 300,
                enableDD:true,
                dropConfig: {appendOnly:true}
            });
            
            // add a tree sorter in folder mode
            new Tree.TreeSorter(tree2, {folderSort:true});
            
            // add the root node
            var root2 = new Tree.AsyncTreeNode({
                text: 'steam Home', 
                draggable:false, 
                id:'/home/steam'
            });
            tree2.setRootNode(root2);
            tree2.render('tree2');
            
            root2.expand(false, /*no anim*/ false);
        }
    };
}();

Ext.EventManager.onDocumentReady(TreeTest.init, TreeTest, true);
END;

$css = <<< END
    #tree, #tree2 {
    	float:left;
    	margin:20px;
    	border:1px solid #c3c3c3;
    	overflow:auto;
    }
    .folder .x-tree-node-icon{
		background:transparent url(../../resources/images/default/tree/folder.gif);
	}
	.x-tree-node-expanded .x-tree-node-icon{
		background:transparent url(../../resources/images/default/tree/folder-open.gif);
	}
END;
$portal->add_css_style($css);
$portal->add_javascript_src("NERD!", "./ext-base.js");
$portal->add_javascript_src("NERD!", "./ext-all.js");

//$portal->add_javascript_code("NERD!", $js1);




$portal->set_page_main(
	"steam-Explorer - Test1 - Mit Ext-JS",
	"<link rel=\"stylesheet\" type=\"text/css\" href=\"./css/ext-all.css\" /><script type=\"text/javascript\">" . $js1 ."</script><div id=\"tree\"></div>
	<div id=\"tree2\"></div>",
	""
);

$portal->show_html();
?>