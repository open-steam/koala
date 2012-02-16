<?php
include_once( "../../etc/koala.conf.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

$js1 = <<< END
$(function () {
    $("#demo2").jstree({
        "core" : { "initially_open" : [ "root" ] },
        "html_data" : {
            "data" : "<li id='root'><a href='#'>Root node</a><ul><li><a href='#'>Child node</a></li></ul></li>"
        },
        "plugins" : [ "themes", "html_data" ]
    });
});


END;

$css = <<< END

END;
$portal->add_css_style($css);
$portal->add_javascript_src("NERD!", "./jquery-1.4.2.min.js");
$portal->add_javascript_src("NERD!", "./jquery.jstree.min.js");

//$portal->add_javascript_code("NERD!", $js1);




$portal->set_page_main(
	"steam-Explorer - Test2 - Mit jquery",
	"<link rel=\"stylesheet\" type=\"text/css\" href=\"./themes/default/style.css\" /><script type=\"text/javascript\">" . $js1 ."</script><div id=\"tree\"></div>
	<div id=\"tree2\"></div>",
	""
);

$portal->show_html();
?>