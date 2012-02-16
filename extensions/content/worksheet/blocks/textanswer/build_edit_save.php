<?php
// CHANGE VALUES OF ARRAY $content


	$content['text'] = $_POST['text'];
	$content['answer_maxpoints'] = $_POST['answer_maxpoints'];
	
	$content['sample'] = (isset($_POST['sample']) AND $_POST['sample'] == "1");

	$content['sample_text'] = $_POST['sample_text'];

	$content['sample_chars_number'] = $_POST['sample_chars_number'];
	$content['sample_minutes_number'] = $_POST['sample_minutes_number'];

	$content['sample_chars'] = (isset($_POST['sample_chars']) AND $_POST['sample_chars'] == "1");
	$content['sample_minutes'] = (isset($_POST['sample_minutes']) AND $_POST['sample_minutes'] == "1");
	
	$content['sample_points'] = (int) $_POST['sample_points'];


?>