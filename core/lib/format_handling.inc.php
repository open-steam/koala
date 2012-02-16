<?php

function iso_to_unix( $string )
{
        $values = explode( "-", $string );
        return mktime( 0, 0, 0, $values[ 1 ], $values[ 2], $values[ 0 ] );
}

function unix_to_iso( $timestamp )
{
        return strftime( "%Y-%m-%d", $timestamp );
}

function str_to_timestamp( $date_iso, $date_time )
{
        $ts_date = strtotime( $date_iso );
        $time    = explode( ":", $date_time );
        return strtotime(
                        "+" . $time[ 0 ] . " hours " .
                        "+" . $time[ 1 ] . " minutes",
                        $ts_date
                        );
}

function how_long_ago( $timestamp )
{
        $offset = time() - $timestamp;
        switch( TRUE )
        {
                case ( $offset < 120 ): // less then two minutes
                        return str_replace( "%s", $offset, gettext( "%s seconds ago" ) );
                        break;
                case ( $offset <  7200 ):       // less then two hours
                        $t = floor( $offset / 60 );
                        return str_replace( "%m", $t, gettext( "%m minutes ago" ) );
                        break;
                case ( $offset < 172800 ):      // less then two days
                        $t = floor( $offset / 3600 );
                        return str_replace( "%h", $t, gettext( "%h hours ago" ) );
                        break;
                case ( $offset < 1209600 ):     // less then two weeks
                        $t = floor( $offset / 86400 );
                        return str_replace( "%d", $t, gettext( "%d days ago" ) );
                        break;
                case ( $offset < 12096000 ):    // less than twenty weeks
                        $t = floor( $offset / 604800 );
                        return str_replace( "%w", $t, gettext( "%w weeks ago" ) );
                        break;
                default:  // more than 20 weeks
                        return strftime( "%x", $timestamp );
                        break;
        }
}

function time_left( $timestamp )
{
        $timeleft = $timestamp - time();
        if ( $timeleft < 0 )
        {
                return how_long_ago( $timestamp );
        }
        $days = ceil( $timeleft / 86400 );
        return str_replace( "%d", $days, gettext( "%d days left" ) );
}

function is_phone_number( $pStringNumber)
{
	return preg_match( "/^(\+|00|0|\(|[0-9])([0-9]*|\W|-|\(|\))*$/", $pStringNumber );
}

/**
 * Test if Matrikelnummer is valid
 * matrikel modulo 11 is 0; if modulo is 1, the last digit (crc) is 0.
 * matrikels start with 3 or 6.
 * @author Studinfo Staff
 */
function check_matriculation_number( $mnr )
{
  $first = substr($mnr, 0, 1);
  $prf   = substr($mnr, strlen($mnr)-1, 1);
  $mod   = $mnr % 11;
  return (($first==3 || $first==6) && ($mod==0 ? TRUE : ($mod==1 && $prf==0)));
}

function get_formatted_filesize( $filesize )
{
        $scale = array( "B", "KB", "MB", "GB", "TB" );
        $s = $scale[ 0 ];
        for( $i=1; (( $i < count( $scale ) ) && ( $filesize >= 1024 ) ); $i++ )
        {
                $filesize = $filesize / 1024;
                $s = $scale[ $i ];
        }
        return round( $filesize, 2 ) . "&nbsp;" . $s;
}

function get_formatted_output( $text, $chars = 0, $br ="\n" )
{
        return BBCode( $text );
}

function format_length( $text, $chars = 0, $br = "\n" )
{
	if ( $chars > 0 )
          {
          $text = wordwrap( $text, $chars, $br, 1 );
          }
          $text = stripslashes( nl2br( $text ) );
        // URL PATTERN 1
        $text = preg_replace( "#(( www))#i", "http://www", $text  );
        $text = preg_replace(
        "#(^|[^\"=]{1})(www.|https://|http://|ftp://|mailto:|news:)([^\s<>]+)([\s\n<>]|$)#sm", "\\1 <a target=\"_blank\" href=\"\\2\\3\">\\2\\3</a>\\4",
        $text
        );

        return $text;
}

function BBCode($Text)
{
        // Replace any html brackets with HTML Entities to prevent executing HTML or script
        // Don't use strip_tags here because it breaks [url] search by replacing & with amp
        $Text = str_replace("<", "&lt;", $Text);
        $Text = str_replace(">", "&gt;", $Text);

        // Convert new line chars to html <br /> tags
        $Text = nl2br($Text);

        // Set up the parameters for a URL search string
        $URLSearchString = " a-zA-Z0-9\:\/\-\?\&\.\=\_\~\#\'\,";
        // Set up the parameters for a MAIL search string
        $MAILSearchString = $URLSearchString . " a-zA-Z0-9\.@";

        // Perform URL Search
        $Text = preg_replace("/\[url\]([$URLSearchString]*)\[\/url\]/", '<a href="$1" target="_blank">$1</a>', $Text);
        $Text = preg_replace("(\[url\=([$URLSearchString]*)\](.+?)\[/url\])", '<a href="$1" target="_blank">$2</a>', $Text);
        //$Text = preg_replace("(\[url\=([$URLSearchString]*)\]([$URLSearchString]*)\[/url\])", '<a href="$1" target="_blank">$2</a>', $Text);

        // Perform MAIL Search
        $Text = preg_replace("(\[mail\]([$MAILSearchString]*)\[/mail\])", '<a href="mailto:$1">$1</a>', $Text);
        $Text = preg_replace("/\[mail\=([$MAILSearchString]*)\](.+?)\[\/mail\]/", '<a href="mailto:$1">$2</a>', $Text);

        // Check for bold text
        $Text = preg_replace("(\[b\](.+?)\[\/b])is",'<b>$1</b>',$Text);

        // Check for Italics text
        $Text = preg_replace("(\[i\](.+?)\[\/i\])is",'<i>$1</i>',$Text);

        // Check for Underline text
        $Text = preg_replace("(\[u\](.+?)\[\/u\])is",'<u>$1</u>',$Text);

        // Check for strike-through text
        $Text = preg_replace("(\[s\](.+?)\[\/s\])is",'<span style="text-decoration: line-through;">$1</span>',$Text);

        // Check for over-line text
        $Text = preg_replace("(\[o\](.+?)\[\/o\])is",'<span style="text-decoration: overline;">$1</span>',$Text);

        // Check for colored text
        $Text = preg_replace("(\[color=(.+?)\](.+?)\[\/color\])is","<span style=\"color: $1\">$2</span>",$Text);

        // Check for sized text
        $Text = preg_replace("(\[size=(.+?)\](.+?)\[\/size\])is","<span style=\"font-size: $1px\">$2</span>",$Text);

        // Check for list text
        $Text = preg_replace("/\[list\](.+?)\[\/list\]/is", '<ul class="listbullet">$1</ul>' ,$Text);
        $Text = preg_replace("/\[list=1\](.+?)\[\/list\]/is", '<ul class="listdecimal">$1</ul>' ,$Text);
        $Text = preg_replace("/\[list=i\](.+?)\[\/list\]/s", '<ul class="listlowerroman">$1</ul>' ,$Text);
        $Text = preg_replace("/\[list=I\](.+?)\[\/list\]/s", '<ul class="listupperroman">$1</ul>' ,$Text);
        $Text = preg_replace("/\[list=a\](.+?)\[\/list\]/s", '<ul class="listloweralpha">$1</ul>' ,$Text);
        $Text = preg_replace("/\[list=A\](.+?)\[\/list\]/s", '<ul class="listupperalpha">$1</ul>' ,$Text);
        $Text = str_replace("[*]", "<li>", $Text);

        // Check for font change text
        $Text = preg_replace("(\[font=(.+?)\](.+?)\[\/font\])","<span style=\"font-family: $1;\">$2</span>",$Text);

        // Declare the format for [code] layout
        $CodeLayout = '<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                <tr>
                <td class="quotecodeheader"> Code:</td>
                </tr>
                <tr>
                <td class="codebody">$1</td>
                </tr>
                </table>';
        // Check for [code] text
        $Text = preg_replace("/\[code\](.+?)\[\/code\]/is","$CodeLayout", $Text);

        // Declare the format for [quote] layout
        $QuoteLayout = '<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                <tr>
                <td class="quotecodeheader"> Quote:</td>
                </tr>
                <tr>
                <td class="quotebody">$1</td>
                </tr>
                </table>';

        // Check for [code] text
        $Text = preg_replace("/\[quote\](.+?)\[\/quote\]/is","$QuoteLayout", $Text);

        // Images
        // [img]pathtoimage[/img]
        $Text = preg_replace("/\[img\](.+?)\[\/img\]/", '<img src="$1">', $Text);

        // [img=widthxheight]image source[/img]
        $Text = preg_replace("/\[img\=([0-9]*)x([0-9]*)\](.+?)\[\/img\]/", '<img src="$3" height="$2" width="$1">', $Text);

        return $Text;
}

function h($text)
{
        // convenience function to make fixing XSS vulnaribilities easier
        return htmlentities($text, ENT_QUOTES, "UTF-8");
}

function unhtmlentities($string)
{
        // Ersetzen numerischer Darstellungen
        $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
        $string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);
        // Ersetzen benannter Zeichen
        $trans_tbl = get_html_translation_table(HTML_ENTITIES);
        $trans_tbl = array_flip($trans_tbl);
        return strtr($string, $trans_tbl);
}

function readable_filesize ( $size_value ) {
	if ( !is_numeric( $size_value ) ) return $size_value;
	$size_value = (int)$size_value;
	if ( $size_value < 1024 ) return (string)$size_value . " B";
	if ( ($size_value = $size_value >> 10) < 1024 ) return (string)$size_value . " KB";
	if ( ($size_value = $size_value >> 10) < 1024 ) return (string)$size_value . " MB";
	if ( ($size_value = $size_value >> 10) < 1024 ) return (string)$size_value . " GB";
	return $size_value . " TB";
}

function parse_filesize ( $size_str ) {
	sscanf( (string)$size_str, "%f%s", $value, $unit );
	$value = (int)$value;
	if ( !is_string( $unit ) )
		return $value;
	$unit = trim( $unit );
	if ( empty( $unit ) )
		return $value;
	switch ( strtolower( $unit ) ) {
		case "b":
			return $value;
		case "k":
		case "kb":
			return $value * 1024;
		case "m":
		case "mb":
			return $value * 1048576;
		case "g":
		case "gb":
			return $value * 1073741824;
		case "t":
		case "tb":
			return $value * 1099511627776;
	}
	return $value;
}
?>
