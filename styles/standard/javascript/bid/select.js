/* Options innerhalb einer Selectbox 1 Feld nach oben oder unten bewegen */

function selectUp(list, i) {
  if (i > 0) {
    selectSwap(list, i, i - 1);
    selectHighlight(list, i - 1);
  }
}

function selectDown(list, i) {
  if (i < list.length - 1) {
    selectSwap(list, i, i + 1);
    selectHighlight(list, i + 1);
  }
}

function selectSwap(list, a, b) {
  var objA = new Option(list.options[a].text, list.options[a].value);
  var objB = new Option(list.options[b].text, list.options[b].value);
  list.options[a] = objB;
  list.options[b] = objA;
}

function selectHighlight(list, pos) {
  for(var j = 0; j < list.length; j++) {
    if(j == pos)
      list.options[j].selected = true;
    else
      list.options[j].selected = false;
  }
}