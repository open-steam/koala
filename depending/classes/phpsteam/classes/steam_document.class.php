<?php
/**
 * Implements the steam_document class
 *
 * Longer description follows
 *
 * PHP versions 5
 *
 * @package PHPsTeam
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Alexander Roth <aroth@it-roth.de>, Dominik Niehus <nicke@upb.de>
 */

/**
 * steam_document
 *
 * Longer description follows
 *
 * @package PHPsTeam
 */

class steam_document extends steam_object
{
	public function get_type() {
		return CLASS_DOCUMENT | CLASS_OBJECT;
	}
	
	/**
	 *function download:
	 *
	 * @return
	 */
	public function download()
	{
		//first run server request
		$request = new steam_request($this->get_steam_connector()->get_id(), $this->get_steam_connector()->get_transaction_id(), $this, 0, COAL_FILE_DOWNLOAD );
		$command = $this->get_steam_connector()->command( $request );
		$buffer = "";
		$size = 0;
		$args = $command->get_arguments();
		 
		//then get data
		while ( $size < $args[ 0 ] )
		{
			$buffer .= $this->get_steam_connector()->read_socket( $args[ 0 ] );
			$size = strlen( $buffer );
		}

		//if no error send headers
		header( "Pragma: private\n" );
		header( "Cache-Control: must-revalidate, post-check=0, pre-check=0\n" );
		header( "Content-Type: " . $this->get_attribute( "DOC_MIME_TYPE" ) . "\n");
		header( "Content-Disposition: attachment; filename=\"" . $this->get_name() . "\"\n" );
		header( "Content-Length: " . $this->get_content_size() . "\n");
		//return data
		return $buffer;
	}

	/**
	 * function get_reader:
	 *
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function get_reader( $pBuffer = 0 )
	{
		$module_read_doc = $this->get_steam_connector()->get_module( "table:read_documents" );
		return $this->steam_command(
		$module_read_doc,
			"get_reader",
		array( $this ),
		$pBuffer
		);
	}

	/**
	 * function is_read:
	 *
	 * @param $pUser
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function is_read( $pUser = "", $pBuffer = 0  )
	{
		$pUser = ( empty( $pUser ) ) ? $this->get_steam_connector()->get_current_steam_user() : $pUser;
		$module_read_doc = $this->get_steam_connector()->get_module( "table:read_documents" );
		return $this->steam_command(
		$module_read_doc,
			"is_reader",
		array( $this, $pUser ),
		$pBuffer
		);
	}

	/**
	 * function set_content:
	 *
	 * Sets the content of this document
	 * @param string $pContent document's content
	 * @param Boolean $pBuffer send now or buffer request?
	 * @return boolean TRUE|FALSE
	 */
	public function set_content( $pContent, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
			"set_content",
		array( $pContent ),
		$pBuffer
		);
	}

	/**
	 * function get_content_size:
	 *
	 * This function returns the content size in Byte
	 *Example:
	 *<code>
	 *$size = $myDocument->get_content_size()
	 *</code>
	 *
	 * @param Boolean $pBuffer send now or buffer request?
	 *
	 * @return Integer the content size in Byte
	 */
	public function get_content_size( $pBuffer = 0 )
	{
		$result = $this->steam_command(
		$this,
			"get_content_size",
		array(),
		$pBuffer
		);
		if ( $pBuffer == 0 )
		{
			$this->attributes[ "DOC_SIZE" ] = $result;
		}
		return $result;
	}

	/**
	 * function get_content_size:
	 *
	 * This function returns the content id
	 *Example:
	 *<code>
	 *$id = $myDocument->get_content_id()
	 *</code>
	 *
	 * @param Boolean $pBuffer send now or buffer request?
	 *
	 * @return Integer the content id
	 */
	public function get_content_id( $pBuffer = 0 )
	{
		$result = $this->steam_command(
		$this,
			"get_content_id",
		array(),
		$pBuffer
		);
		if ( $pBuffer == 0 )
		{
			$this->attributes[ "DOC_ID" ] = $result;
		}
		return $result;
	}


	/**
	 * function get_content:
	 * This function returns the content of the document
	 *
	 *Example:
	 *<code>
	 *$content = myDocument->get_content()
	 *</code>
	 * @param Boolean $pBuffer send now or buffer request?
	 *
	 * @return String content of the document
	 *
	 */
	public function get_content( $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
			"get_content",
		array(),
		$pBuffer
		);
	}

	/**
	 * function delete_thumbnail:
	 *
	 * Delete a specific thumbnail of this object
	 *
	 * Using this method is only possible on servers with server version >= 2.2.39
	 *
	 * @param integer $Width Width of the thumbnail to delete
	 * @param integer $Height height of the thumbnail to delete
	 * @param Boolean $pBuffer send now or buffer request?
	 *
	 */
	public function delete_thumbnail( $pWidth = -1, $pHeight = -1, $pBuffer = FALSE )
	{
		$version = $this->get_steam_connector()->get_server_version();
		list($major, $minor, $micro) = split('[.]', $version);
		if (((int)$major < 2) || ((int)$major == 2 && (int)$minor < 2) || ((int)$major == 2 && (int)$minor == 2 && (!isset($micro) ||  (int)$micro < 39) )) {
			throw new steam_exception( $this->get_steam_connector()->get_login_user_name(), "Error: get_thumbnail_data is not available on servers with version < 2.2.39 (actual server version is " . $version . ").", 404 );
		}
		$thumbnailsmodule = $this->get_steam_connector()->get_module("thumbnails");
		if (is_object($thumbnailsmodule)) {
			return $this->get_steam_connector()->predefined_command( $thumbnailsmodule, "delete_thumbnail", array( $this, $pWidth, $pHeight ), $pBuffer);
		}
		throw new steam_exception( $this->get_steam_connector()->get_login_user_name(), "Error: cant get module \"wiki\" from server.", 404 );
	}


	/**
	 * function delete_thumbnails:
	 *
	 * Deletes all thumbnails of this object
	 *
	 * Using this method is only possible on servers with server version >= 2.2.39
	 *
	 */
	public function delete_thumbnails( $pBuffer = 0 )
	{
		$version = $this->get_steam_connector()->get_server_version();
		list($major, $minor, $micro) = split('[.]', $version);
		if (((int)$major < 2) || ((int)$major == 2 && (int)$minor < 2) || ((int)$major == 2 && (int)$minor == 2 && (!isset($micro) ||  (int)$micro < 39) )) {
			throw new steam_exception($this->get_steam_connector()->get_login_user_name(), "Error: delete_thumbnails is not available on servers with version < 2.2.39 (actual server version is " . $version . ").", 404 );
		}
		$thumbnailsmodule = $this->get_steam_connector()->get_module("thumbnails");
		if (is_object($thumbnailsmodule)) {
			return $this->get_steam_connector()->predefined_command( $thumbnailsmodule, "delete_thumbnails", array( $this ), $pBuffer);
		}
		throw new steam_exception($this->get_steam_connector()->get_login_user_name(), "Error: cant get module \"wiki\" from server.", 404 );
	}


	/**
	 * function get_thumbnail:

	 * This function returns the content of an image document by using the
	 * thumbnails module of the server. Asking for a thumbnail of given width
	 * and/or height triggers the server to generate a matching thumbnail.
	 * Thumbnails will be handled completely by the server module thumbnails, so *
	 * generating thumbnails and cache them is up to the server
	 *
	 * Please make sure to call this method on image documents only. Otherwise
	 * a server side exception will be thrown indication this issue.
	 *
	 * Important notes:
	 * -Using this method is only possible on servers with server version >= 2.2.39
	 * -the generated thumbnails may not be from the same mime type as the source,
	 * so make sure to check the DOC_MIME_TYPE of the resulting image. (In the case
	 * you use this on a .tif image with -1,-1 the result is the tif image (no
	 * thumb generation necessary), if you call this method on a tif with 10,10 the * result may be from type jpg depending on the graphics features of your
	 * system. Type Safe thumbnail generation will be done for jpg, gif, png and
	 * bmp
	 * - To prevent thumbnail service from "out of memory"- problems, scaling
	 * images is limited to 25 Megapixels (5000x5000)
	 *
	 * @param integer $pWidth the required width of the thumbnail, -1 to determine
	 *        width by scaling to given height
	 * @param integer $pHeight the required height of the thumbnail, -1 to
	 *        determine height by scaling to given width
	 * @param integer $pIgnore_aspect_ratio Aspect ratio was respected by default,
	 *        pass 0 here to ignore aspect ratio.
	 * @param Boolean $pBuffer send now or buffer request?
	 *
	 * @return steam_object the thumbnail object
	 *
	 */
	public function get_thumbnail($pWidth = -1, $pHeight = -1, $pIgnore_aspect_ratio = 0 , $pBuffer = FALSE )
	{
		$version = $this->get_steam_connector()->get_server_version();
		list($major, $minor, $micro) = split('[.]', $version);
		if (((int)$major < 2) || ((int)$major == 2 && (int)$minor < 2) || ((int)$major == 2 && (int)$minor == 2 && (!isset($micro) ||  (int)$micro < 39) )) {
			throw new steam_exception($this->get_steam_connector()->get_login_user_name(), "Error: get_thumbnail is not available on servers with version < 2.2.39 (actual server version is " . $version . ").", 404 );
		}
		$thumbnailsmodule = $this->get_steam_connector()->get_module("thumbnails");
		if (is_object($thumbnailsmodule)) {
			$vars = array(
                "width" => $pWidth,
                "height" => $pHeight,
                "ignore_aspect_ratio" => $pIgnore_aspect_ratio
			);
			return $this->get_steam_connector()->predefined_command( $thumbnailsmodule, "get_image", array( $this, $vars ), $pBuffer);
		}
		throw new steam_exception($this->get_steam_connector()->get_login_user_name(), "Error: cant get module \"wiki\" from server.", 404 );
	}


	/**
	 * function get_thumbnail_data:
	 *
	 * This function returns the content of an image document by using the
	 * thumbnails module of the server. Asking for a thumbnail of given width
	 * and/or height triggers the server to generate a matching thumbnail.
	 * Thumbnails will be handled completely by the server module thumbnails, so *
	 * generating thumbnails and cache them is up to the server
	 *
	 * Please make sure to call this method on image documents only. Otherwise
	 * a server side exception will be thrown indication this issue.
	 *
	 * Important notes:
	 * -Using this method is only possible on servers with server version >= 2.2.39
	 * -Using this method on an animated gif results in a still (not animated)
	 * thumbnail
	 * -the generated thumbnails may not be from the same mime type as the source,
	 * so make sure to check the mimetype of the resulting image. (In the case
	 * you use this on a .tif image with -1,-1 the result is the tif image (no
	 * thumb generation necessary), if you call this method on a tif with 10,10 the * result may be from type jpg depending on the graphics features of your
	 * system. Type Safe thumbnail generation will be done for jpg, gif, png and
	 * bmp
	 * - To prevent thumbnail service from "out of memory"- problems, scaling
	 * images is limited to 25 Megapixels (5000x5000)
	 *
	 * @param integer $pWidth the required width of the thumbnail, -1 to determine
	 *        width by scaling to given height
	 * @param integer $pHeight the required height of the thumbnail, -1 to
	 *        determine height by scaling to given width
	 * @param integer $pIgnore_aspect_ratio Aspect ratio was respected by default,
	 *        pass 0 here to ignore aspect ratio.
	 * @param Boolean $pBuffer send now or buffer request?
	 *
	 * @return array data of the thumbnail image with keys:
	 *         "content" : contains the content of the thumbnail
	 *         "mimetype": contains the mimetype of the thumbnail (please note that
	 *                     it is possible to get a jpg-thumbnail of a png image)
	 *         "timestamp":last modified timestamp of the thumbnail
	 *
	 */
	public function get_thumbnail_data( $pWidth = -1, $pHeight = -1, $pIgnore_aspect_ratio = 0 , $pBuffer = FALSE )
	{
		$version = $this->get_steam_connector()->get_server_version();
		list($major, $minor, $micro) = @split('[.]', $version);
		if (((int)$major < 2) || ((int)$major == 2 && (int)$minor < 2) || ((int)$major == 2 && (int)$minor == 2 && (!isset($micro) ||  (int)$micro < 39) )) {
			throw new steam_exception($this->get_steam_connector()->get_login_user_name(), "Error: get_thumbnail_data is not available on servers with version < 2.2.39 (actual server version is " . $version . ").", 404 );
		}
		$thumbnailsmodule = $this->get_steam_connector()->get_module("thumbnails");
		if (is_object($thumbnailsmodule)) {
			$vars = array(
                "width" => $pWidth,
                "height" => $pHeight,
                "ignore_aspect_ratio" => $pIgnore_aspect_ratio
			);
			return $this->get_steam_connector()->predefined_command( $thumbnailsmodule, "get_image_data", array( $this, $vars ), $pBuffer);
		}
		throw new steam_exception($this->get_steam_connector()->get_login_user_name(), "Error: cant get module \"thumbnails\" from server.", 404 );
	}

	/**
	 * get wiki content as html
	 *
	 * Please make sure your document is of type wiki (mime type ="text/wiki"
	 * before calling this method.
	 *
	 * please note that you must replace the links within the wiki by hand
	 * because the pathes in the replace have to fit you applications pathes
	 * The Link Terms are:
	 * "/scripts/wikiedit.pike?path=&lt;internal path in steam&gt;"- links to a non existing wiki
	 * "&lt;internal path in steam&gt;" - links to another wiki
	 * You have to replace these links into some path your application is able
	 * to handle and process the "create wiki" e.g. the "show wiki" commands
	 *
	 * @param $pBuffer TRUE if buffer this command
	 * @return html representation of the wikis content
	 */
	public function get_content_html($pBuffer = FALSE) {
		$wikimodule = $steam_connector->get_module("wiki");
		if (is_object($wikimodule)) {
			return $steam_connector->predefined_command( $wikimodule, "wiki_to_html_plain", array( $document ), $pBuffer);
		}
		throw new steam_exception( $this->steam_connector->get_login_user_name(), "Error: cant get module \"wiki\" from server.", 404 );
	}

	public function get_version()
	{
		$version = (int) $this->get_attribute( "DOC_VERSION" );
		return $version;
	}

	public function get_previous_versions()
	{
		$versions = $this->get_attribute( "DOC_VERSIONS" );
		 
		if(is_array($versions) && !empty($versions) && count($versions) > 0)
  	{
  		krsort($versions);
  		$versions = array_values($versions);
  		return $versions;	
  	}
  	return FALSE;
  }
  
  public function is_previous_version_of()
  {
  	$doc = $this->get_attribute("OBJ_VERSIONOF");
  	if(is_object($doc))
  		return $doc;
  	return FALSE;
  }
}
?>