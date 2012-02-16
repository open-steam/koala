<?php
include_once( "../../etc/koala.conf.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();
$min_mb = 8;
if( !lms_steam::is_koala_admin($user) )
{
	header("location:/");
	exit;
}

$link = mysql_connect(STEAM_DATABASE_HOST, STEAM_DATABASE_USER, STEAM_DATABASE_PASS, true);
if (!$link) {
    die('no connection: ' . mysql_error());
}

mysql_select_db("steam", $link);
$result = mysql_query("SELECT doc_id,COUNT(*) AS cnt FROM doc_data GROUP BY doc_id ORDER BY cnt DESC");

$html = <<< END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>koaLA Admin - find big files</title>
<style type="text/css">
* {margin:0; padding:0; outline:0}
body {font:11px Verdana,Arial; margin:25px; background:#fff url(images/bg.gif) repeat-x}
#tablewrapper {width:980px; margin:0 auto}
#tableheader {height:55px}
.search {float:left; padding:6px; border:1px solid #c6d5e1; background:#fff}
#tableheader select {float:left; font-size:12px; width:125px; padding:2px 4px 4px}
#tableheader input {float:left; font-size:12px; width:225px; padding:2px 4px 4px; margin-left:4px}
.details {float:right; padding-top:12px}
.details div {float:left; margin-left:15px; font-size:12px}
.tinytable {width:979px; border-left:1px solid #c6d5e1; border-top:1px solid #c6d5e1; border-bottom:none}
.tinytable th {background:url(images/header-bg.gif); text-align:left; color:#cfdce7; border:1px solid #fff; border-right:none}
.tinytable th h3 {font-size:10px; padding:6px 8px 8px}
.tinytable td {padding:4px 6px 6px; border-bottom:1px solid #c6d5e1; border-right:1px solid #c6d5e1}
.tinytable .head h3 {background:url(images/sort.gif) 7px center no-repeat; cursor:pointer; padding-left:18px}
.tinytable .desc, .sortable .asc {background:url(images/header-selected-bg.gif)}
.tinytable .desc h3 {background:url(images/desc.gif) 7px center no-repeat; cursor:pointer; padding-left:18px}
.tinytable .asc h3 {background:url(images/asc.gif) 7px  center no-repeat; cursor:pointer; padding-left:18px}
.tinytable .head:hover, .tinytable .desc:hover, .tinytable .asc:hover {color:#fff}
.tinytable .evenrow td {background:#fff}
.tinytable .oddrow td {background:#ecf2f6}
.tinytable td.evenselected {background:#ecf2f6}
.tinytable td.oddselected {background:#dce6ee}
.tinytable tfoot {background:#fff; font-weight:bold}
.tinytable tfoot td {padding:6px 8px 8px}
#tablefooter {height:15px; margin-top:20px}
#tablenav {float:left}
#tablenav img {cursor:pointer}
#tablenav div {float:left; margin-right:15px}
#tablelocation {float:right; font-size:12px}
#tablelocation select {margin-right:3px}
#tablelocation div {float:left; margin-left:15px}
.page {margin-top:2px; font-style:italic}
#selectedrow td {background:#c6d5e1}
</style>
</head>
<body>
	<div id="tablewrapper">
		<h1>Suche nach großen Dateien</h1>
		<div id="tableheader" style="display:none">
        	<div class="search">
                <select id="columns" onchange="sorter.search('query')"></select>
                <input type="text" id="query" onkeyup="sorter.search('query')" />
            </div>
            <span class="details">
				<div>Records <span id="startrecord"></span>-<span id="endrecord"></span> of <span id="totalrecords"></span></div>
        		<div><a href="javascript:sorter.reset()">reset</a></div>
        	</span>
        </div>
        <table cellpadding="0" cellspacing="0" border="0" id="table" class="tinytable">
            <thead>
                <tr>
                    <th class="nosort"><h3>ob_id</h3></th>
                    <th><h3>Size</h3></th>
                    <th><h3>Path</h3></th>
                    <th><h3>Owner ID</h3></th>
                    <th><h3>Owner</h3></th>
                </tr>
            </thead>
            <tbody>

END;

$creators = array();


$content_ids = array();
$result2 = mysql_query("SELECT * FROM ob_data WHERE ob_attr='CONTENT_ID'");
while ($row2 = mysql_fetch_array($result2)) {
	$content_ids[$row2["ob_data"]] = $row2["ob_id"];
}

$count = 0;
while($row = mysql_fetch_array($result)){
	$mb = round(((integer)$row["cnt"] * 65503) / 1024 / 1024); 
	if ($mb < $min_mb) {
		break;
	}
	$count ++;
	$result3 = mysql_query("SELECT * FROM ob_data WHERE ob_id=".$content_ids[$row["doc_id"]]." AND (ob_attr='OBJ_PATH' OR ob_attr='Creator')");
	while ($row3 = mysql_fetch_array($result3)) {
		if ($row3["ob_attr"] == "OBJ_PATH") {
			$path = $row3["ob_data"];
		} else if ($row3["ob_attr"] == "Creator") {
			$creator_id = $row3["ob_data"];
			$creator_id = substr($creator_id, 1);
			if (isset($creators[$creator_id])) {
				$creator_name = $creators[$creator_id];
			} else {
				$result4 = mysql_query("SELECT * FROM ob_data WHERE ob_id=".$creator_id." AND ob_attr='OBJ_NAME'");
				$row4 = mysql_fetch_array($result4);
				$creator_name = $row4["ob_data"];
				$creators[$creator_id] = $creator_name;
			}
		}
	}
	$ob_id = $content_ids[$row["doc_id"]];
	
	$html .= "<tr><td>".$ob_id."</td><td>".$mb."MB</td><td>".$path."</td><td>".$creator_id."</td><td>$creator_name</td></tr>";
}

$html .= <<< END
           </tbody>
        </table>
       <div id="tablefooter" style="display:none">
          <div id="tablenav">
            	<div>
                    <img src="images/first.gif" width="16" height="16" alt="First Page" onclick="sorter.move(-1,true)" />
                    <img src="images/previous.gif" width="16" height="16" alt="First Page" onclick="sorter.move(-1)" />
                    <img src="images/next.gif" width="16" height="16" alt="First Page" onclick="sorter.move(1)" />
                    <img src="images/last.gif" width="16" height="16" alt="Last Page" onclick="sorter.move(1,true)" />
                </div>
                <div>
                	<select id="pagedropdown"></select>
				</div>
                <div>
                	<a href="javascript:sorter.showall()">view all</a>
                </div>
            </div>
			<div id="tablelocation">
            	<div>
                    <select onchange="sorter.size(this.value)">
                    <option value="5">5</option>
                        <option value="10" selected="selected">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>Entries Per Page</span>
                </div>
                <div class="page">Page <span id="currentpage"></span> of <span id="totalpages"></span></div>
            </div>
        </div>
    </div>
	<script type="text/javascript">
var TINY={};function T$(i){return document.getElementById(i)}function T$$(e,p){return p.getElementsByTagName(e)}TINY.table=function(){function sorter(n,t,p){this.n=n;this.id=t;this.p=p;if(this.p.init){this.init()}}sorter.prototype.init=function(){this.set();var t=this.t,i=d=0;t.h=T$$('tr',t)[0];t.l=t.r.length;t.w=t.r[0].cells.length;t.a=[];t.c=[];this.p.is=this.p.size;if(this.p.colddid){d=T$(this.p.colddid);var o=document.createElement('option');o.value=-1;o.innerHTML='All Columns';d.appendChild(o)}for(i;i<t.w;i++){var c=t.h.cells[i];t.c[i]={};if(c.className!='nosort'){c.className=this.p.headclass;c.onclick=new Function(this.n+'.sort('+i+')');c.onmousedown=function(){return false}}if(this.p.columns){var l=this.p.columns.length,x=0;for(x;x<l;x++){if(this.p.columns[x].index==i){var g=this.p.columns[x];t.c[i].format=g.format==null?1:g.format;t.c[i].decimals=g.decimals==null?2:g.decimals}}}if(d){var o=document.createElement('option');o.value=i;o.innerHTML=T$$('h3',c)[0].innerHTML;d.appendChild(o)}}this.reset()};sorter.prototype.reset=function(){var t=this.t;t.t=t.l;for(var i=0;i<t.l;i++){t.a[i]={};t.a[i].s=1}if(this.p.sortcolumn!=undefined){this.sort(this.p.sortcolumn,1,this.p.is)}else{if(this.p.paginate){this.size()}this.alt();this.sethover()}this.calc()};sorter.prototype.sort=function(x,f,z){var t=this.t;t.y=x;var x=t.h.cells[t.y],i=0,n=document.createElement('tbody');for(i;i<t.l;i++){t.a[i].o=i;var v=t.r[i].cells[t.y];t.r[i].style.display='';while(v.hasChildNodes()){v=v.firstChild}t.a[i].v=v.nodeValue?v.nodeValue:''}for(i=0;i<t.w;i++){var c=t.h.cells[i];if(c.className!='nosort'){c.className=this.p.headclass}}if(t.p==t.y&&!f){t.a.reverse();x.className=t.d?this.p.ascclass:this.p.descclass;t.d=t.d?0:1}else{t.p=t.y;f&&this.p.sortdir==-1?t.a.sort(cp).reverse():t.a.sort(cp);t.d=0;x.className=this.p.ascclass}for(i=0;i<t.l;i++){var r=t.r[t.a[i].o].cloneNode(true);n.appendChild(r)}t.replaceChild(n,t.b);this.set();this.alt();if(this.p.paginate){this.size(z)}this.sethover()};sorter.prototype.sethover=function(){if(this.p.hoverid){for(var i=0;i<this.t.l;i++){var r=this.t.r[i];r.setAttribute('onmouseover',this.n+'.hover('+i+',1)');r.setAttribute('onmouseout',this.n+'.hover('+i+',0)')}}};sorter.prototype.calc=function(){if(this.p.sum||this.p.avg){var t=this.t,i=x=0,f,r;if(!T$$('tfoot',t)[0]){f=document.createElement('tfoot');t.appendChild(f)}else{f=T$$('tfoot',t)[0];while(f.hasChildNodes()){f.removeChild(f.firstChild)}}if(this.p.sum){r=this.newrow(f);for(i;i<t.w;i++){var j=r.cells[i];if(this.p.sum.exists(i)){var s=0,m=t.c[i].format||'';for(x=0;x<this.t.l;x++){if(t.a[x].s){s+=parseFloat(t.r[x].cells[i].innerHTML.replace(/(\$|\,)/g,''))}}s=decimals(s,t.c[i].decimals?t.c[i].decimals:2);s=isNaN(s)?'n/a':m=='$'?s=s.currency(t.c[i].decimals):s+m;r.cells[i].innerHTML=s}else{r.cells[i].innerHTML='&nbsp;'}}}if(this.p.avg){r=this.newrow(f);for(i=0;i<t.w;i++){var j=r.cells[i];if(this.p.avg.exists(i)){var s=c=0,m=t.c[i].format||'';for(x=0;x<this.t.l;x++){if(t.a[x].s){s+=parseFloat(t.r[x].cells[i].innerHTML.replace(/(\$|\,)/g,''));c++}}s=decimals(s/c,t.c[i].decimals?t.c[i].decimals:2);s=isNaN(s)?'n/a':m=='$'?s=s.currency(t.c[i].decimals):s+m;j.innerHTML=s}else{j.innerHTML='&nbsp;'}}}}};sorter.prototype.newrow=function(p){var r=document.createElement('tr'),i=0;p.appendChild(r);for(i;i<this.t.w;i++){r.appendChild(document.createElement('td'))}return r};sorter.prototype.alt=function(){var t=this.t,i=x=0;for(i;i<t.l;i++){var r=t.r[i];if(t.a[i].s){r.className=x%2==0?this.p.evenclass:this.p.oddclass;var cells=T$$('td',r);for(var z=0;z<t.w;z++){cells[z].className=t.y==z?x%2==0?this.p.evenselclass:this.p.oddselclass:''}x++}if(!t.a[i].s){r.style.display='none'}}};sorter.prototype.page=function(s){var t=this.t,i=x=0,l=s+parseInt(this.p.size);if(this.p.totalrecid){T$(this.p.totalrecid).innerHTML=t.t}if(this.p.currentid){T$(this.p.currentid).innerHTML=this.g}if(this.p.startingrecid){var b=((this.g-1)*this.p.size)+1,m=b+(this.p.size-1);m=m<t.l?m:t.t;m=m<t.t?m:t.t;T$(this.p.startingrecid).innerHTML=t.t==0?0:b;T$(this.p.endingrecid).innerHTML=m}for(i;i<t.l;i++){var r=t.r[i];if(t.a[i].s){r.style.display=x>=s&&x<l?'':'none';x++}else{r.style.display='none'}}};sorter.prototype.move=function(d,m){this.goto(d==1?(m?this.d:this.g+1):(m?1:this.g-1))};sorter.prototype.goto=function(s){if(s<=this.d&&s>0){this.g=s;this.page((s-1)*this.p.size)}};sorter.prototype.size=function(s){var t=this.t;if(s){this.p.size=s}this.g=1;this.d=Math.ceil(this.t.t/this.p.size);if(this.p.navid){T$(this.p.navid).style.display=this.d<2?'none':'block'}this.page(0);if(this.p.totalid){T$(this.p.totalid).innerHTML=t.t==0?1:this.d}if(this.p.pageddid){var d=T$(this.p.pageddid),l=this.d+1;d.setAttribute('onchange',this.n+'.goto(this.value)');while(d.hasChildNodes()){d.removeChild(d.firstChild)}for(var i=1;i<=this.d;i++){var o=document.createElement('option');o.value=i;o.innerHTML=i;d.appendChild(o)}}};sorter.prototype.showall=function(){this.size(this.t.t)};sorter.prototype.search=function(f){var i=x=n=0,k=-1,q=T$(f).value.toLowerCase();if(this.p.colddid){k=T$(this.p.colddid).value}var s=(k==-1)?0:k,e=(k==-1)?this.t.w:parseInt(s)+1;for(i;i<this.t.l;i++){var r=this.t.r[i],v;if(q==''){v=1}else{for(x=s;x<e;x++){var b=r.cells[x].innerHTML.toLowerCase();if(b.indexOf(q)==-1){v=0}else{v=1;break}}}if(v){n++}this.t.a[i].s=v}this.t.t=n;if(this.p.paginate){this.size()}this.calc();this.alt()};sorter.prototype.hover=function(i,d){this.t.r[i].id=d?this.p.hoverid:''};sorter.prototype.set=function(){var t=T$(this.id);t.b=T$$('tbody',t)[0];t.r=t.b.rows;this.t=t};Array.prototype.exists=function(v){for(var i=0;i<this.length;i++){if(this[i]==v){return 1}}return 0};Number.prototype.currency=function(c){var n=this,d=n.toFixed(c).split('.');d[0]=d[0].split('').reverse().join('').replace(/(\d{3})(?=\d)/g,'$1,').split('').reverse().join('');return'$'+d.join('.')};function decimals(n,d){return Math.round(n*Math.pow(10,d))/Math.pow(10,d)};function cp(f,c){var g,h;f=g=f.v.toLowerCase();c=h=c.v.toLowerCase();var i=parseFloat(f.replace(/(\$|\,)/g,'')),n=parseFloat(c.replace(/(\$|\,)/g,''));if(!isNaN(i)&&!isNaN(n)){g=i,h=n}i=Date.parse(f);n=Date.parse(c);if(!isNaN(i)&&!isNaN(n)){g=i;h=n}return g>h?1:(g<h?-1:0)};return{sorter:sorter}}();
	</script>
	<script type="text/javascript">
	var sorter = new TINY.table.sorter('sorter','table',{
		headclass:'head',
		ascclass:'asc',
		descclass:'desc',
		evenclass:'evenrow',
		oddclass:'oddrow',
		evenselclass:'evenselected',
		oddselclass:'oddselected',
		paginate:true,
		size:$count,
		colddid:'columns',
		currentid:'currentpage',
		totalid:'totalpages',
		startingrecid:'startrecord',
		endingrecid:'endrecord',
		totalrecid:'totalrecords',
		hoverid:'selectedrow',
		pageddid:'pagedropdown',
		navid:'tablenav',
		sortcolumn:1,
		sortdir:-1,
		//sum:[8],
		//avg:[6,7,8,9],
		columns:[{index:7, format:'%', decimals:1},{index:8, format:'$', decimals:0}],
		init:true
	});
  </script>
</body>
</html>
END;

echo $html;

?>