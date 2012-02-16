function insert(aTag, eTag, form, element) {
  var input = document.forms[ form ].elements[ element ];
  input.focus();
  /* für Internet Explorer */
  if(typeof document.selection != 'undefined') {
    /* Einfügen des Formatierungscodes */
    var range = document.selection.createRange();
    var insText = range.text;
    range.text = aTag + insText + eTag;
    /* Anpassen der Cursorposition */
    range = document.selection.createRange();
    if (insText.length == 0) {
      range.move('character', -eTag.length);
    } else {
      range.moveStart('character', aTag.length + insText.length + eTag.length);      
    }
    range.select();
  }
  /* für neuere auf Gecko basierende Browser */
  else if(typeof input.selectionStart != 'undefined')
  {
    /* Einfügen des Formatierungscodes */
    var start = input.selectionStart;
    var end = input.selectionEnd;
    var insText = input.value.substring(start, end);
    input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
    /* Anpassen der Cursorposition */
    var pos;
    if (insText.length == 0) {
      pos = start + aTag.length;
    } else {
      pos = start + aTag.length + insText.length + eTag.length;
    }
    input.selectionStart = pos;
    input.selectionEnd = pos;
  }
  /* für die übrigen Browser */
  else
  {
    /* Abfrage der Einfügeposition */
    var pos;
    var re = new RegExp('^[0-9]{0,3}$');
    while(!re.test(pos)) {
      pos = prompt("Einfügen an Position (0.." + input.value.length + "):", "0");
    }
    if(pos > input.value.length) {
      pos = input.value.length;
    }
    /* Einfügen des Formatierungscodes */
    var insText = prompt("Bitte geben Sie den zu formatierenden Text ein:");
    input.value = input.value.substr(0, pos) + aTag + insText + eTag + input.value.substr(pos);
  }
}
var postmaxchars = 15000;
function checklength(theform) {
 if (postmaxchars != 0) message = " Die maximale Grenze liegt bei "+postmaxchars+" Zeichen."; else message = "";
 alert("Ihre Nachricht ist "+theform.eingabe.value.length+" Zeichen lang."+message);
}

function validate(theform) {
 if (theform.message.value=="" || theform.topic.value=="") { alert("Thema- und Nachrichtfeld müssen ausgefüllt werden!"); return false; }
 if (postmaxchars != 0) {
  if (theform.message.value.length > postmaxchars) {
   alert("Ihre Nachricht ist zu lang. Bitte reduzieren Sie Ihre Nachricht auf "+postmaxchars+" Zeichen. Momentan ist sie "+theform.message.value.length+" Zeichen lang.");
   return false;
  }
  else return true;
 } 
 else return true;
}
