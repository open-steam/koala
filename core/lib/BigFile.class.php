<?php
class BigFile {
	private $file_handle;
	
	/**
	 * 
	 * Load the file from a filepath 
	 * @param string $path_to_file
	 * @throws Exception if path cannot be read from
	 */
	public function __construct( $path_to_log )
	{
	    if( is_readable($path_to_log) )
	    {
	        $this->file_handle = fopen( $path_to_log, 'r');
	    }
	    else
	    {
	        throw new Exception("The file path to the file is not valid");
	    } 
	}
	
	/**
	 * 
	 * 'Finish your breakfast' - Jay Z's homme Strict
	 */
	public function __destruct()
	{
	    fclose($this->file_handle); 
	}
	
	/**
	 * 
	 * Returns a number of characters from the end of a file w/o loading the entire file into memory
	 * @param integer $number_of_characters_to_get
	 * @return string $characters
	 */
	public function getFromEnd( $number_of_characters_to_get )
	{
	    $offset = -1*$number_of_characters_to_get;
	    $text = "";
	
	    fseek( $this->file_handle, $offset , SEEK_END);
	
	    while(!feof($this->file_handle))
	    {
	        $text .= fgets($this->file_handle);
	    }
	
	    return $text;
	}
}
?>