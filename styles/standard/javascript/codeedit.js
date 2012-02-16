/*
 +-------------------------------------------------------------------+
 |                 J S - C O D E E D I T   (v1.4)                    |
 |                                                                   |
 | Copyright Gerd Tentler              www.gerd-tentler.de/tools     |
 | Created: Oct. 3, 2009               Last modified: Apr. 25, 2011  |
 +-------------------------------------------------------------------+
 | This program may be used and hosted free of charge by anyone for  |
 | personal purpose as long as this copyright notice remains intact. |
 |                                                                   |
 | Obtain permission before selling the code for this program or     |
 | hosting this software on a commercial website or redistributing   |
 | this software over the Internet or in any other medium. In all    |
 | cases copyright must remain intact.                               |
 +-------------------------------------------------------------------+

===========================================================================================================
 This script was tested with the following systems and browsers:

 - Windows: IE, Firefox

 If you use another browser or system, this script may not work for you - sorry.

 Generally, code editing should work on Windows with Internet Explorer 5.5+ and with browsers using the
 Mozilla 1.3+ engine, i.e. all browsers that support "designMode".

 NOTE: The script also works with browsers that don't support code editing - a simple textarea will
 replace the code editor.

 For instructions on how to use this script, read the README file or visit my website:
 http://www.gerd-tentler.de/tools/codeedit/
===========================================================================================================
*/
//---------------------------------------------------------------------------------------------------------
// Add new methods to Function prototype - needed to pass editor instance to event handlers etc.
//---------------------------------------------------------------------------------------------------------
if(typeof Function.prototype.bind == 'undefined') Function.prototype.bind = function() {
	var _this = this, args = [], object = arguments[0];
	for(var i = 1; i < arguments.length; i++) args.push(arguments[i]);
	return function() {
		return _this.apply(object, args);
	}
}

if(typeof Function.prototype.bindAsEventListener == 'undefined') Function.prototype.bindAsEventListener = function() {
	var _this = this, args = [], object = arguments[0];
	for(var i = 1; i < arguments.length; i++) args[i + 1] = arguments[i];
	return function(e) {
		args[0] = e || event;
		return _this.apply(object, args);
	}
}

//---------------------------------------------------------------------------------------------------------
// Global variables and functions
//---------------------------------------------------------------------------------------------------------
var OP = (window.opera || navigator.userAgent.indexOf('Opera') != -1);
var IE = (navigator.userAgent.indexOf('MSIE') != -1 && !OP);
var FF = (navigator.userAgent.indexOf('Firefox') != -1 && !OP);
var WK = (navigator.userAgent.indexOf('WebKit') != -1 && !OP);
var GK = (navigator.userAgent.indexOf('Gecko') != -1 || OP);
var DM = (document.designMode && document.execCommand && !OP && !WK); /* Opera and WebKit not supported at the moment */

function CodeEdit(node, options, id) {
//---------------------------------------------------------------------------------------------------------
// Initialization
//---------------------------------------------------------------------------------------------------------
	this.node = node;
	this.language = options[0] ? options[0].toLowerCase() : '';
	this.viewLineNumbers = tools.inArray('lineNumbers', options, true);
	this.setFocus = tools.inArray('focus', options, true);
	this.id = id;
	this.textWidth = node.offsetWidth;
	this.textHeight = node.offsetHeight;
	this.fieldName = (node.name != '') ? node.name : node.id;
	this.bgColor = this.node.style.backgroundColor ? this.node.style.backgroundColor : '#FFFFFF';
	this.borderWidth = this.node.style.borderWidth ? parseInt(this.node.style.borderWidth) : 1;
	this.content = this.node.value.replace(/\s+$/, '');
	this.editor = null;
	this.canvas = null;
	this.numbers = null;
	this.input = null;
	this.timer = null;
	this.lines = [];
	this.cntLines = Math.round(this.textHeight / 16);
	this.maxLines = 0;
	this.paste = false;

//---------------------------------------------------------------------------------------------------------
// Class methods
//---------------------------------------------------------------------------------------------------------
	this.create = function() {
		if(DM) {
			var cont = document.createElement('div');
			cont.style.width = (this.textWidth - this.borderWidth * 2) + 'px';
			cont.style.height = (this.textHeight - this.borderWidth * 2) + 'px';
			cont.style.borderWidth = this.borderWidth + 'px';
			cont.style.borderStyle = this.node.style.borderStyle ? this.node.style.borderStyle : 'solid';
			cont.style.borderColor = this.node.style.borderColor ? this.node.style.borderColor : '#808080';
			cont.style.styleFloat = this.node.style.styleFloat;
			cont.style.cssFloat = this.node.style.cssFloat;
			this.node.parentNode.replaceChild(cont, this.node);

			if(this.viewLineNumbers) {
				this.numbers = document.createElement('div');
				this.numbers.style.display = 'none';
				this.numbers.style.styleFloat = 'left';
				this.numbers.style.cssFloat = 'left';
				this.numbers.style.overflow = 'hidden';
				this.numbers.style.textAlign = 'right';
				this.numbers.style.padding = '4px';
				this.numbers.style.borderRight = '1px solid #808080';
				this.numbers.style.color = '#808080';
				this.numbers.style.backgroundColor = '#F0F0F0';
				this.numbers.style.height = (this.textHeight - this.borderWidth * 2 - 8) + 'px';
				this.numbers.style.width = '35px';
				this.numbers.style.fontFamily = 'Monospace';
				this.numbers.style.fontSize = '13px';
				cont.appendChild(this.numbers);
				tools.setUnselectable(this.numbers);
				this.setNumbers();
			}

			this.node = document.createElement('iframe');
			this.node.id = this.id;
			this.node.frameBorder = 0;
			this.node.style.width = '100%'
			this.node.style.height = (this.textHeight - this.borderWidth * 2) + 'px';
			cont.appendChild(this.node);

			this.input = document.createElement('input');
			this.input.type = 'hidden';
			this.input.name = this.input.id = this.fieldName;
			cont.appendChild(this.input);

			if(!this.initEditor()) alert("Could not create code editor");
		}
		else {
			this.node.style.whiteSpace = 'pre';
			this.node.style.padding = '2px';
			this.editor = this.node;
			tools.addListener(this.node, 'keydown', this.keyDownHandler.bindAsEventListener(this));
		}
	}

	this.getEditor = function() {
		if(this.node.contentWindow) return this.node.contentWindow;
		if(document.frames) return document.frames[this.id];
		return false;
	}

	this.initEditor = function() {
		if(this.editor = this.getEditor()) {
			var html =	'<html><head><style type="text/css"> ' +
						'BODY { ' +
						'margin: 4px; ' +
						'background-color: ' + this.bgColor + '; ' +
						'white-space: nowrap; ' +
						'color: #000000; ' +
						'font-family: Monospace; ' +
						'font-size: 13px; ' +
						'} ' +
						'P { margin: 0px; } ' +
						'IMG { width: 1px; height: 1px; } ' +
						this.setLanguageStyle() +
						'</style></head>' +
						'<body></body></html>';

			this.editor.document.designMode = 'on';
			if(GK) {
				this.editor.document.execCommand('useCSS', false, true); /* for older browsers */
				this.editor.document.execCommand('styleWithCSS', false, false);
			}
			this.editor.document.open();
			this.editor.document.write(html);
			this.editor.document.close();
			this.canvas = this.editor.document.body;

			if(this.viewLineNumbers) {
				this.numbers.style.display = 'block';
				this.node.style.width = (this.node.offsetWidth - 46) + 'px';
			}

			if(this.content != '') {
				this.content = this.content.replace(/</g, '&lt;');
				this.content = this.content.replace(/>/g, '&gt;');
				this.setCode(0, 0, this.content);
				this.syntaxHilight(true);
			}
			else if(FF) {
				/* workaround for Firefox: place caret correctly into canvas */
				this.canvas.innerHTML = '<br>';
			}
			tools.addListener(this.editor.document, 'keydown', this.keyDownHandler.bindAsEventListener(this));
			tools.addListener(this.editor.document, 'keyup', this.keyUpHandler.bindAsEventListener(this));
			tools.addListener(this.editor, 'scroll', this.scrollHandler.bindAsEventListener(this));

			if(FF) {
				/* for some reason, this only seems to work with Firefox :-( */
				tools.addListener(this.editor, 'load', this.loadHandler.bindAsEventListener(this));
			}
			else {
				/* ugly workaround for other browsers */
				setTimeout(this.loadHandler.bindAsEventListener(this), 1000);
			}
			return true;
		}
		return false;
	}

	this.setLanguageStyle = function() {
		var map = languages[this.language];
		var style = 'u, tt, b, s, i, em, ins { text-decoration: none; font-style: normal; font-weight: normal; } ';
		for(var key in map) if(map[key].style) style += map[key].style + ' ';
		return style;
	}

	this.setNumbers = function() {
		if(this.lines) {
			var cnt = this.lines.length + 1;
			if(cnt < this.cntLines) cnt = this.cntLines;
		}
		else var cnt = this.cntLines;

		var numbers = [];
		cnt += 10;
		for(var i = 1; i <= cnt; i++) numbers.push(i);
		this.numbers = tools.replaceHtml(this.numbers, numbers.join('<br>'));
		this.maxLines = cnt;
	}

	this.getCode = function(lineFrom, lineTo, convSpecialChars) {
		var code = this.canvas.innerHTML.replace(/[\r\n]/g, '');
		if(code) {
			code = code.replace(/<(p|div)>(.*?)<\/(p|div)>/gi, '$2\n');
			code = code.replace(/<br>/gi, '\n');
			if(!IE) code = code.replace(/<img>/i, '\u0001');

			this.lines = code.split('\n');

			if(!lineFrom) lineFrom = 0;
			if(!lineTo || lineTo > this.lines.length) lineTo = this.lines.length;

			code = this.lines.slice(lineFrom, lineTo).join('\n');
			code = code.replace(/(&nbsp;){4}/g, '\t');
			code = code.replace(/&nbsp;/g, ' ');
			code = code.replace(/<[^>]+>/g, '');

			if(convSpecialChars) {
				code = code.replace(/&amp;/g, '&');
				code = code.replace(/&lt;/g, '<');
				code = code.replace(/&gt;/g, '>');
			}
		}
		return code;
	}

	this.setCode = function(lineFrom, lineTo, code) {
		code = code.replace(/\r?\n/g, '<br>');
		code = code.replace(/\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;');
		code = code.replace(/\s/g, '&nbsp;');

		if(this.lines && lineTo > 0) {
			var c = [];
			c = this.lines.slice(0, lineFrom);
			c.push(code);
			c = c.concat(this.lines.slice(lineTo));
			code = c.join('<br>');
		}
		if(!IE) code = code.replace(/\u0001/, '<img>');
		this.canvas = tools.replaceHtml(this.canvas, code);

		if(this.numbers) {
			if(this.lines.length > this.maxLines) this.setNumbers();
			setTimeout(this.scrollHandler.bind(this), 50);
		}
	}

	this.parseCode = function(code) {
		var map, key, i;
		if(map = languages[this.language]) for(key in map) {
			if(map[key].match) for(i = 0; i < map[key].match.length; i++) {
				code = code.replace(map[key].match[i], map[key].replace[i]);
			}
		}
		return code;
	}

	this.insertMarker = function() {
		if(IE) {
			this.insertText('\u0001');
		}
		else if(GK) {
			var range = this.editor.getSelection().getRangeAt(0);
			range.insertNode(this.editor.document.createElement('img'));
		}
	}

	this.removeMarker = function() {
		if(IE) {
			var range = this.canvas.createTextRange();
			if(range.findText('\u0001')) {
				range.text = '';
				range.select();
			}
		}
		else if(GK) {
			var sel = this.editor.getSelection();
			var range = this.editor.document.createRange();
			var node = this.canvas.getElementsByTagName('img')[0];
			range.selectNode(node);
			if(OP) range.collapse(true);
			sel.removeAllRanges();
			sel.addRange(range);
			node.parentNode.removeChild(node);
		}
	}

	this.insertText = function(str) {
		if(IE) {
			var range = this.editor.document.selection.createRange();
			range.text = str;
		}
		else if(GK) {
			if(DM) {
				this.insertMarker();
				var range = this.editor.getSelection().getRangeAt(0);
				range.insertNode(this.editor.document.createTextNode(str));
				this.removeMarker();
			}
			else {
				/* special treatment for textarea */
				var start = this.editor.selectionStart;
				var end = this.editor.selectionEnd;
				var top = this.editor.scrollTop;
				var content = this.editor.value;
				this.editor.value = content.substring(0, start) + str + content.substring(end, content.length);
				this.editor.selectionStart = start + str.length;
				this.editor.selectionEnd = start + str.length;
				if(top) this.editor.scrollTop = top;
			}
		}
	}

	this.syntaxHilight = function(init) {
		if(init) {
			var lineFrom = lineTo = 0;
		}
		else {
			var lineFrom = this.getStartLine();
			var lineTo = lineFrom + this.cntLines;
		}
		this.insertMarker();
		var code = this.parseCode(this.getCode(lineFrom, lineTo));
		this.setCode(lineFrom, lineTo, code);
		this.removeMarker();
		this.timer = null;
	}

	this.getStartLine = function() {
		var perc = this.canvas.scrollTop / this.canvas.scrollHeight;
		return Math.round(this.lines.length * perc) + 1;
	}

//---------------------------------------------------------------------------------------------------------
// Event handlers
//---------------------------------------------------------------------------------------------------------
	this.loadHandler = function(e) {
		if(this.setFocus) this.editor.focus();
		tools.addListener(this.input.form, 'submit', this.submitHandler.bindAsEventListener(this));
	}

	this.keyDownHandler = function(e) {
		var evt = e ? e : this.editor.event;
		var keyCode = (evt.which || evt.keyCode || evt.charCode);
		this.paste = (keyCode == 86 && (evt.ctrlKey || evt.metaKey));

		if(!evt.shiftKey && !evt.ctrlKey && !evt.altKey && !evt.metaKey) {
			switch(keyCode) {
				case 9:
					this.insertText('\u00A0\u00A0\u00A0\u00A0');
					if(evt.preventDefault) evt.preventDefault();
					return false;
				case 13:
					if(IE) {
						this.insertText('\n');
						if(evt.preventDefault) evt.preventDefault();
						return false;
					}
					break;
			}
		}
	}

	this.keyUpHandler = function(e) {
		if(typeof tools == 'undefined') return;
		var evt = e ? e : this.editor.event;
		var keyCode = (evt.which || evt.keyCode || evt.charCode);
		var ctrlA = (keyCode == 65 && (evt.ctrlKey || evt.metaKey));
		var ctrlC = (keyCode == 67 && (evt.ctrlKey || evt.metaKey));
		var ignoreKey = (tools.inArray(keyCode, [16, 17]) || ctrlA || ctrlC);
		var moveKey = tools.inArray(keyCode, [33, 34, 37, 38, 39, 40]);

		if(!ignoreKey && !moveKey) {
			if(this.timer) clearTimeout(this.timer);
			this.timer = setTimeout(this.syntaxHilight.bind(this), 500);
		}
	}

	this.scrollHandler = function(e) {
		if(this.numbers) this.numbers.scrollTop = this.canvas.scrollTop;
	}

	this.submitHandler = function(e) {
		this.input.value = this.getCode(0, 0, true);
	}
}

//---------------------------------------------------------------------------------------------------------
// Little helpers
//---------------------------------------------------------------------------------------------------------
var tools = {

	inArray: function(val, arr, ignoreCase) {
		var str = '|' + arr.join('|') + '|';
		if(ignoreCase) {
			str = str.toLowerCase();
			val = val.toLowerCase();
		}
		return (str.indexOf('|' + val + '|') != -1);
	},

	addListener: function(obj, type, fn) {
		if(obj.addEventListener) {
			obj.addEventListener(type, fn, false);
		}
		else if(obj.attachEvent) {
			obj.attachEvent('on' + type, fn);
		}
	},

	setUnselectable: function(node) {
		node.unselectable = true;
		node.style.MozUserSelect = 'none';
		node.onmousedown = function() { return false; }
		node.style.cursor = 'default';
	},

	replaceHtml: function(node, html) {
		/*@cc_on // pure innerHTML is slightly faster in IE
			node.innerHTML = html;
			return node;
		@*/
		var newNode = node.cloneNode(false);
		newNode.innerHTML = html;
		node.parentNode.replaceChild(newNode, node);
		return newNode;
	}
}

//---------------------------------------------------------------------------------------------------------
// Convert textareas
//---------------------------------------------------------------------------------------------------------
tools.addListener(window, 'load', function() {
	var nodes = document.getElementsByTagName('textarea');
	var options = ceos = [];

	for(var i = 0; i < nodes.length; i++) {
		if(nodes[i].className.match(/^codeedit(\s+(.+))?/i)) {
			options = RegExp.$2.split(/\s+/);
			ceos.push(new CodeEdit(nodes[i], options, 'codeEdit_' + (i + 1)));
		}
	}
	for(i in ceos) ceos[i].create();
});

//---------------------------------------------------------------------------------------------------------
// Supported languages
//---------------------------------------------------------------------------------------------------------
var languages = {

	javascript: {
		operators: {
			match: [ /\/\*/g, /\*\//g, /\/\//g, /((&amp;)+|(&lt;)+|(&gt;)+|[\|!=%\*\/\+\-]+)/g, /\u0002/g, /\u0003/g, /\u0004/g ],
			replace: [ '\u0002', '\u0003', '\u0004', '<tt>$1</tt>', '/*', '*/', '//' ],
			style: 'tt { color: #C00000; }'
		},
		brackets: {
			match: [ /([\(\)\{\}\[\]])/g ],
			replace: [ '<b>$1</b>' ],
			style: 'b { color: #A000A0; font-weight: bold; }'
		},
		numbers: {
			match: [ /\b(-?\d+)\b/g ],
			replace: [ '<u>$1</u>' ],
			style: 'u { color: #C00000; }'
		},
		keywords: {
			match: [ /\b(break|case|catch|const|continue|default|delete|do|else|export|false|finally|for|function|if|in|instanceof|new|null|return|switch|this|throw|true|try|typeof|undefined|var|void|while|with)\b/g ],
			replace: [ '<em>$1</em>' ],
			style: 'em { color: #0000C0; }'
		},
		strings: {
			match: [ /(".*?")/g, /('.*?')/g ],
			replace: [ '<s>$1</s>', '<s>$1</s>' ],
			style: 's, s u, s tt, s b, s em, s i { color: #008000; font-weight: normal; }'
		},
		comments: {
			match: [ /(\/\/[^\n]*)(\n|$)/g, /(\/\*)/g, /(\*\/)/g ],
			replace: [ '<i>$1</i>$2', '<i>$1', '$1</i>' ],
			style: 'i, i u, i tt, i b, i s, i em { color: #808080; font-weight: normal; }'
		}
	},

	php: {
		tags: {
			match: [ /&lt;(\/?(a|abbr|acronym|address|applet|area|b|base|basefont|bdo|big|blockquote|body|br|button|caption|center|cite|code|col|colgroup|dd|del|dfn|dir|div|dl|dt|em|fieldset|font|form|frame|frameset|h[1-6]|head|hr|html|i|iframe|img|input|ins|isindex|kbd|label|legend|li|link|map|menu|meta|noframes|noscript|object|ol|optgroup|option|p|param|pre|q|s|samp|script|select|small|span|strike|strong|style|sub|sup|table|tbody|td|textarea|tfoot|th|thead|title|tr|tt|u|ul|var)(\s+.*?)?)&gt;/gi ],
			replace: [ '��$1���' ]
		},
		operators: {
			match: [ /\/\*/g, /\*\//g, /\/\//g, /((&amp;)+|(&lt;)+|(&gt;)+|[\|!=%\*\/\+\-]+)/g, /\u0002/g, /\u0003/g, /\u0004/g, /��(.+?)���/g ],
			replace: [ '\u0002', '\u0003', '\u0004', '<tt>$1</tt>', '/*', '*/', '//', '<em>&lt;$1&gt;</em>' ],
			style: 'tt { color: #C00000; }'
		},
		brackets: {
			match: [ /([\(\)\{\}\[\]])/g, /(<tt>)?&lt;(<\/tt>)?\?(php)?/gi, /\?(<tt>)?&gt;(<\/tt>)?/gi ],
			replace: [ '<b>$1</b>', '<b>&lt;?$3</b>', '<b>?&gt;</b>' ],
			style: 'b { color: #A000A0; font-weight: bold; }'
		},
		numbers: {
			match: [ /\b(-?\d+)\b/g ],
			replace: [ '<u>$1</u>' ],
			style: 'u { color: #C00000; }'
		},
		keywords: {
			match: [ /\b(__CLASS__|__FILE__|__FUNCTION__|__LINE__|__METHOD__|abstract|and|array|as|break|case|catch|class|clone|const|continue|declare|default|die|do|echo|else|elseif|empty|enddeclare|endfor|endforeach|endif|endswitch|endwhile|eval|exception|exit|extends|final|false|for|foreach|function|global|if|implements|include|include_once|interface|isset|list|new|or|print|private|protected|public|require|require_once|return|static|switch|this|throw|true|try|unset|use|var|while|xor)\b/g ],
			replace: [ '<em>$1</em>' ],
			style: 'em, em tt { color: #0000C0; font-weight: normal; }'
		},
		variables: {
			match: [ /(\$)(<[^>]+>)?(\w+)(<\/[^>]+>)?\b/gi ],
			replace: [ '<ins>$1$3</ins>' ],
			style: 'ins { color: #909000; }'
		},
		strings: {
			match: [ /(".*?")/g, /('.*?')/g ],
			replace: [ '<s>$1</s>', '<s>$1</s>' ],
			style: 's, s u, s tt, s b, s em, s ins, s i { color: #008000; font-weight: normal; }'
		},
		comments: {
			match: [ /(\/\/[^\n]*)(\n|$)/g, /(#[^\n]*)(\n|$)/g, /(\/\*)/g, /(\*\/)/g, /(<tt>)?&lt;(<\/tt><tt>)?!--(<\/tt>)/gi, /(<tt>)?--(<\/tt><tt>)?&gt;(<\/tt>)?/gi ],
			replace: [ '<i>$1</i>$2', '<i>$1</i>$2', '<i>$1', '$1</i>', '<i>&lt;!--', '--&gt;</i>' ],
			style: 'i, i u, i tt, i b, i s, i em, i ins { color: #808080; font-weight: normal; }'
		}
	},

	html: {
		scriptAreas: {
			match: [ /(&lt;script(.*?)&gt;)/gi, /(&lt;\/script&gt;)/gi ],
			replace: [ '$1<tt>', '</tt>$1' ],
			style: 'tt { color: #909000; }'
		},
		styleAreas: {
			match: [ /(&lt;style(.*?)&gt;)/gi, /(&lt;\/style&gt;)/gi ],
			replace: [ '$1<b>', '</b>$1' ],
			style: 'b { color: #A000A0; }'
		},
		tags: {
			match: [ /(&lt;\/?(a|abbr|acronym|address|applet|area|b|base|basefont|bdo|big|blockquote|body|br|button|caption|center|cite|code|col|colgroup|dd|del|dfn|dir|div|dl|dt|em|fieldset|font|form|frame|frameset|h[1-6]|head|hr|html|i|iframe|img|input|ins|isindex|kbd|label|legend|li|link|map|menu|meta|noframes|noscript|object|ol|optgroup|option|p|param|pre|q|s|samp|script|select|small|span|strike|strong|style|sub|sup|table|tbody|td|textarea|tfoot|th|thead|title|tr|tt|u|ul|var)(\s+.*?)?&gt;)/gi ],
			replace: [ '<em>$1</em>' ],
			style: 'em { color: #0000C0; }'
		},
		strings: {
			match: [ /=(".*?")/g, /=('.*?')/g ],
			replace: [ '=<s>$1</s>', '=<s>$1</s>' ],
			style: 's, s tt, s b, s em, s i { color: #008000; }'
		},
		comments: {
			match: [ /(&lt;!--)/g, /(--&gt;)/g ],
			replace: [ '<i>$1', '$1</i>' ],
			style: 'i, i tt, i b, i s, i em { color: #808080; }'
		}
	},

	css: {
		classes: {
			match: [ /(.+?)\{/g ],
			replace: [ '<tt>$1</tt>{' ],
			style: 'tt { color: #0000C0; }'
		},
		keys: {
			match: [ /([\{\n]\s*)([\w\-]*?:)([^\/])/g ],
			replace: [ '$1<u>$2</u>$3', ':' ],
			style: 'u { color: #C00000; }'
		},
		brackets: {
			match: [ /([\{\}])/g ],
			replace: [ '<b>$1</b>' ],
			style: 'b { color: #A000A0; font-weight: bold; }'
		},
		comments: {
			match: [ /(\/\*)/g, /(\*\/)/g ],
			replace: [ '<i>$1', '$1</i>' ],
			style: 'i, i tt, i u, i b { color: #808080; font-weight: normal; }'
		}
	},

	perl: {
		tags: {
			match: [ /&lt;(\/?(a|abbr|acronym|address|applet|area|b|base|basefont|bdo|big|blockquote|body|br|button|caption|center|cite|code|col|colgroup|dd|del|dfn|dir|div|dl|dt|em|fieldset|font|form|frame|frameset|h[1-6]|head|hr|html|i|iframe|img|input|ins|isindex|kbd|label|legend|li|link|map|menu|meta|noframes|noscript|object|ol|optgroup|option|p|param|pre|q|s|samp|script|select|small|span|strike|strong|style|sub|sup|table|tbody|td|textarea|tfoot|th|thead|title|tr|tt|u|ul|var)(\s+.*?)?)&gt;/gi ],
			replace: [ '��$1���' ]
		},
		operators: {
			match: [ /((&amp;)+|(&lt;)+|(&gt;)+|[\|=\+\-]+|[!%\*\/~])/g, /��(.+?)���/g ],
			replace: [ '<tt>$1</tt>', '<em>&lt;$1&gt;</em>' ],
			style: 'tt { color: #C00000; }'
		},
		brackets: {
			match: [ /([\(\)\{\}\[\]])/g ],
			replace: [ '<b>$1</b>' ],
			style: 'b { color: #A000A0; font-weight: bold; }'
		},
		numbers: {
			match: [ /\b(-?\d+)\b/g ],
			replace: [ '<u>$1</u>' ],
			style: 'u { color: #C00000; }'
		},
		keywords: {
			match: [ /\b(abs|accept|alarm|atan2|bind|binmode|bless|caller|chdir|chmod|chomp|chop|chown|chr|chroot|close|closedir|connect|continue|cos|crypt|dbmclose|dbmopen|defined|delete|die|do|dump|each|else|elsif|endgrent|endhostent|endnetent|endprotoent|endpwent|eof|eval|exec|exists|exit|fcntl|fileno|find|flock|for|foreach|fork|format|formlinegetc|getgrent|getgrgid|getgrnam|gethostbyaddr|gethostbyname|gethostent|getlogin|getnetbyaddr|getnetbyname|getnetent|getpeername|getpgrp|getppid|getpriority|getprotobyname|getprotobynumber|getprotoent|getpwent|getpwnam|getpwuid|getservbyaddr|getservbyname|getservbyport|getservent|getsockname|getsockopt|glob|gmtime|goto|grep|hex|hostname|if|import|index|int|ioctl|join|keys|kill|last|lc|lcfirst|length|link|listen|LoadExternals|local|localtime|log|lstat|map|mkdir|msgctl|msgget|msgrcv|msgsnd|my|next|no|oct|open|opendir|ordpack|package|pipe|pop|pos|print|printf|push|pwd|qq|quotemeta|qw|rand|read|readdir|readlink|recv|redo|ref|rename|require|reset|return|reverse|rewinddir|rindex|rmdir|scalar|seek|seekdir|select|semctl|semget|semop|send|setgrent|sethostent|setnetent|setpgrp|setpriority|setprotoent|setpwent|setservent|setsockopt|shift|shmctl|shmget|shmread|shmwrite|shutdown|sin|sleep|socket|socketpair|sort|splice|split|sprintf|sqrt|srand|stat|stty|study|sub|substr|symlink|syscall|sysopen|sysread|system|syswritetell|telldir|tie|tied|time|times|tr|truncate|uc|ucfirst|umask|undef|unless|unlink|until|unpack|unshift|untie|use|utime|values|vec|waitpid|wantarray|warn|while|write)\b/g ],
			replace: [ '<em>$1</em>' ],
			style: 'em, em tt { color: #0000C0; font-weight: normal; }'
		},
		variables: {
			match: [ /(<tt>)?([\$@%])(<\/tt>)?(<[^>]+>)?(\w+)(<\/[^>]+>)?\b/gi ],
			replace: [ '<ins>$2$5</ins>' ],
			style: 'ins { color: #909000; }'
		},
		strings: {
			match: [ /(".*?")/g, /('.*?')/g ],
			replace: [ '<s>$1</s>', '<s>$1</s>' ],
			style: 's, s u, s tt, s b, s em, s ins, s i { color: #008000; font-weight: normal; }'
		},
		comments: {
			match: [ /(#[^\n]*)(\n|$)/g, /(<tt>)?&lt;(<\/tt><tt>)?!--(<\/tt>)/gi, /(<tt>)?--(<\/tt><tt>)?&gt;(<\/tt>)?/gi ],
			replace: [ '<i>$1</i>$2', '<i>&lt;!--', '--&gt;</i>' ],
			style: 'i, i u, i tt, i b, i s, i em, i ins { color: #808080; font-weight: normal; }'
		}
	},

	xml: {
		tags: {
			match: [ /(&lt;\/?([\w\-:]+)(\s+.*?)?\/?&gt;)/gi ],
			replace: [ '<em>$1</em>' ],
			style: 'em { color: #0000C0; }'
		},
		attributes: {
			match: [ /([\w\-:]+)=(".*?")/g, /([\w\-]+)=('.*?')/g ],
			replace: [ '<u>$1</u>=<s>$2</s>', '<u>$1</u>=<s>$2</s>' ],
			style: 'u { color: #C00000; } s, s u, s em, s i { color: #008000; }'
		},
		comments: {
			match: [ /(&lt;!--)/g, /(--&gt;)/g ],
			replace: [ '<i>$1', '$1</i>' ],
			style: 'i, i u, i em { color: #808080; }'
		}
	},

	sql: {
		operators: {
			match: [ /((&amp;)+|(&lt;)+|(&gt;)+|[\|=]+|[!%\*\/\+\-])/gi ],
			replace: [ '<tt>$1</tt>' ],
			style: 'tt { color: #C00000; }'
		},
		numbers: {
			match: [ /\b(-?\d+)\b/g ],
			replace: [ '<u>$1</u>' ],
			style: 'u { color: #C00000; }'
		},
		commands: {
			match: [ /\b(abort|alter|analyze|begin|call|checkpoint|close|cluster|comment|commit|copy|create|deallocate|declare|delete|drop|end|execute|explain|fetch|grant|insert|listen|load|lock|move|notify|optimize|prepare|reindex|replace|reset|restart|revoke|rollback|select|set|show|start|truncate|unlisten|update)\b/gi ],
			replace: [ '<em>$1</em>' ],
			style: 'em { color: #0000C0; font-weight: bold; }'
		},
		keywords: {
			match: [ /\b(accessible|add|after|aggregate|alias|all|and|as|asc|authorization|auto_increment|between|both|by|cascade|cache|cache|called|cascade|case|character\s+set|charset|check|collate|column|comment|constraint|createdb|createuser|cycle|databases?|default|deferrable|deferred|delayed|desc|diagnostics|distinct(row)?|domain|duplicate|each|else|else?if|encrypted|except|exception|exists|false|fixed|for|force|foreign|from|full|function|get|group|having|high_priority|if|immediate|immutable|in|increment|index|inherits|initially|inner|input|intersect|into|invoker|is|join|key|language|left|like|limit|local|loop|low_priority|match|maxvalue|minvalue|natural|nextval|no|nocreatedb|nocreateuser|not|null|of|offset|oids|on|only|operator|or|order|outer|owner|partial|password|perform|plpgsql|primary|record|references|require|restrict|returns?|right|row|rule|schemas?|security|sensitive|separator|sequence|session|spatial|sql|stable|statistics|table|temp|temporary|terminated|then|to|trailing|transaction|trigger|true|type|unencrypted|union|unique|unsigned|user|using|valid|values?|view|volatile|when|where|while|with(out)?|xor|zerofill|zone)\b/gi ],
			replace: [ '<b>$1</b>' ],
			style: 'b { color: #0000E0; }'
		},
		types: {
			match: [ /\b(bigint|bigserial|binary|bit|blob|bool(ean)?|box|bytea|char(acter)?|cidr|circle|date(time)?|dec(imal)?|double|enum|float[48]?|inet|int[248]?|integer|interval|line|longblob|longtext|lseg|macaddr|mediumblob|mediumint|money|numeric|oid|path|point|polygon|precision|real|refcursor|serial[48]?|smallint|text|time(stamp)?|tinyblob|tinyint|varbinary|varbit|varchar(acter)?|year)\b/gi ],
			replace: [ '<ins>$1</ins>' ],
			style: 'ins { color: #909000; }'
		},
		strings: {
			match: [ /(".*?")/g, /('.*?')/g ],
			replace: [ '<s>$1</s>', '<s>$1</s>' ],
			style: 's, s b, s u, s tt, s em, s ins, s i { color: #008000; font-weight: normal; }'
		},
		comments: {
			match: [ /(#[^\n]*)(\n|$)/g ],
			replace: [ '<i>$1</i>$2' ],
			style: 'i, i b, i tt, i u, i s, i em, i ins { color: #808080; font-weight: normal; }'
		}
	}
}
