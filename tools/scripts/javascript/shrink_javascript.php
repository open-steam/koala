<?php
/**
 * jsmin.php - PHP implementation of Douglas Crockford's JSMin.
 *
 * This is pretty much a direct port of jsmin.c to PHP with just a few
 * PHP-specific performance tweaks. Also, whereas jsmin.c reads from stdin and
 * outputs to stdout, this library accepts a string as input and returns another
 * string as output.
 *
 * PHP 5 or higher is required.
 *
 * Permission is hereby granted to use this version of the library under the
 * same terms as jsmin.c, which has the following license:
 *
 * --
 * Copyright (c) 2002 Douglas Crockford  (www.crockford.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * The Software shall be used for Good, not Evil.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * --
 *
 * @package JSMin
 * @author Ryan Grove <ryan@wonko.com>
 * @copyright 2002 Douglas Crockford <douglas@crockford.com> (jsmin.c)
 * @copyright 2008 Ryan Grove <ryan@wonko.com> (PHP port)
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @version 1.1.1 (2008-03-02)
 * @link http://code.google.com/p/jsmin-php/
 */

class JSMin {
  const ORD_LF    = 10;
  const ORD_SPACE = 32;

  protected $a           = '';
  protected $b           = '';
  protected $input       = '';
  protected $inputIndex  = 0;
  protected $inputLength = 0;
  protected $lookAhead   = null;
  protected $output      = '';

  // -- Public Static Methods --------------------------------------------------

  public static function minify($js) {
    $jsmin = new JSMin($js);
    return $jsmin->min();
  }

  // -- Public Instance Methods ------------------------------------------------

  public function __construct($input) {
    $this->input       = str_replace("\r\n", "\n", $input);
    $this->inputLength = strlen($this->input);
  }

  // -- Protected Instance Methods ---------------------------------------------

  protected function action($d) {
    switch($d) {
      case 1:
        $this->output .= $this->a;

      case 2:
        $this->a = $this->b;

        if ($this->a === "'" || $this->a === '"') {
          for (;;) {
            $this->output .= $this->a;
            $this->a       = $this->get();

            if ($this->a === $this->b) {
              break;
            }

            if (ord($this->a) <= self::ORD_LF) {
              throw new JSMinException('Unterminated string literal.');
            }

            if ($this->a === '\\') {
              $this->output .= $this->a;
              $this->a       = $this->get();
            }
          }
        }

      case 3:
        $this->b = $this->next();

        if ($this->b === '/' && (
            $this->a === '(' || $this->a === ',' || $this->a === '=' ||
            $this->a === ':' || $this->a === '[' || $this->a === '!' ||
            $this->a === '&' || $this->a === '|' || $this->a === '?')) {

          $this->output .= $this->a . $this->b;

          for (;;) {
            $this->a = $this->get();

            if ($this->a === '/') {
              break;
            } elseif ($this->a === '\\') {
              $this->output .= $this->a;
              $this->a       = $this->get();
            } elseif (ord($this->a) <= self::ORD_LF) {
              throw new JSMinException('Unterminated regular expression '.
                  'literal.');
            }

            $this->output .= $this->a;
          }

          $this->b = $this->next();
        }
    }
  }

  protected function get() {
    $c = $this->lookAhead;
    $this->lookAhead = null;

    if ($c === null) {
      if ($this->inputIndex < $this->inputLength) {
        $c = $this->input[$this->inputIndex];
        $this->inputIndex += 1;
      } else {
        $c = null;
      }
    }

    if ($c === "\r") {
      return "\n";
    }

    if ($c === null || $c === "\n" || ord($c) >= self::ORD_SPACE) {
      return $c;
    }

    return ' ';
  }

  protected function isAlphaNum($c) {
    return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
  }

  protected function min() {
    $this->a = "\n";
    $this->action(3);

    while ($this->a !== null) {
      switch ($this->a) {
        case ' ':
          if ($this->isAlphaNum($this->b)) {
            $this->action(1);
          } else {
            $this->action(2);
          }
          break;

        case "\n":
          switch ($this->b) {
            case '{':
            case '[':
            case '(':
            case '+':
            case '-':
              $this->action(1);
              break;

            case ' ':
              $this->action(3);
              break;

            default:
              if ($this->isAlphaNum($this->b)) {
                $this->action(1);
              }
              else {
                $this->action(2);
              }
          }
          break;

        default:
          switch ($this->b) {
            case ' ':
              if ($this->isAlphaNum($this->a)) {
                $this->action(1);
                break;
              }

              $this->action(3);
              break;

            case "\n":
              switch ($this->a) {
                case '}':
                case ']':
                case ')':
                case '+':
                case '-':
                case '"':
                case "'":
                  $this->action(1);
                  break;

                default:
                  if ($this->isAlphaNum($this->a)) {
                    $this->action(1);
                  }
                  else {
                    $this->action(3);
                  }
              }
              break;

            default:
              $this->action(1);
              break;
          }
      }
    }

    return $this->output;
  }

  protected function next() {
    $c = $this->get();

    if ($c === '/') {
      switch($this->peek()) {
        case '/':
          for (;;) {
            $c = $this->get();

            if (ord($c) <= self::ORD_LF) {
              return $c;
            }
          }

        case '*':
          $this->get();

          for (;;) {
            switch($this->get()) {
              case '*':
                if ($this->peek() === '/') {
                  $this->get();
                  return ' ';
                }
                break;

              case null:
                throw new JSMinException('Unterminated comment.');
            }
          }

        default:
          return $c;
      }
    }

    return $c;
  }

  protected function peek() {
    $this->lookAhead = $this->get();
    return $this->lookAhead;
  }
}

// -- Exceptions ---------------------------------------------------------------
class JSMinException extends Exception {}

///////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////

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

$allJSfiles = null;			// an array with all JS-files to be minimized
$js_header = "";			// the Header for the "normal" JS-files
//$cookmenu_header = "";		// the Header for the CookMenu JS-files
$js_code = "";				// the code of the "normal" JS-files
//$cookmenu_code = "";		// the code of the CookMenu JS-files
$js_complete = "";			// header + code of the "normal" JS-files
//$cookmenu_complete = "";	// header + code of the CookMenu JS-files
$js_minimized = "";			// minimized code of the "normal" JS-files
//$cookmenu_minimized = "";	// minimized code of the CookMenu JS-files
$js_output = "";			// screen output
//$cookmenu_output = "";		// screen output

$js_output = "\njavascript_complete.js\n";
$js_output .= "======================\n\n";

//$cookmenu_output = "\ncookmenu_complete.js\n";
//$cookmenu_output .= "====================\n\n";

$js_header = //$cookmenu_header =
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

// Linux
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
					"sources/prototype_window/window_ext.js");
				//	"sources/JSCookMenu/JSCookMenu.js",
				//	"sources/JSCookMenu/effect.js",
				//	"sources/JSCookMenu/ThemeOffice/theme.js");

foreach ($allJSfiles as $file)
{
	// these files can be found directly in the corresponding templates
	if (substr($file, -7) == "home.js" || substr($file, -17) == "list_inventory.js") continue;

	if (substr($file, -20) == "cookmenu_complete.js" || substr($file, -22) == "javascript_complete.js") continue;

	// collect files in variables

//	if (substr($file, -13) == "JSCookMenu.js" ||
//		substr($file, -20) == "JSCookMenu/effect.js" ||  // Linux
			// substr($file, -20) == "JSCookMenu\\effect.js" ||  // Windows
//			substr($file, -8) == "theme.js")
//	{
//		$cookmenu_output .= $file . " : " . number_format(filesize($file) / 1024 ,0 ,'' , '.') . " KB\n";
//		$cookmenu_header .= "\n" . "// " . $file;

//		$cookmenu_code .= "//------------------------------" . "\n" .
//						  "// " . $file . "\n" .
//						  "//------------------------------" . "\n\n" .
//						  file_get_contents($file) . "\n\n\n\n";
//	}
//	else
//	{
		$js_output .= $file . " : " . number_format(filesize($file) / 1024 ,0 ,'' , '.') . " KB\n";
		$js_header .= "\n" . "// " . $file;

		$js_code .= "//------------------------------" . "\n" .
					"// " . $file . "\n" .
					"//------------------------------" . "\n\n" .
					file_get_contents($file) . "\n\n\n\n";
//	}
}

// close headers
$js_header .= "\n//\n//////////////////////////////////////////////////" ."\n\n";
//$cookmenu_header .= "\n//\n//////////////////////////////////////////////////" ."\n\n";

// merge headers and code
$js_complete = $js_header . $js_code;
//$cookmenu_complete = $cookmenu_header . $cookmenu_code;

// print output
echo $js_output;
//echo $cookmenu_output;

echo "\n==================== Schreibe Dateien ====================\n";

$js_minimized = JSMin::minify( $js_complete );
//$cookmenu_minimized = JSMin::minify( $cookmenu_complete );

// $path = "..\\..\\public\\javascript\\";    // Windows
$path = "../../public/javascript/";        // Linux

$filePointer = fopen("javascript_complete.js","w+");
fwrite($filePointer, $js_complete);
fclose($filePointer);

$filePointer = fopen($path . "javascript_minimized.js","w+");
fwrite($filePointer, $js_minimized);
fclose($filePointer);

//$filePointer = fopen("cookmenu_complete.js","w+");
//fwrite($filePointer, $cookmenu_complete);
//fclose($filePointer);

//$filePointer = fopen($path . "cookmenu_minimized.js","w+");
//fwrite($filePointer, $cookmenu_minimized);
//fclose($filePointer);

//$cookmenu_filesize = filesize("cookmenu_complete.js");
//$cookmenu_filesize_minimized = filesize($path . "cookmenu_minimized.js");

$js_filesize = filesize("javascript_complete.js");
$js_filesize_minimized = filesize($path . "javascript_minimized.js");

$js_difference = $js_filesize - $js_filesize_minimized;
//$cookmenu_difference = $cookmenu_filesize - $cookmenu_filesize_minimized;

$js_percent = $js_difference / $js_filesize * 100;
//$cookmenu_percent = $cookmenu_difference / $cookmenu_filesize * 100;

$js_percent = number_format($js_percent , 2 , ',' , '.');
//$cookmenu_percent = number_format($cookmenu_percent , 2 , ',' , '.');

echo "\n";
echo "javascript_complete.js : " . number_format($js_filesize / 1024 ,0 ,'' , '.') . " KB\n";
echo "javascript_minimized.js : " . number_format($js_filesize_minimized / 1024 ,0 ,'' , '.') . " KB\n";
echo "Ersparnis : " . number_format($js_difference / 1024 ,0 ,'' , '.') . " KB ($js_percent %)\n";
echo "\n";
//echo "cookmenu_complete.js : " . number_format($cookmenu_filesize / 1024 ,0 ,'' , '.') . " KB\n";
//echo "cookmenu_minimized.js : " . number_format($cookmenu_filesize_minimized / 1024 ,0 ,'' , '.') . " KB\n";
//echo "Ersparnis : " . number_format($cookmenu_difference / 1024 ,0 ,'' , '.') . " KB ($cookmenu_percent  %)\n";
//echo "\n";
?>