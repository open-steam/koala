<?php

function encrypt( $s, $key = NULL )
{
	if ( ! is_string( $key ) || empty( $key ) )
	{
		$key = session_id();
	}

	for ( $i = 0; $i <= strlen( $s ); $i++ )
	{
    if (!isset($r)) $r = "";
		$r .= substr( str_shuffle( md5( $key ) ), ( $i % strlen( md5( $key ) ) ), 1 ) . ($i < strlen($s)?$s[$i]:"");
	}
	
	$ss = "";
	for ( $i = 1; $i <= strlen( $r ); $i++ )
	{
		$a = ( $i % strlen( md5( $key ) ) );
		$b = ord( substr( md5( $key ), $a - 1, 1 ) );
		$c = ord( $r[$i - 1] );
		$ss = $ss . chr( $c + $b );
	}

	return urlencode( base64_encode( $ss ) );
	// return $s;
}

function decrypt( $s, $key = NULL )
{
	if ( ! is_string( $key ) || empty( $key ) )
	{
		$key = session_id();
	}

	$s = base64_decode( urldecode( $s ) );

	for ( $i = 1; $i <= strlen( $s ); $i++ )
	{
		$s[$i - 1] = chr( ord( $s[$i - 1] ) - ord( substr( md5( $key ), ( $i % strlen( md5( $key ) ) ) - 1, 1 ) ) );
	}
	for ( $i = 1; $i <= strlen( $s ) - 2; $i = $i + 2 )
	{
		if (!isset($r))  $r  = $s[$i];
    else             $r .= $s[$i];
	}
	return $r;
}

?>
