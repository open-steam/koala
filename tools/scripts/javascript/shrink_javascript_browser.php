<html>
<head>
<title>JavaScript-Files minimieren</title>

<script type="text/javascript">
/* jsmin.js - 2006-08-31
Author: Franck Marcia
This work is an adaptation of jsminc.c published by Douglas Crockford.
Permission is hereby granted to use the Javascript version under the same
conditions as the jsmin.c on which it is based.

jsmin.c
2006-05-04

Copyright (c) 2002 Douglas Crockford  (www.crockford.com)

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

The Software shall be used for Good, not Evil.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

Update:
        add level:
                1: minimal, keep linefeeds if single
                2: normal, the standard algorithm
                3: agressive, remove any linefeed and doesn't take care of potential
                   missing semicolons (can be regressive)
        store stats
                jsmin.oldSize
                jsmin.newSize
*/

String.prototype.has = function(c) {
        return this.indexOf(c) > -1;
};

function jsmin(comment, input, level) {

        if (input === undefined) {
                input = comment;
                comment = '';
                level = 2;
        } else if (level === undefined || level < 1 || level > 3) {
                level = 2;
        }

        if (comment.length > 0) {
                comment += '\n';
        }

        var a = '',
                b = '',
                EOF = -1,
                LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
                DIGITS = '0123456789',
                ALNUM = LETTERS + DIGITS + '_$\\',
                theLookahead = EOF;


        /* isAlphanum -- return true if the character is a letter, digit, underscore,
                        dollar sign, or non-ASCII character.
        */

        function isAlphanum(c) {
                return c != EOF && (ALNUM.has(c) || c.charCodeAt(0) > 126);
        }


        /* get -- return the next character. Watch out for lookahead. If the
                        character is a control character, translate it to a space or
                        linefeed.
        */

        function get() {

                var c = theLookahead;
                if (get.i == get.l) {
                        return EOF;
                }
                theLookahead = EOF;
                if (c == EOF) {
                        c = input.charAt(get.i);
                        ++get.i;
                }
                if (c >= ' ' || c == '\n') {
                        return c;
                }
                if (c == '\r') {
                        return '\n';
                }
                return ' ';
        }

        get.i = 0;
        get.l = input.length;


        /* peek -- get the next character without getting it.
        */

        function peek() {
                theLookahead = get();
                return theLookahead;
        }


        /* next -- get the next character, excluding comments. peek() is used to see
                        if a '/' is followed by a '/' or '*'.
        */

        function next() {

                var c = get();
                if (c == '/') {
                        switch (peek()) {
                        case '/':
                                for (;;) {
                                        c = get();
                                        if (c <= '\n') {
                                                return c;
                                        }
                                }
                                break;
                        case '*':
                                get();
                                for (;;) {
                                        switch (get()) {
                                        case '*':
                                                if (peek() == '/') {
                                                        get();
                                                        return ' ';
                                                }
                                                break;
                                        case EOF:
                                                throw 'Error: Unterminated comment.';
                                        }
                                }
                                break;
                        default:
                                return c;
                        }
                }
                return c;
        }


        /* action -- do something! What you do is determined by the argument:
                        1   Output A. Copy B to A. Get the next B.
                        2   Copy B to A. Get the next B. (Delete A).
                        3   Get the next B. (Delete B).
           action treats a string as a single character. Wow!
           action recognizes a regular expression if it is preceded by ( or , or =.
        */

        function action(d) {

                var r = [];

                if (d == 1) {
                        r.push(a);
                }

                if (d < 3) {
                        a = b;
                        if (a == '\'' || a == '"') {
                                for (;;) {
                                        r.push(a);
                                        a = get();
                                        if (a == b) {
                                                break;
                                        }
                                        if (a <= '\n') {
                                                throw 'Error: unterminated string literal: ' + a;
                                        }
                                        if (a == '\\') {
                                                r.push(a);
                                                a = get();
                                        }
                                }
                        }
                }

                b = next();

                if (b == '/' && '(,=:[!&|'.has(a)) {
                        r.push(a);
                        r.push(b);
                        for (;;) {
                                a = get();
                                if (a == '/') {
                                        break;
                                } else if (a =='\\') {
                                        r.push(a);
                                        a = get();
                                } else if (a <= '\n') {
                                        throw 'Error: unterminated Regular Expression literal';
                                }
                                r.push(a);
                        }
                        b = next();
                }

                return r.join('');
        }


        /* m -- Copy the input to the output, deleting the characters which are
                        insignificant to JavaScript. Comments will be removed. Tabs will be
                        replaced with spaces. Carriage returns will be replaced with
                        linefeeds.
                        Most spaces and linefeeds will be removed.
        */

        function m() {

                var r = [];
                a = '\n';

                r.push(action(3));

                while (a != EOF) {
                        switch (a) {
                        case ' ':
                                if (isAlphanum(b)) {
                                        r.push(action(1));
                                } else {
                                        r.push(action(2));
                                }
                                break;
                        case '\n':
                                switch (b) {
                                case '{':
                                case '[':
                                case '(':
                                case '+':
                                case '-':
                                        r.push(action(1));
                                        break;
                                case ' ':
                                        r.push(action(3));
                                        break;
                                default:
                                        if (isAlphanum(b)) {
                                                r.push(action(1));
                                        } else {
                                                if (level == 1 && b != '\n') {
                                                        r.push(action(1));
                                                } else {
                                                        r.push(action(2));
                                                }
                                        }
                                }
                                break;
                        default:
                                switch (b) {
                                case ' ':
                                        if (isAlphanum(a)) {
                                                r.push(action(1));
                                                break;
                                        }
                                        r.push(action(3));
                                        break;
                                case '\n':
                                        if (level == 1 && a != '\n') {
                                                r.push(action(1));
                                        } else {
                                                switch (a) {
                                                case '}':
                                                case ']':
                                                case ')':
                                                case '+':
                                                case '-':
                                                case '"':
                                                case '\'':
                                                        if (level == 3) {
                                                                r.push(action(3));
                                                        } else {
                                                                r.push(action(1));
                                                        }
                                                        break;
                                                default:
                                                        if (isAlphanum(a)) {
                                                                r.push(action(1));
                                                        } else {
                                                                r.push(action(3));
                                                        }
                                                }
                                        }
                                        break;
                                default:
                                        r.push(action(1));
                                        break;
                                }
                        }
                }

                return r.join('');
        }

        jsmin.oldSize = input.length;
        ret = m(input);
        jsmin.newSize = ret.length;

        return comment + ret;

}

function minimize()
{
        try
        {
                var js = document.getElementsByName('js_complete')[0].value;
                var cookmenu = document.getElementsByName('cookmenu_complete')[0].value;

                document.getElementsByName('js_minimized')[0].value = jsmin(js);
                document.getElementsByName('cookmenu_minimized')[0].value = jsmin(cookmenu);
                return true;
        }
        catch(e) { alert("Fehler bei Minimierung aufgetreten! \n\n" + e); return false; }
}
</script>

<style type="text/css">
body
{
	font-family: Verdana, Helvetica, Sans-Serif;
    background-color: #D9E9FF;
}

table
{
	border: 0px;
    border-spacing: 2px;
}

td
{
	padding: 5px;
    background-color: #BDD9FF;
}
</style>
</head>

<body>
<center>

<?php

function recursive_glob($pattern = '*', $flags = 0, $path = '')
{
    $paths = glob($path . '*', GLOB_MARK | GLOB_ONLYDIR | GLOB_NOSORT);
    $files = glob($path . $pattern, $flags);

    foreach ($paths as $path)
    {
            $files = array_merge($files, recursive_glob($pattern, $flags, $path));
    }

    return $files;
}

if (isset($_POST['js_complete']) && isset($_POST['js_minimized']) && isset($_POST['cookmenu_complete']) && isset($_POST['cookmenu_minimized']))
{
        // second run: new js-files are being written onto HDD

	//	$path = "..\\..\\public\\javascript\\";    // Windows
		$path = "../../public/javascript/";        // Linux

        $js_complete = $_POST['js_complete'];
        $js_minimized = $_POST['js_minimized'];

        $cookmenu_complete = $_POST['cookmenu_complete'];
        $cookmenu_minimized = $_POST['cookmenu_minimized'];

        $filePointer = fopen("javascript_complete.js","w+");
        fwrite($filePointer, $js_complete);
        fclose($filePointer);

        $filePointer = fopen($path . "javascript_minimized.js","w+");
        fwrite($filePointer, $js_minimized);
        fclose($filePointer);

        $filePointer = fopen("cookmenu_complete.js","w+");
        fwrite($filePointer, $cookmenu_complete);
        fclose($filePointer);

        $filePointer = fopen($path . "cookmenu_minimized.js","w+");
        fwrite($filePointer, $cookmenu_minimized);
        fclose($filePointer);

        $cookmenu_filesize = filesize("cookmenu_complete.js");
        $cookmenu_filesize_minimized = filesize($path . "cookmenu_minimized.js");

        $js_filesize = filesize("javascript_complete.js");
        $js_filesize_minimized = filesize($path . "javascript_minimized.js");

        $js_difference = $js_filesize - $js_filesize_minimized;
        $cookmenu_difference = $cookmenu_filesize - $cookmenu_filesize_minimized;

		$js_percent = $js_difference / $js_filesize * 100;
		$cookmenu_percent = $cookmenu_difference / $cookmenu_filesize * 100;

		$js_percent = number_format($js_percent , 2 , ',' , '.');
		$cookmenu_percent = number_format($cookmenu_percent , 2 , ',' , '.');

        echo "<br /><br />";

        echo "<table width=\"400\">";
        echo "<tr>";
        echo "<td width=\"230\">javascript_complete.js</td>";
        echo "<td width=\"170\">" . number_format($js_filesize / 1024 ,0 ,'' , '.') . " KB</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td width=\"230\">javascript_minimized.js</td>";
        echo "<td width=\"170\">" . number_format($js_filesize_minimized / 1024 ,0 ,'' , '.') . " KB</td>";
        echo "</tr>";
		echo "<tr>";
        echo "<td width=\"230\">Ersparnis</td>";
        echo "<td width=\"170\">" . number_format($js_difference / 1024 ,0 ,'' , '.') . " KB ($js_percent %)</td>";
        echo "</tr>";
        echo "</table>";

		echo "<br /><br />";

		echo "<table width=\"400\">";
        echo "<tr>";
        echo "<td width=\"230\">cookmenu_complete.js</td>";
        echo "<td width=\"170\">" . number_format($cookmenu_filesize / 1024 ,0 ,'' , '.') . " KB</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td width=\"230\">cookmenu_minimized.js</td>";
        echo "<td width=\"170\">" . number_format($cookmenu_filesize_minimized / 1024 ,0 ,'' , '.') . " KB</td>";
        echo "</tr>";
		echo "<tr>";
        echo "<td width=\"230\">Ersparnis</td>";
        echo "<td width=\"170\">" . number_format($cookmenu_difference / 1024 ,0 ,'' , '.') . " KB ($cookmenu_percent  %)</td>";
        echo "</tr>";
        echo "</table>";

        echo "<br /><br />";

        echo "<h2>Die Dateien wurden gespeichert.";
        echo "<br /><br />";
        echo "Weiterhin frohes Schaffen!</h2>";
}
else
{
        // first run: js-files are searched and centralized, when finished user has to commit minimization

		$allJSfiles = null;			// an array with all JS-files to be minimized
		$js_header = "";			// the Header for the "normal" JS-files
		$cookmenu_header = "";		// the Header for the CookMenu JS-files
		$js_code = "";				// the code of the "normal" JS-files
        $cookmenu_code = "";		// the code of the CookMenu JS-files
        $js_complete = "";			// header + code of the "normal" JS-files
        $cookmenu_complete = "";	// header + code of the CookMenu JS-files
        $js_table = "";				// table for html output
        $cookmenu_table = "";		// table for html output

        echo "<h2>JavaScript-Files minimieren</h2>";
        echo "<br /><br />";

        $js_table =
        "<table width=\"600\">" .
        "<tr>" .
        "<td colspan=\"2\" align=\"center\"><b>javascript_complete.js</b></td>" .
        "</tr>";

        $cookmenu_table =
        "<table width=\"600\">" .
        "<tr>" .
        "<td colspan=\"2\" align=\"center\"><b>cookmenu_complete.js<b></td>" .
        "</tr>";

        $js_header = $cookmenu_header =
        "//////////////////////////////////////////////////" ."\n" .
		"//" . "\n" .
        "// This file contains the following files:" . "\n" .
		"//";

        // $allJSfiles = recursive_glob('*.js');

		/* Windows
        $allJSfiles = array("sources\\bbcode.js",
        					"sources\\builder.js",
        					"sources\\prototype.js",
							"sources\\effects.js",
        					"sources\\dragdrop.js",
        					"sources\\controls.js",
        					"sources\\scriptaculous.js",
        					"sources\\filter.js",
        					"sources\\slider.js",
        					"sources\\prototype_window\\tooltip.js",
        					"sources\\prototype_window\\window.js",
        					"sources\\prototype_window\\window_effects.js",
        					"sources\\prototype_window\\window_ext.js",
        					"sources\\JSCookMenu\\JSCookMenu.js",
        					"sources\\JSCookMenu\\effect.js",
        					"sources\\JSCookMenu\\ThemeOffice\\theme.js");
        */

		$allJSfiles = array("sources/bbcode.js",
        					"sources/builder.js",
        					"sources/prototype.js",
							"sources/effects.js",
        					"sources/dragdrop.js",
        					"sources/controls.js",
        					"sources/scriptaculous.js",
        					"sources/filter.js",
        					"sources/slider.js",
        					"sources/prototype_window/tooltip.js",
        					"sources/prototype_window/window.js",
        					"sources/prototype_window/window_effects.js",
        					"sources/prototype_window/window_ext.js",
        					"sources/JSCookMenu/JSCookMenu.js",
        					"sources/JSCookMenu/effect.js",
        					"sources/JSCookMenu/ThemeOffice/theme.js");

        foreach ($allJSfiles as $file)
        {
                // these files can be found directly in the corresponding templates
                if (substr($file, -7) == "home.js" || substr($file, -17) == "list_inventory.js") continue;

                if (substr($file, -20) == "cookmenu_complete.js" || substr($file, -22) == "javascript_complete.js") continue;

                ///// collect files in variables \\\\\

                if (substr($file, -13) == "JSCookMenu.js" ||
                    substr($file, -20) == "JSCookMenu/effect.js" ||  // Linux
                 // substr($file, -20) == "JSCookMenu\\effect.js" ||  // Windows
                    substr($file, -8) == "theme.js")
                {
						$cookmenu_table .=
						"<tr>" .
                		"<td width=\"500\">$file</td>" .
                		"<td width=\"100\" align=\"right\">" . number_format(filesize($file) / 1024 ,0 ,'' , '.') . " KB</td>" .
                		"</tr>";

                        $cookmenu_header .= "\n" . "// " . $file;

                        $cookmenu_code .= "//------------------------------" . "\n" .
                                          "// " . $file . "\n" .
                                          "//------------------------------" . "\n\n" .
                                          file_get_contents($file) . "\n\n\n\n";
                }
                else
                {
						$js_table .=
						"<tr>" .
                		"<td width=\"500\">$file</td>" .
                		"<td width=\"100\" align=\"right\">" . number_format(filesize($file) / 1024 ,0 ,'' , '.') . " KB</td>" .
                		"</tr>";

                        $js_header .= "\n" . "// " . $file;

                        $js_code .= "//------------------------------" . "\n" .
                                    "// " . $file . "\n" .
                                    "//------------------------------" . "\n\n" .
                                    file_get_contents($file) . "\n\n\n\n";
                }
        }

		// close headers
        $js_header .= "\n//\n//////////////////////////////////////////////////" ."\n\n";
        $cookmenu_header .= "\n//\n//////////////////////////////////////////////////" ."\n\n";

        // merge headers and code
        $js_complete = $js_header . $js_code;
        $cookmenu_complete = $cookmenu_header . $cookmenu_code;

		// print tables
		echo $js_table;
		echo "</table>";

		echo "<br /><br />";

		echo $cookmenu_table;
        echo "</table>";

        echo "<form action=\"shrink_javascript.php\" method=\"post\" onsubmit=\"return minimize();\">";
        echo "<input type=\"hidden\" name=\"js_complete\" value=\"" . htmlspecialchars($js_complete) . "\">";
        echo "<input type=\"hidden\" name=\"js_minimized\">";
        echo "<input type=\"hidden\" name=\"cookmenu_complete\" value=\"" . htmlspecialchars($cookmenu_complete) . "\">";
        echo "<input type=\"hidden\" name=\"cookmenu_minimized\">";
        echo "<br /><br />";
        echo "<input type=\"submit\" name=\"verkleinern\" value=\"Minimierung starten\">";
        echo "</form>";
}
?>

</center>
</body>
</html>