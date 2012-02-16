<?php
class searchsupport {
  
  private $steam_object;
  
  public function __construct( $steam_object ) {
    $this->steam_object = $steam_object;
  }
  
	public function search_user_posts( $message_board, $user )
	{
		return $this->steam_object->get_steam_connector()->predefined_command(
			$this->steam_object,
			"search_user_posts",
			array( $message_board, $user ),
			0
		);
	}
	
	public function search_messageboard( $message_board, $pattern )
	{
		return $this->steam_object->get_steam_connector()->predefined_command(
			$this->steam_object,
			"search_messageboard",
			array( $message_board, $pattern ),
			0
		);
	}
}
?>
