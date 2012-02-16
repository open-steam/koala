  function openProperties(object_id) {
    window.open("./properties.php?properties=" + object_id, 'properties', 'resizable,scrollbars,width=560,height=450');
    return false;
  }

  function openIdcard(owner) {
    window.open("./idcard.php?user=" + owner, 'idcard', 'resizable,scrollbars,width=590,height=260');
    return false;
  }


  var que = unescape(parent.location.search);
  var que = que.substring(1, que.length);
  var que = que.split("&");
  querystring = new Array();
  for (var i=0; i<que.length; i++)  {
    tmp = que[i].split("=");
    querystring[tmp[0]] = tmp[1];
  }

  if ( querystring["visibleIds"] )
    visibleIds = querystring["visibleIds"].split("_");
  else
    visibleIds = new Array();

  sb_setTags('{TAGS}');
  sb_setImageURL('./libs/searchBar/images');
  sb_init('mySearchBar', '{LANGUAGE_TITLE_5}', '{LANGUAGE_TITLE_7}');
  sb_onSearchCallback( rowFilter );

  if ( querystring["tagFilter"] )
    sb_setSearchString(querystring["tagFilter"]);

  bidGrid = new dhtmlXGridObject('gridbox');
  filteredRows = new Array();

  var auswahlBreite = 25;
  var iconBreite = 30;
  var eigenschaftenBreite = 35;
  var restBreite = (document.getElementById("gridbox").offsetWidth-5-(auswahlBreite + iconBreite + eigenschaftenBreite))/100;
  var nameBreite = Math.ceil(restBreite*27);
  var beschreibungBreite = Math.ceil(restBreite*20);
  var tagBreite = Math.ceil(restBreite*20);
  var letzteAenderngBreite = Math.ceil(restBreite*15);
  var groesseBreite = Math.ceil(restBreite*8);
  var besitzerBreite = Math.ceil(restBreite*10);

  bidGrid.imgURL = "{DOC_ROOT}/libs/dhtmlxGrid/imgs/";
  bidGrid.setHeader("<input id='cb_selectAll' onClick='selectAllVisible()' type='checkbox'>, &nbsp;, &nbsp;,{LANGUAGE_TITLE_1},{LANGUAGE_TITLE_6},{LANGUAGE_TITLE_5}, {LANGUAGE_TITLE_2},{LANGUAGE_TITLE_3},{LANGUAGE_TITLE_4}");
  bidGrid.setInitWidths(auswahlBreite + ","+ iconBreite + ","+ eigenschaftenBreite + "," + nameBreite + ","+ beschreibungBreite + ","+ tagBreite + "," + letzteAenderngBreite + "," + groesseBreite + "," + besitzerBreite);
  bidGrid.setColAlign("center,center,center,left,left,left,left,right,left");
  bidGrid.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro,ro");
  bidGrid.setColSorting("na,na,str,str,str,str,str,str,str");
  bidGrid.enableResizing("false,false,false,true,true,true,true,true,true");
  bidGrid.enableTooltips("false,false,false,false,false,false,false,false,false");
  bidGrid.setSkin("light");		
  bidGrid.enableAutoHeigth(true, 400, true);
  bidGrid.setOnCheckHandler(doOnCheck);
  bidGrid.init();

  sb_onSearch();

  function doOnCheck(rowId, cellInd, state){

    var allChecked = true;
    var i = 0;

    while ( allChecked && i<bidGrid.getRowsNum() ) {
      if ( !filteredRows.search(i) && bidGrid.cells2(i,0).getValue()==0 )
        allChecked = false;
         i++;
      }

      if (bidGrid.getRowsNum()==0) allChecked = false;

      if (allChecked)
        document.getElementById("cb_selectAll").checked=true;
      else
        document.getElementById("cb_selectAll").checked=false;

      for (var i=0; i<bidGrid.getRowsNum(); i++) {
        if (bidGrid.cells2(i,0).getValue() == 1)
          document.getElementById("marked_" + bidGrid.getRowId(i)).checked=true;
        else
          document.getElementById("marked_" + bidGrid.getRowId(i)).checked=false;
        }

  }

  function selectAllVisible(){	

    for (var i=0; i<bidGrid.getRowsNum(); i++)
      if (!filteredRows.search(i))
        bidGrid.cells2(i,0).setValue(document.getElementById("cb_selectAll").checked?1:0);

   }

  function rowFilter() {

    if ( !sb_isValidSearchString() )
      return ;

    filteredRows.popAll();

    for (var i=0; i<bidGrid.getRowsNum(); i++) {
      if ( sb_parse(bidGrid.cells2(i,5).getValue()) )
        bidGrid.setRowHidden(bidGrid.getRowId(i), false);
      else {
        bidGrid.setRowHidden(bidGrid.getRowId(i), true);
        bidGrid.cells2(i,0).setValue(false);
        filteredRows.push(i);
      }
    }

    doOnCheck(-1, -1, false);

  }

  function showAll() {
    for (var i=0; i<bidGrid.getRowsNum(); i++)
      bidGrid.setRowHidden(bidGrid.getRowId(i), false);
    filteredRows.popAll();
    doOnCheck(-1, -1, false);
    sb_setSearchString("");
  }

  function showSelected() {
    filteredRows.popAll();
    for (var i=0; i<bidGrid.getRowsNum(); i++) {
      if ( bidGrid.cells2(i,0).getValue() == 1 )
        bidGrid.setRowHidden(bidGrid.getRowId(i), false);
      else {
        bidGrid.setRowHidden(bidGrid.getRowId(i), true);
        filteredRows.push(i);
      }
    }
    document.getElementById("cb_selectAll").checked=true;
  }

  function showPerspective() {
    var vids = "&visibleIds=";
    if (filteredRows.length!=0) {
      for (var i=0; i<bidGrid.getRowsNum(); i++)
        if (!filteredRows.search(i)) {
          vids = vids + bidGrid.getRowId(i) + "_";
        }
      vids = vids.substr(0, vids.length-1);
    } else vids = "";
    parent.top.location.href="{DOC_ROOT}/index.php?object=" + querystring["object"] + vids;
  }

  function showTagFilterPerspective() {
    var q = escape(sb_getSearchString());
    parent.top.location.href="{DOC_ROOT}/index.php?object=" + querystring["object"] + "&tagFilter=" + q;
  }





date = '{OBJECT_LAST_CHANGED}';
    date = date.slice(0, 10);
    day = date.slice(0, 2);
    month = date.slice(3, 5);
    year = date.slice(8, 10);
    time = '{OBJECT_LAST_CHANGED}';
    time = time.slice(11, 16);
    hour = time.slice(11, 13);
    min = time.slice(14, 16);

    sortableTimestamp = year + month + day + hour + min;
    
/*
    if (String('{bid:remark}').length>0)
      description = '<!--' + String('{bid:description}').toLowerCase() + '--><span onmouseover=\"overlib(\'{bid:remark}\')\"; onmouseout=\"nd();\">{bid:description}</span>';
    else
*/
      description = '<!--' + String('{bid:description}').toLowerCase() + '-->{bid:description}';

    bidGrid.addRow(
      '{OBJECT_ID}',
      '0,' +
      quoteString('{ITEM_PROPERTIES}') + ',' +
      '<!--{OBJECT_MIMETYPE}--><img src="{OBJECT_ICON}" border="0">,' +
      '<!--' + quoteString('{OBJECT_NAME}') + '-->&nbsp;' + quoteString('{ITEM_LINK}') + ',' +
      quoteString(description) + ',' +
      '{bid:tags},' +
      '<!--' + sortableTimestamp + '-->' + date + '&nbsp;' + time + ',' +
      '{OBJECT_SIZE},' +
      '<!--{OBJECT_OWNER}{OBJECT_NAME}-->&nbsp;<a href="{DOC_ROOT}/idcard.php?user={OBJECT_OWNER}" onclick="return openIdcard(\'{OBJECT_OWNER}\')">{OBJECT_OWNER}</span>'
    );

    if (visibleIds.length>0)
      if ( ! visibleIds.search('{OBJECT_ID}') ) {
        bidGrid.setRowHidden('{OBJECT_ID}', true);
        filteredRows.push(bidGrid.getRowsNum());
      }

    if ( querystring['tagFilter'] )
      if ( !sb_parse('{bid:tags}')) {
        bidGrid.setRowHidden('{OBJECT_ID}', true);
        filteredRows.push(bidGrid.getRowsNum());
      }