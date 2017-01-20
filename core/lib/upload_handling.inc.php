<?php
// TODO: Check if mysql connection is closed properly
mysql_connect(STEAM_DATABASE_HOST, STEAM_DATABASE_USER, STEAM_DATABASE_PASS);
mysql_select_db(STEAM_DATABASE);

function check_permissions($user, $oid, $password) {
  $query = "select v from i_users where k='" . $user . "'";
  $result = mysql_query($query);
  $row = mysql_fetch_row($result);
  $uid = $result[0];

  // if we dont have uid we can get this from i_users
  $query = "select v from i_security_cache where k='" . $oid . ":" . $uid . "'";
  $result = mysql_query($query);
  $row = mysql_fetch_row($result);
  $permissions = $row[0]; // read permission is bit "1"
  if (($permissions & 2)==2)
    return 1;
  else {
    $permission_denied = $permissions >> 16;
    if (($permission_denied & 2)===2)
      return 0;
    else {
      $STEAM = new steam_connector(STEAM_SERVER, STEAM_PORT, $user, $password );
      $document = steam_factory::get_object( $STEAM->get_id(), (int)$oid );
      if ($document->check_access(2, \lms_steam::get_current_user())===1)
	return 1;
    }
  }
  return 0;
}

function upload($content_id, $content)
{
  // http sends everything at once

  $query = "delete from doc_data where doc_id=" . $content_id;
  mysql_query($query);

  $iNextRecNr = 1;
  $max_buflen = 65504;
  $pos = 0;
  while ($pos < strlen($content)) {
	if (strlen($content)-$pos < $max_buflen)
	  $data = substr($content, $pos);
	else
	  $data = substr($content, $pos, $max_buflen);
	$data = mysql_real_escape_string($data);
	$query = "insert into doc_data values('" . $data . "', ". $content_id . ", " . $iNextRecNr . ")";
  	$result = mysql_query($query);
	$iNextRecNr++;
	$pos += $max_buflen;
  }
  return 1;
}

?>
