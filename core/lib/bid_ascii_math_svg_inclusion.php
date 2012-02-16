<?php
  require_once("config.php");

  $ascii_math_svg_include = PHP_EOL .
    '<script type="text/javascript" src="' . $config_webserver_ip . 
    '/javascript/tinymce/jscripts/tiny_mce/plugins/asciimath/js/ASCIIMathMLwFallback.js">' .
    '</script>' . PHP_EOL .
     '<script type="text/javascript" src="' . $config_webserver_ip . 
     '/javascript/tinymce/jscripts/tiny_mce/plugins/asciisvg/js/ASCIIsvgPI.js">' .
     '</script>' . PHP_EOL .
    '<script type="text/javascript">' . PHP_EOL .
      "var AScgiloc = '" . $config_webserver_ip . 
      "/tools/asciisvg/svgimg.php';" . PHP_EOL .
      "var AMTcgiloc = '" . $config_cgi_ip . 
      "/mimetex.cgi';" . PHP_EOL .
    '</script>' . PHP_EOL;

  function add_ascii_math_svg_include($doc_content) {
    global $config_webserver_ip;
    global $ascii_math_svg_include;

    $pos = strpos($doc_content, '<head>');
    if ($pos === false) {
      $pos = strpos($doc_content, '<html>');
      if ($pos !== false) {
        $doc_content = substr($doc_content, 0,$pos+6) . PHP_EOL .
          "<head>" .
          $ascii_math_svg_include .
          "</head>" . PHP_EOL .
          substr($doc_content, $pos+6, strlen($doc_content));
      }
    }
    else {
      $doc_content = substr($doc_content, 0, $pos+6) .
        $ascii_math_svg_include .
        substr($doc_content, $pos+6, strlen($doc_content));
    }
    return $doc_content;
  }
?>
