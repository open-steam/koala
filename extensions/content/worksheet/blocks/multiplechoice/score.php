<?php

	
	$data['score'] = $content['answers_points_start'];
	$data['max'] = $content['answers_points_start'];

	foreach ($content['answers'] as $k=>$answer) {
		
		//calculate score
		if (($answer['correct'] AND $solution['solution']['answers'][$k]) OR (!$answer['correct'] AND !$solution['solution']['answers'][$k])) {
			//correct
			$data['score'] += $content['answers_points_right'];
		} else {
			//wrong
			$data['score'] -= $content['answers_points_wrong'];
		}
		
		//calculate max score
		$data['max'] += $content['answers_points_right'];
		
	}
	
	if (isset($solution['correction']['extra'])) {
		//add extra points
		$data['score'] += $solution['correction']['extra'];
	}



?>