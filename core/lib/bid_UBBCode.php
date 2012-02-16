<?php
////////////////////////////////////////////////////////////////
/*

Replaces the UBB tags listed below with HTML tags, and vice versa.
Also keeps the line feeds in the text and removes all HTML tags.


For the lastest version go to:
http://www.phpclasses.org/browse.html/package/818.html


    UBB Tags:
                    [b]...[/b]                        bold
                    [i]...[/i]                        italic
                    [code]...[/code]                source code
                    [img], [/img]                        images
                    [/br]                         zeilenumbruch
                    [quote]...[/quote]                blockquote
                    [url]http//www.link[/url]        links
                    [url=http//www.link]name[/url]        links
                    [email]me@home.de[/email]        email link
                    [email=me@home.de]name[/email]        email link

    Additional Tags:
                    [u]...[/u]                        underline
                    [center]..[/center]                center
                    [color=name]...[/color]                colors
                    [size=gr��e]...  [/size]               size
    For a description of ubbcodes see: http://community.infopop.net/infopop/ubbcode.html

FUNCTIONS:
    function encode($str)
    function decode($str)
    function listCodes()

////////////////////////////////////////////////////////////////

    This library is free software; you can redistribute it and/or
    modify it under the terms of the GNU Lesser General Public
    License as published by the Free Software Foundation; either
    version 2.1 of the License, or (at your option) any later version.

    This library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
    Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public
    License along with this library; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
////////////////////////////////////////////////////////////////
/**
* Replaces the ubb tags with HTML tags, and vice versa.
* Also keeps the line feeds in the text and removes slashes (good for sql queries).
*
* @author            Lennart Groetzbach <lennartg@web.de>
* @copyright        Lennart Groetzbach <lennartg@web.de> - distributed under the LGPL
* @version             1.2 - 2002/10/27

<p>
History / Changes<br>
<table border="1" width="100%" cellpadding="3"><tr>
      <th>Version</th>    <th>Reported By</th>        <th>File / Function Changed</th>    <th>Date of Change</th> <th>Commment</th>
</tr><tr>
      <td>1.2</td>        <td>-</td>        <td>strip()</td>            <td>2003/03/24</td>     <td>added removal of color tag</td>
</tr><tr>
      <td>1.1</td>        <td>P.Gareau</td>        <td>encoding and decoding</td>            <td>2002/10/27</td>     <td>checks now for closing tags</td>
</tr><tr>
      <td>1.1</td>        <td>-</td>        <td>strip()</td>            <td>2002/10/27</td>     <td>added function to strip the ubb tags</td>
</tr></table>

*
* @access   public
*/
class UBBCode {

//////////////////////////////////////////////////////////
/**
* Encodes the string
* Removes all html tags and exchanges only the ubb tags and with html tags.
*
*
* @access   public
* @param    String      $str
* @return   String
*/
function encode($str) {
  $str = str_replace(array("<", ">", "\""), array("&lt;", "&gt;", "&quot;"), $str);
//  $str = strip_tags($str);

//  $str = preg_replace('/\[http(.*?)\ (.*)\]/', '<a href="http\1" target="_new">\2</a>', $str);

//  $str = preg_replace('//', '', $str);
  $str = preg_replace('/\[\/br\]/', '<br />', $str);
  $str = preg_replace('/\[b\](.*)\[\/b\]/Us', '<strong>\1</strong>', $str);
  $str = preg_replace('/\[i\](.*)\[\/i\]/Us', '<em>\1</em>', $str);
  $str = preg_replace('/\[u\](.*)\[\/u\]/Us', '<u>\1</u>', $str);
  $str = preg_replace('/\[center\](.*)\[\/center\]/Us', '<center>\1</center>', $str);
  $str = preg_replace('/\[code\](.*)\[\/code\]/Us', '<pre>\1</pre>', $str);
  $str = preg_replace('/\[quote\](.*)\[\/quote\]/Us', '<blockquote>\1</blockquote>', $str);

  $str = preg_replace('/\[table\](.*)\[\/table\]/Us', '<table>\1</table>', $str);
  $str = preg_replace('/\[tr\](.*)\[\/tr\]/Us', '<tr>\1</tr>', $str);
  $str = preg_replace('/\[th\](.*)\[\/th\]/Us', '<th>\1</th>', $str);
  $str = preg_replace('/\[td\](.*)\[\/td\]/Us', '<td>\1</td>', $str);

  $str = preg_replace('/\[color=(.*)\](.*)\[\/color\]/Us', '<font color="\1">\2</font>', $str);
  $str = preg_replace('/\[size=(.*)\](.*)\[\/size\]/Us', '<font size="\1">\2</font>', $str);

  $str = preg_replace('/\[img\](.*)\[\/img\]/Us', '<img src="\1"/>', $str);
  $str = preg_replace('/\[url\](.*)\[\/url\]/Us', '<a href="\1" target="_blank">\1</a>', $str);
  $str = preg_replace('/\[url=(.*)\](.*)\[\/url\]/Us', '<a href="\1" target="_blank">\2</a>', $str);
  $str = preg_replace('/\[email\](.*)\[\/email\]/Us', '<a href="mailto:\1">\1</a>', $str);
  $str = preg_replace('/\[email=(.*)\](.*)\[\/email\]/Us', '<a href="mailto:\1">\2</a>', $str);

  return nl2br($str);
}

//////////////////////////////////////////////////////////
/**
* Strips all UBB tags
* Removes all ubb tags and leaves only plain text
*
*
* @access   public
* @param    String      $str
* @return   String
*/
function strip($str) {
  $str = eregi_replace("\[b\]", '', $str);
  $str = eregi_replace("\[/b\]", '', $str);
  $str = eregi_replace("\[i\]", '', $str);
  $str = eregi_replace("\[/i\]", '', $str);
  $str = eregi_replace("\[u\]", '', $str);
  $str = eregi_replace("\[/u\]", '', $str);
  $str = eregi_replace("\[center\]", '', $str);
  $str = eregi_replace("\[/center\]", '', $str);
  $str = eregi_replace("\[code\]", '', $str);
  $str = eregi_replace("\[/code\]", '', $str);
  $str = eregi_replace("\[/color\]", '', $str);
  $str = preg_replace ("/\[color=(\S*?)\]/si", '', $str);
  $str = preg_replace ("/\[url(\S*?)\]/si", '', $str);
  $str = eregi_replace("\[/url\]", '', $str);
  $str = eregi_replace("\[email\]", '', $str);
  $str = eregi_replace("\[/email\]", '', $str);
  $str = eregi_replace("\[img\]", '', $str);
  $str = eregi_replace("\[/img\]", '', $str);
  $str = eregi_replace("\[quote\]", '', $str);
  $str = eregi_replace("\[/quote\]", '', $str);
  return $str;
}

//////////////////////////////////////////////////////////
/**
* Decodes the string
* Removes the html tags and replaces them with ubb code tags
*
* @access   public
* @param    String      $str
* @return   String
*/
function decode($str) {
  $str = eregi_replace("\<b\>", '[b]', $str);
  $str = eregi_replace("\</b\>", '[/b]', $str);
  $str = eregi_replace("\<i\>", '[i]', $str);
  $str = eregi_replace("\</i\>", '[/i]', $str);
  $str = eregi_replace("\<u\>", '[u]', $str);
  $str = eregi_replace("\</u\>", '[/u]', $str);
  $str = eregi_replace("\<center\>", '[center]', $str);
  $str = eregi_replace("\</center\>", '[/center]', $str);
  $str = eregi_replace("\<code\>", '[pre]', $str);
  $str = eregi_replace("\</code\>", '[/pre]', $str);
  $str = eregi_replace("\\<font color=([^>\[]*)\\>([^<\[]*)</font>" ,"[color=\"\\1\"]\\2[/color]",$str);
  $str = eregi_replace("\\<a href=\"([^\\[\"]*)\"\\ target=\"_blank\">([^<\[]*)</a>","[url=\\1]\\2[/url]", $str);
  $str = eregi_replace("\\<a href=\"mailto:([^\\[]*)\"\\>([^<\[]*)</a>","[email=\\1]\\2[/email]",$str);
  $str = eregi_replace("\\<img src=\"([^\\[]*)\"\\ border=0>([^<\[]*)","[img]\\1[/img]",$str);
  $str = eregi_replace("\<blockquote><smallfont>Quote:</smallfont><hr>", '[quote]', $str);
  $str = eregi_replace("\<hr></blockquote>", '[/quote]', $str);
  $str = eregi_replace("\<hr></blockquote>", '[/quote]', $str);
  $str = eregi_replace("\<br />\n", '/n', $str);
  $str = eregi_replace("\<br>", '/n', $str);
  return $str;
}

//////////////////////////////////////////////////////////
/**
* Dumps the code tags
* Displays a <pre> block with the "ubb_def" css style class
*
* @access   public
*/
function listCodes() {

return "<pre class='ubb_def'>
[b]...[/b]                          fett / bold
[i]...[/i]                          kursiv / italic
[u]...[/u]                          unterstrichen / underline

[center]...[/center]                zentriert / center
[size=number] ... [/size]           Schriftgr&ouml;sse / size
                                     bspw: +4 | +3 | +2 | +1 | -1 | -2
[color=name]...[/color]             Schriftfarbe / colors
                                     bspw: blue | red | yellow | green
[/br]                               Zeilenumbruch / enter

[img]...[/img]                      Grafik / images
[code]...[/code]                    Quelltext / source code
[quote]...[/quote]                  Zitat / blockquote

[url=http://www.link.me]name[/url]  Verkn&uuml;pfung / link
[email=me@home.de]name[/email]      E-Mail-Verkn&uuml;pfung / email link
</pre>";
}

//////////////////////////////////////////////////////////
}
//////////////////////////////////////////////////////////

?>