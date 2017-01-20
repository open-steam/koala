<?php
class downloader {

  private $content_id = -1;
  
  function connect_to_mysql() {
    $db_host = STEAM_DATABASE_HOST;
    $db_database = STEAM_DATABASE;
    $db_user = STEAM_DATABASE_USER;
    $db_password = STEAM_DATABASE_PASS;
    if ( !empty($db_host) && !empty($db_database) && !empty($db_user) ) {
      mysql_connect($db_host, $db_user, $db_password );
      mysql_select_db( $db_database );
    }
    else throw new Exception( "Unable to connect to database.", E_CONFIGURATION);
  }

  function disconnect_from_mysql() {
    mysql_close();
  }

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
    if (($permissions & 1)==1)
      return 1;
    else {
      $permission_denied = $permissions >> 16;
      if (($permission_denied & 1)==1)
        return 0;
      else {
        $STEAM = new steam_connector(STEAM_SERVER, STEAM_PORT, $user, $password );
        $document = steam_factory::get_object( $STEAM->get_id(), (int)$oid, CLASS_OBJECT );
        if ($document->check_access_read(\lms_steam::get_current_user())===1)
    return 1;
      }
    }
    return 0;
  }
  
  function is_modified($last_modified_server, $last_modified_http)
  {
    $timestamp_http = date2UnixTimestamp($last_modified_http);
    if ($last_modified_server > $timestamp_http)
      return 0;
    return 1;
  }
  
  function get_last_modified($oid)
  {
    $query = "select ob_id from ob_data where ob_attr='OBJ_PATH' and ob_data='\"" .
      $path . "\"'";
    $result = mysql_query($query);
    if (!$result)
      return "";
    $row = mysql_fetch_row($result);
    return $row[0];
  }
  
  function get_mime_type($oid)
  {
    $query = "select ob_data from ob_data where ob_attr='DOC_MIME_TYPE' and ob_i\
  d = " . $oid;
    $result = mysql_query($query);
    if ($result) {
      if ($row = mysql_fetch_row($result)) {
        return substr($row[0], 1, -1);
      }
    }
    return "application/x-unknown-content-type";
  }

  function get_content_id( $oid ) {
    if ($this->content_id === -1) {
      $query = "select ob_data from ob_data where ob_attr='CONTENT_ID' AND ob_id="
        . $oid;
      $result = mysql_query($query);
      if (!$result) {
        if (defined("LOG_DEBUGLOG")) {
          $time1 = microtime(TRUE);
          logging::write_log( LOG_DEBUGLOG, "download_handling::get_content_id\t" . $oid . " \tCONTENT_ID not set. (result is null)" );
        }
        return -1;
      }
      $row = mysql_fetch_row($result);
      if (!$row) {
        if (defined("LOG_DEBUGLOG")) {
          $time1 = microtime(TRUE);
          logging::write_log( LOG_DEBUGLOG, "download_handling::get_content_id\t" . $oid . " \tCONTENT_ID not set (row is null)." );
        }
        return -1;
      }
      $this->content_id = $row[0];
    }
    return $this->content_id;
  }

  
  function download_and_print($oid, $user)
  {
    $content_id = $this->get_content_id( $oid );
    
    if ($content_id === -1) {
      if (defined("LOG_DEBUGLOG")) {
        $time1 = microtime(TRUE);
        logging::write_log( LOG_DEBUGLOG, "download_handling::download_and_print\t" . $oid . " \tCONTENT_ID is -1 (not yet set in database)." );
      }
    }
    $query = "select rec_data from doc_data where doc_id=" . $content_id .
      " order by rec_order";
    $result = mysql_query($query);
    while ( $row = mysql_fetch_row($result) ) {
      print_r($row[0]);
    }
  }
  
  function download_and_print_path($path, $user)
  {
    $query = "select ob_id from ob_data where ob_attr='OBJ_PATH' and ob_data='\"" .
      $path . "\"'";
    $result = mysql_query($query);
    $row = mysql_fetch_row($result);
    $oid = $row[0];
    download_and_print($oid, $user);
  }
  
  function get_content_size( $contentid ) {
    $size = 0;
    $query = "select length(rec_data) from doc_data where doc_id=" . $contentid . " order by rec_order";
    $result = mysql_query($query);
    $i = 0;
    while ( $row = mysql_fetch_row($result) ) {
      $i++;
      $size += (int)$row[0];
    }
    return $size;
  }
  
  
  // TODO: Fill with values
  function get_document_attributes($id) {
    $mysql_data = array();
    $data = array();
    $rs = "";
    $query = "select * from ob_data where (ob_attr='DOC_MIME_TYPE' or ob_attr='OBJ_NAME' or ob_attr='DOC_LAST_MODIFIED' or ob_attr='OBJ_CREATION_TIME') and ob_id = " . $id;
    $result = mysql_query($query);
    if ($result) {
      while ($row = mysql_fetch_row($result)) {
        // store key value pairs in temp array
	//print("row[2]=" . $row[2] . " row[3]=" . $row[3]);
        $mysql_data[$row[2]] = str_replace('"', '', $row[3]);
      }
    } else {
      if (defined("LOG_DEBUGLOG")) {
        $time1 = microtime(TRUE);
        logging::write_log( LOG_DEBUGLOG, "download_handling::get_document_attributes\t" . $id . " \tresult is null fetching the needed attribute values" );
      }
    }
    $mime = "application/x-unknown-content-type";
    if (isset($mysql_data[DOC_MIME_TYPE]) && $mysql_data[DOC_MIME_TYPE] != "") $mime = $mysql_data[DOC_MIME_TYPE]; 
    $data["mimetype"]    = $mime;
  
    $lm = $mysql_data[OBJ_CREATION_TIME];
    if (isset($mysql_data[DOC_LAST_MODIFIED]) && $mysql_data[DOC_MIME_TYPE] != 0) $lm = $mysql_data[DOC_LAST_MODIFIED];
    
    $data["mimetype"]    = $mime;
    $data["lastmodified"]= $lm;
    $data["name"]        = $mysql_data[OBJ_NAME];
    $data["contentsize"] = $this->get_content_size( $this->get_content_id( $id ) );
    return $data;
  }
}
?>
