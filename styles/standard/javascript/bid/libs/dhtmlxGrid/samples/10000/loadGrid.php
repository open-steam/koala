<?php
	header("Content-type:text/xml");
	ini_set('max_execution_time', 600);
	require_once('config.php'); 
	print("<?xml version=\"1.0\"?>");
?>
<?php
	//$_GET['posStart'];
	if(isset($_GET["posStart"]))
		$posStart = $_GET['posStart'];
	else
		$posStart = 0;
	if(isset($_GET["count"]))
		$count = $_GET['count'];
	else
		$count = 100;
	if(isset($_GET["nm_mask"]))
		$nm_mask = $_GET['nm_mask'];
	else
		$nm_mask = "";
	if(isset($_GET["cd_mask"]))
		$cd_mask = $_GET['cd_mask'];
	else
		$cd_mask = "";
	
	//$_GET['count'];
	
	$link = mysql_pconnect($mysql_host, $mysql_user, $mysql_pasw);
	$db = mysql_select_db ($mysql_db);
	//Create database and table if doesn't exists
	if(!$db){
		//mysql_create_db($mysql_db,$link);
		$sql = "Create database ".$mysql_db;
	 	$res = mysql_query ($sql);
		$sql = "use ".$mysql_db;
	   	$res = mysql_query ($sql);
		$sql = "CREATE TABLE Grid (item_id INT UNSIGNED not null AUTO_INCREMENT,item_nm VARCHAR (200),item_cd VARCHAR (15),PRIMARY KEY ( item_id ))";

		$res = mysql_query ($sql);
		if(!$res){
			echo mysql_errno().": ".mysql_error()." at ".__LINE__." line in ".__FILE__." file<br>";
		}else{
			populateDBRendom();
		}
	}

	//sleep(10);
    $fields=  array("item_nm","","item_cd");
    if (!isset($_GET["orderBy"]))
		$_GET["orderBy"]=0;
    if (!isset($_GET["direction"]))
		$_GET["direction"]="ASC";

	getDataFromDB('','',$fields[$_GET["orderBy"]],$_GET["direction"]);
	mysql_close($link);
	
	
	//populate db with 10000 records
	function populateDBRendom(){
		$filename = getcwd()."/longtext.txt";
		$handle = fopen ($filename, "r");
		$contents = fread ($handle, filesize ($filename));
		$arWords = split(" ",$contents);
		//print(count($arWords));
		for($i=0;$i<count($arWords);$i++){
			$nm = $arWords[$i];
			$cd = rand(123456,987654);
			$sql = "INsert into Grid(item_nm,item_cd) Values('".$nm."','".$cd."')";
			mysql_query ($sql);
			if($i==9999)
				break;
		}
		fclose ($handle);
	}
	//print one level of the tree, based on parent_id
	function getDataFromDB($name_mask,$code_mask,$sort_by,$sort_dir){
		GLOBAL $posStart,$count,$nm_mask,$cd_mask;
		$sql = "SELECT  * FROM Grid Where 0=0";
		if($nm_mask!='')
			$sql.= " and item_nm like '$nm_mask%'";
		if($cd_mask!='')
			$sql.= " and item_cd like '$cd_mask%'";
		if($sort_dir=='')
			$sort_dir = "asc";
		if($sort_by!='')
			$sql.= " Order By $sort_by $sort_dir";
		//print($sql);
		if($posStart==0){
			$sqlCount = "Select count(*) as cnt from ($sql) as tbl";
			//print($sqlCount);
			$resCount = mysql_query ($sqlCount);
			while($rowCount=mysql_fetch_array($resCount)){
				$totalCount = $rowCount["cnt"];
			}
		}
		$sql.= " LIMIT ".$posStart.",".$count;
		$res = mysql_query ($sql);
		print("<rows total_count='".$totalCount."' pos='".$posStart."'>");
		if($res){
			while($row=mysql_fetch_array($res)){
				print("<row id='".$row['item_id']."'>");
					print("<cell>");
					print($row['item_nm']);//."[".$row['item_id']."]");	
					print("</cell>");
					print("<cell>");
						print("index is ".$posStart);	
					print("</cell>");
					print("<cell>");
					print($row['item_cd']);	
					print("</cell>");
				print("</row>");
				$posStart++;
			}
		}else{
			echo mysql_errno().": ".mysql_error()." at ".__LINE__." line in ".__FILE__." file<br>";
		}
		print("</rows>");
	}
?>
