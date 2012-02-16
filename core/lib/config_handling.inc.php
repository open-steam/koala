<?php

class Config {
	private $config_path;
	private $config_template_path;
	private $entries;
	private $undocumented_entries;

	/**
	 * Reads a config template and config file and parses them for documented
	 * and undocumented config entries.
	 * 
	 * @param String config_path file path to the config file
	 * @param String config_template_path file path to the config template
	 */
	function Config ( $config_path = NULL, $config_template_path = NULL ) {
		$this->config_path = $config_path;
		$this->config_template_path = $config_template_path;
		$this->entries = array();
		$this->undocumented_entries = array();
		
		$config = NULL;
		if ( is_string( $config_template_path ) ) {
			if ( !file_exists( $config_template_path ) || !is_array( $config = file( $config_template_path ) ) )
				throw new Exception( "Could not read config template file: " . $this->config_template_path );
		}
		else if ( is_array( $config_template_path ) )
			$config = $config_template_path;
		if ( is_array( $config ) ) {  // parse config template
			$current_question = NULL;
			$current_type = NULL;
			$current_info = NULL;
			foreach ( $config as $line ) {
				$match = array();
				// new question:
				if ( preg_match( "#^([ \t]*///[ \t]*)(" . implode( "|", ConfigEntry::get_config_types() ) . ")([ \t]*:[ \t]*)(.*)[ \t]*$#i", $line, $match ) > 0 ) {
					$current_type = $match[2];
					$current_question = $match[4];
					$current_info = NULL;
					continue;
				}
				// additional info:
				if ( preg_match( "#^([ \t]*///[ \t]*)([^/].*)[ \t]*$#", $line, $match ) > 0 ) {
					if ( !is_string( $current_info ) ) $current_info = "";
					$current_info .= $match[2];
					continue;
				}
				// config entry:
				if ( preg_match( "#^([ \t]*)([ \t]*define[ \t]*\([ \t]*\")([a-zA-Z0-9_]+)(\"[ \t]*,[ \t]*)\"?(?U)(.*)(?-U)\"?([ \t]*\)[ \t]*;.*)$#", $line, $match ) > 0 ) {
					if ( !is_string( $current_type ) || !is_string( $current_question ) ) {
						$this->add_undocumented_entry( $match[3], $match[5] );
						continue;
					}
					$this->add_entry( $match[3], $current_type, $current_question, $current_info, ConfigEntry::config_value_to_answer( $current_type, $match[5] ), NULL );
					continue;
				}
				// other comment:
				if ( preg_match( "#^[ \t]*//.*$#", $line ) > 0 )
					continue;
				// otherwise, the current config entry ends:
				$current_type = NULL;
				$current_question = NULL;
				$current_info = NULL;
			}
		}

		$config = NULL;
		if ( is_string( $config_path ) ) {
			if ( !file_exists( $config_path ) || !is_array( $config = file( $config_path ) ) )
				$config = NULL;
		}
		else if ( is_array( $config_path ) )
			$config = $config_path;
		if ( is_array( $config ) ) {  // parse config file
			$config = file( $config_path );
			foreach ( $config as $line ) {  // scan undocumented entries:
				$match = array();
				if ( preg_match( "#^([ \t]*)([ \t]*define[ \t]*\([ \t]*\")([a-zA-Z0-9_]+)(\"[ \t]*,[ \t]*)\"?(?U)(.*)(?-U)\"?([ \t]*\)[ \t]*;.*)$#", $line, $match ) < 1 )
					continue;
				if ( is_object( $this->get_undocumented_entry( $match[3] ) ) )
					$this->get_undocumented_entry( $match[3] )->set_config_answer( $match[5] );
			}
			if ( is_array($config) ) {
				foreach ( $this->entries as $entry )
					$entry->parse_config( $config );
			}
		}
	}

	/**
	 * Add a new config entry. Config entries are usually read from the config
	 * template. You can use this function to add new entries or to document
	 * undocumented config entries.
	 *
	 * @param String $config_key the config key of the entry
	 * @param String $config_type a config entry type (see ConfigEntry::get_config_types())
	 * @param String $question the question to ask the user when prompting for a value for this entry
	 * @param String $info additional info that can be shown to the user when describing this entry, or when prompting for a value
	 * @param String $default_answer the default answer to offer to the user when prompting for a value
	 * @param String $error_msg the error message to show to the user when he enters an invalid value
	 * @param Boolean $optional marks the entry as optional
	 * @return Object the new config entry
	 */
	function add_entry ( $config_key, $config_type, $question, $info = NULL, $default_answer = NULL, $error_msg = NULL, $optional = FALSE ) {
		$old_entry = $this->get_entry( $config_key );
		if ( is_object( $old_entry ) ) return $old_entry;
		$entry = $this->get_undocumented_entry( $config_key );
		if ( is_object( $entry ) ) {
			unset( $this->undocumented_entries[ $config_key ] );
			$entry->set_config_type( $config_type );
			$entry->set_question( $question );
			$entry->set_info( $info );
			if ( is_string( $default_answer ) ) $entry->set_default_answer( $default_answer );
			$entry->set_error_message( $error_msg );
			$entry->set_is_optional( $optional );
			$this->entries[ $config_key ] = $entry;
		}
		else {
			$entry = new ConfigEntry( $config_key, $config_type, $question, $info, $default_answer, $error_msg, $optional );
			$this->entries[ $config_key ] = $entry;
		}
		return $this->entries[ $config_key ];
	}

	function remove_entry ( $config_key ) {
		unset( $this->entries[ $config_key ] );
	}

	/**
	 * Add a new undocumented config entry. Undocumented config entries are
	 * usually read from the config template and are not asked for values from
	 * the user.
	 * If the config entry already exists as a documented config entry, then
	 * this will be returned instead (and no undocumented config entry will be
	 * created).
	 *
	 * @param String $config_key the config key of the entry
	 * @param String $default_answer the default answer for this entry
	 * @return unknown
	 */
	function add_undocumented_entry ( $config_key, $default_answer = NULL ) {
		$old_entry = $this->get_undocumented_entry( $config_key );
		if ( !is_object( $old_entry) ) $old_entry = $this->get_entry( $config_key );
		if ( is_object( $old_entry ) ) return $old_entry;
		$this->undocumented_entries[ $config_key ] = new ConfigEntry( $config_key, NULL, $config_key, NULL, $default_answer );
	}

	function remove_undocumented_entry ( $config_key ) {
		unset( $this->undocumented_entries[ $config_key ] );
	}

	/**
	 * Get all config entries that have been annotated with config type and
	 * question in the config template. All non-annotated config entries can
	 * be queried by the get_undocumented_entries() and
	 * get_undocumented_entries_default() functions.
	 *
	 * @return Array config entries
	 */
	function get_entries () {
		return $this->entries;
	}

	/**
	 * Get a single config entry that has been annotated with config type and
	 * question in the config template. Non-annotated config entries can be
	 * queried by the get_undocumented_entry() and get_undocumented_entry_default()
	 * functions.
	 *
	 * @param String $config_key the config key of the requested entry
	 * @return Object a config entry or NULL if no matching entry could be found
	 */
	function get_entry ( $config_key ) {
		if ( empty( $this->entries[ $config_key ] ) ) return FALSE;
		return $this->entries[ $config_key ];
	}

	/**
	 * Gets all undocumented config entries (that have not been annotated with
	 * config type and question in the config template).
	 * 
	 * @return Array the undocumented config entries
	 */
	function get_undocumented_entries () {
		return $this->undocumented_entries;
	}

	/**
	 * Get a single undocumented config entry (that has not been annotated with
	 * config type and question in the config template).
	 *
	 * @param String $config_key the config key of the requested undocumented entry
	 * @return Object a config entry or NULL if no matching entry could be found
	 */
	function get_undocumented_entry ( $config_key ) {
		if ( empty( $this->undocumented_entries[ $config_key ] ) ) return FALSE;
		return $this->undocumented_entries[ $config_key ];
	}

	/**
	 * Get an array of all config entries that have not been marked as
	 * having been asked.
	 *
	 * @return Array config entries that have not been asked yet
	 */
	function get_unasked () {
		$unasked = array();
		foreach ( $this->entries as $entry )
			if ( !$entry->has_been_asked() ) $unasked[] = $entry;
		return $unasked;
	}

	/**
	 * Reset all config entries to unasked status (marking them as not
	 * having been asked, yet).
	 */
	function reset_unasked () {
		foreach ( $this->entries as $entry )
			$entry->set_has_been_asked( FALSE );
	}

	/**
	 * Ask the user for answers to the config entries. This writes to and reads
	 * from the commandline (via fread and fputs). If you would like to offer
	 * the config options to the user in a web page, then you will have to write
	 * your own.
	 */
	function ask () {
		foreach ( $this->entries as $entry )
			if ( !$entry->is_optional() ) $entry->ask();
	}

	/**
	 * Get an array of entries whose values differ from those in the config file.
	 * These are thus entries that have been changed in regard to the config file.
	 *
	 * @return Array config entries that differ from the values in the config file
	 */
	function get_changes () {
		$changes = array();
		foreach ( $this->entries as $entry )
			if ( $entry->has_changed() )
				$changes[] = $entry;
		return $changes;
	}

	/**
	 * Get an array of undocumented entries whose values differ from those in the config file.
	 * These are thus undocumented entries that have been changed in regard to the config file.
	 *
	 * @return Array undocumented config entries that differ from the values in the config file
	 */
	function get_undocumented_changes () {
		$changes = array();
		$config = file( $this->config_path );
		foreach ( $this->undocumented_entries as $entry ) {
			if ( $entry->has_changed() )
				$changes[] = $entry;
		}
		return $changes;
	}

	/**
	 * Write the config values to the config file. The file structure remains
	 * unchanged, just the values of the config entries will be overwritten.
	 * Config entries that were not present in the config file will be appended
	 * to the end of the file.
	 *
	 * @return Boolean TRUE if the file could be written, FALSE if it could not
	 *   be written
	 */
	function write_config () {
		$config = file( $this->config_path );
		foreach ( $this->entries as $entry )
			$config = $entry->change_config( $config );
		$file = fopen( $this->config_path, "wb" );
		$written = fwrite( $file, implode( "", $config ) );
		fclose( $file );
		if ( $written == 0 ) return FALSE;
		foreach ( $this->entries as $entry )
			$entry->parse_config( $config );
		return TRUE;
	}
}


class ConfigEntry {
	private $config_key;
	private $config_type;
	private $question;
	private $info;
	private $regexp;
	private $error_msg;
	private $answer;
	private $default_answer;
	private $config_answer;
	private $optional;
	private $asked;

	static public function get_config_types () {
		return array( "Any", "String", "Number", "YesNo", "AbsolutePath", "URL", "LanguageCode" );
	}

	static public function get_config_type_regexp ( $config_type ) {
		$regexp = array(
			"any" => "#.*#",
			"string" => "#.+#",
			"number" => "#[0-9]+#",
			"yesno" => "#yes|no#",
			"absolutepath" => "#/.*#",
			"url" => "#(http|https)://.+#",
			"languagecode" => "#[a-z]+_[A-Z]+#",
		);
		if ( empty( $regexp[ strtolower( $config_type ) ] ) ) return FALSE;
		return $regexp[ strtolower( $config_type ) ];
	}

	static public function get_config_type_error_message ( $config_type ) {
		$error_msg = array(
			"any" => "Invalid input.",
			"string" => "Please enter a non-empty string.",
			"number" => "Please enter a number.",
			"yesno" => "Please enter 'yes' or 'no'.",
			"absolutepath" => "Please enter an absolute path.",
			"url" => "Please enter a valid URL (http:// or https://).",
			"languagecode" => "Please enter a valid language code, e.g. de_DE or en_US.",
		);
		if ( empty( $error_msg[ strtolower( $config_type ) ] ) ) return '';
		return $error_msg[ strtolower( $config_type ) ];
	}

	function ConfigEntry ( $config_key, $config_type, $question, $info = NULL ,$default_answer = NULL, $error_msg = NULL, $optional = FALSE ) {
		$this->config_key = $config_key;
		$this->config_type = strtolower( $config_type );
		$this->question = $question;
		$this->info = $info;
		$this->regexp = self::get_config_type_regexp( $this->config_type );
		$this->default_answer = $default_answer;
		$this->config_answer = NULL;
		$this->error_msg = $error_msg;
		$this->optional = $optional;
		$this->asked = FALSE;
		if ( !is_string( $this->error_msg ) )
			$this->error_msg = self::get_config_type_error_message( $this->config_type );
	}

	function parse_config ( $config_lines ) {
		foreach ( $config_lines as $line ) {
			$match = array();
			if ( preg_match( "#^([ \t]*)([ \t]*define[ \t]*\([ \t]*\")(" . $this->config_key . ")(\"[ \t]*,[ \t]*)\"?(?U)(.*)(?-U)\"?([ \t]*\)[ \t]*;.*)$#", $line, $match ) < 1 )
				continue;
			$this->config_answer = self::config_value_to_answer( $this->config_type, $match[5] );
			$this->answer = $this->config_answer;
			return TRUE;
		}
		return FALSE;
	}

	function change_config ( $config_lines ) {
		for ( $i=0; $i<count($config_lines); $i++ ) {
			$line = $config_lines[$i];
			$match = array();
			if ( preg_match( "#^([ \t]*)(//|.{0})([ \t]*define[ \t]*\([ \t]*\")(" . $this->config_key . ")(\"[ \t]*,[ \t]*)\"?(?U)(.*)(?-U)\"?([ \t]*\)[ \t]*;.*)$#", $line, $match ) < 1 )
				continue;
			$config_lines[$i] = $match[1] . $match[3] . $this->config_key . $match[5] . $this->get_config_value() . $match[7] . "\n";
			$this->config_answer = $this->answer;
			return $config_lines;
		}
		// not found, append to end of file (before the closing php-tag):
		for ( $i=count( $config_lines )-1; $i>0; $i-- ) {
			if ( strpos( $config_lines[$i], '?>' ) !== FALSE ) break;
		}
		if ( $i == 0 ) $i = count( $config_lines );
		array_splice( $config_lines, $i, 0, array( "\n", "define( \"" . $this->config_key . "\", " . $this->get_config_value() . " );\n" ) );
/*
		
			
			
			{
				$endline = $config_lines[$i];
				unset( $config_lines[$i] );
				break;
			}
		}
		$config_lines[] = "\n";
		$config_lines[] = "define( \"" . $this->config_key . "\", " . $this->get_config_value() . " );\n";
		if ( !empty( $endline ) ) $config_lines[] = $endline;
			
/*			
			if ( strpos( $config_lines[$i], "?>" ) === FALSE ) continue;
			$new_line = "define( \"" . $this->config_key . "\", " . $this->get_config_value() . " );\n";
			$config_before = array_slice( $config_lines, 0, $i-1);
			$config_after = array_slice( $config_lines, $i );
			$config_lines = array_merge( $config_before, array( "\n", $new_line ), $config_after );
echo "BEFORE: " . $config_before[ count($config_before) - 1 ];
echo "AFTER: " . $config_before[ count($config_after) - 1 ];
echo "LINES: before=" . count($config_before) . ", after=" . count($config_after) . ", new-total=" . count($config_lines) . "\n";
			return $config_lines;
		}
*/
		return $config_lines;
	}

	function ask () {
		$this->asked = TRUE;
		do {
			fputs( STDOUT, $this->question );
			if ( $this->is_optional() ) {
				fputs( STDOUT, ' (optional' );
				if ( self::is_answer( $this->default_answer ) )
					fputs( STDOUT, ', default: ' . $this->default_answer );
				fputs( STDOUT, '): ' );
			}
			else if ( self::is_answer( $this->answer ) ) {
				if ( self::is_answer( $this->default_answer ) && $this->default_answer !== $this->answer )
					fputs( STDOUT, ' (default: ' . $this->default_answer . ')' );
				fputs( STDOUT, ' [' . $this->answer . ']: ' );
			}
			else if ( self::is_answer( $this->default_answer ) ) {
				fputs( STDOUT, ' [' . $this->default_answer . ']: ' );
			}
			else
				fputs( STDOUT, ': ' );
			$line = trim( fgets( STDIN, 64000 ) );
			if ( !is_string($line) || empty($line) ) {
				if ( $this->is_optional() ) {
					if ( !is_string( $this->default_answer ) )
						return $this->answer;
					$ask_continue = new ConfigEntry( "continue", "YesNo", "Would you like to use the default value '" . $this->default_answer . "'?", NULL, "no" );
					if ( $ask_continue->ask() == "yes" )
						$line = $this->default_answer;
					else
						return $this->answer;
				}
				else if ( self::is_answer( $this->answer ) )
					$line = $this->answer;
				else if ( self::is_answer( $this->default_answer ) )
					$line = $this->default_answer;
			}
			if ( is_string($line) ) {
				switch ( strtolower( $this->config_type ) ) {
					case 'yesno' :
						$line = strtolower( $line );
						break;
					case 'absolutepath' :
					case 'url' :
						if ( strlen( $line ) > 1 ) $line = rtrim( $line, "/" );
						break;
				}
			}
			if ( !is_string($this->regexp) || preg_match( $this->regexp, $line ) != 0 ) {
				$this->answer = $line;
				return $line;
			}
			fputs( STDOUT, $this->error_msg . "\n" );
		} while ( true );
	}

	function is_optional () {
		return $this->optional;
	}

	function set_is_optional ( $optional ) {
		$this->optional = $optional;
	}
	
	function get_config_key () {
		return $this->config_key;
	}

	function get_type () {
		return $this->config_type;
	}

	function set_config_type ( $config_type ) {
		$this->config_type = $config_type;
	}

	function get_question () {
		return $this->question;
	}

	function set_question ( $question ) {
		$this->question = $question;
	}

	function get_info () {
		return $this->info;
	}

	function set_info ( $info ) {
		$this->info = $info;
	}

	function get_error_message () {
		return $this->error_msg;
	}

	function set_error_message ( $error_message ) {
		$this->error_msg = $error_message;
	}

	function has_been_asked () {
		return $this->asked;
	}

	function set_has_been_asked ( $asked ) {
		$this->asked = $asked;
	}

	function get_answer () {
		return $this->answer;
	}

	function set_answer ( $answer ) {
		$this->answer = $answer;
	}

	function get_default_answer () {
		return $this->default_answer;
	}

	function set_default_answer ( $default_answer ) {
		$this->default_answer = $default_answer;
	}

	function get_config_answer () {
		return $this->config_answer;
	}

	function set_config_answer ( $config_answer ) {
		$this->config_answer = $config_answer;
	}

	function has_changed () {
		return ( is_string( $this->answer) ) && ($this->answer !== $this->config_answer);
	}

	function get_config_value () {
		return self::answer_to_config_value( $this->config_type, $this->answer );
	}

	static function config_value_to_answer ( $config_type, $value ) {
		switch ( strtolower( $config_type ) ) {
			case "yesno" :
				if ( strtolower( $value ) === "true" ) return "yes";
				else return "no";
			case "string" :
				return trim( $value, "\"" );
		}
		return $value;
	}

	static function answer_to_config_value ( $config_type, $answer ) {
		switch ( strtolower( $config_type ) ) {
			case "yesno" :
				if ( strtolower( $answer ) === "yes" ) return "TRUE";
				else return "FALSE";
			case "number" :
				return $answer;
		}
		return "\"" . $answer . "\"";
	}

	static function is_answer ( $answer ) {
		if ( $answer === FALSE || $answer === NULL ) return FALSE;
		return TRUE;
	}

}

?>
