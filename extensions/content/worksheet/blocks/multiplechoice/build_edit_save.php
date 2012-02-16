<?php
// CHANGE VALUES OF ARRAY $content



	$content['text'] = $_POST['text'];
	$content['answers_points_start'] = $_POST['answers_points_start'];
	$content['answers_points_right'] = $_POST['answers_points_right'];
	$content['answers_points_wrong'] = $_POST['answers_points_wrong'];
	

	$content['answers'] = Array();
	
	for ($i=0; $i <= $_POST['answers_max_id']; $i++) { 
		
		if (isset($_POST['answers_'.$i.'_answer'])) {
			
			$correct = (isset($_POST['answers_'.$i.'_correct']) AND $_POST['answers_'.$i.'_correct'] == 1);
			
			$content['answers'][] = Array(
				"text" => $_POST['answers_'.$i.'_answer'],
				"correct" => $correct
			);
			
		}
		
	}
	

?>