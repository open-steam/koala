<?php
/*
 * class for direct access to the sql database, used for frontend speedups
 */
class databaseAccess{
	public static function getUnreadMails($userName=""){
		$link = mysql_connect(STEAM_DATABASE_HOST, STEAM_DATABASE_USER, STEAM_DATABASE_PASS, true);
		if (!$link) {
		    error_log('no connection: ' . mysql_error());
		    die("Probleme mit der Datenbank. Wir arbeiten an einer L&ouml;sung.");
		}
		
		mysql_select_db("steam", $link);
		$result = mysql_query("SELECT login, ob_id FROM i_userlookup WHERE login='".$userName."'");
		
		
		$userObjectId = 0;
		while($row = mysql_fetch_array($result)){
			$userObjectId = $row["ob_id"];
		}
		
		if ($userObjectId==0){
			return 0;
		}
		
		//secound query
		$mailObjectIdsString = "";
		$result = mysql_query("SELECT ob_id,ob_ident,ob_data FROM ob_data WHERE ob_ident='annots' AND ob_id='".$userObjectId."'");
		while($row = mysql_fetch_array($result)){
			$mailObjectIdsString = $row["ob_data"];
		}
		
		//find the object numbers
		//extract object numbers from the string
		//these are the object ids of the mails
		$mailObjectIdsStringCut = $mailObjectIdsString;
		$objectNumbers = array();
		while (true){
			$firstPercent = stripos($mailObjectIdsStringCut,"%");
			$firstKomma = stripos($mailObjectIdsStringCut,",");
			if ($firstPercent===FALSE) break;
			$objectNumbers[]=substr($mailObjectIdsStringCut,$firstPercent+1,$firstKomma-$firstPercent-1);
			$mailObjectIdsStringCut = substr($mailObjectIdsStringCut,$firstKomma+1);
		}
		
		//build up a long sql string
		$mailsCount=0;
		$first = true;
		foreach ($objectNumbers as $mailObjectNumber){
			$mailsCount++;
			if($first){
				$first=false;
				$allMailsQuery = "SELECT k,v FROM i_read_documents WHERE k='".$mailObjectNumber."'";
			}else{
				$allMailsQuery.=" OR k='".$mailObjectNumber."'";
			}
		}
		$mailsCount--;
			
		
		//check if mails are read
		$allMailsData = array();
		$result = mysql_query($allMailsQuery);
		while($row = mysql_fetch_array($result)){
			$allMailsData[] = $row;
		}
		
		$readMailsCount=0;
		if (isset($allMailsData)) {
			foreach ($allMailsData as $mailData){
				if(stripos($mailData["v"],$userObjectId)!=FALSE){
					$readMailsCount++;
				}
			}
		}
				
		mysql_close($link);
  		$unreadMails = $mailsCount-$readMailsCount;
		return $unreadMails;
	}
}
?>