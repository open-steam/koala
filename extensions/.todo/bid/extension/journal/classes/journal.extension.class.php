<?php
require_once(PATH_PUBLIC . "bid/extension/journal/classes/journal.class.php");

	class journal_extension {
		
		function handle_object($object_id) {
			$journal = new journal();
			return $journal->get_content($object_id);
		}
		
	}
?>