<?php

class language_support
{    
  public function get_language_index() {
    return array(
      "english" => "en_US",
      "german" => "de_DE");
  }
  
	public static function get_supported_languages()
	{
		return array(
				"de_DE"         => "deutsch",
				"en_US"         => "english",
				"zh_TW"         => "&#20013;&#25991;",
				"fr_FR"         => "fran&#231;ais",
				"ru_RU"         => "&#1088;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;",
				"es_ES"         => "espa&#241;ol",
				"tr_TR"         => "t&uuml;rk&#231;e"
			    );
	}

	public function get_default_language()
	{
		return LANGUAGE_DEFAULT;
	}

	public static function get_language()
	{
		if ( ! isset( $_SESSION[ "LANGUAGE_CHOSEN" ] ) )
		{
			$_SESSION[ "LANGUAGE_CHOSEN" ] = LANGUAGE_DEFAULT;
		}
		return $_SESSION[ "LANGUAGE_CHOSEN" ];
	}

	public static function choose_language( $language = "" )
	{
		if ( empty( $language ) )
		{
			$language = language_support::get_language();
		}
		$supported_languages = language_support::get_supported_languages();
		if ( ! array_key_exists( $language, $supported_languages ) )
		{
			throw new Exception( "Language not supported: " . $language . ".", E_PARAMETER );
		}
		$_SESSION[ "LANGUAGE_CHOSEN" ] = $language;
		language_support::initialize( $language );
	}

	public static function initialize( $language )
	{
		putenv( "LANG=" . 	$language . "." . CHARSET );
		putenv( "LANGUAGE=" . 	$language . "." . CHARSET );
		setlocale( LC_ALL, 	$language . "." . CHARSET, $language );
		if (ENABLE_GETTEXT) {
			bindtextdomain( "messages", PATH_LOCALE );
			bind_textdomain_codeset( "messages", CHARSET );
			textdomain( "messages" );
		}
	}

}

?>
