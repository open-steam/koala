<?php
// CHANGE VALUES OF ARRAY $content

$score = $content['answers_points_start'];

foreach ($content['answers'] as $key=>$answer) {

	//{if ($solution.data.answers[$key] AND $answer.correct) OR (!$solution.data.answers[$key] AND !$answer.correct)}
	
	if (($solution['answers'][$key] AND $answer['correct']) OR (!$solution['answers'][$key] AND !$answer['correct'])) {
		$content['answers'][$key]['result'] = true;
		$score += $content['answers_points_right'];
	} else {
		$content['answers'][$key]['result'] = false;
		$score -= $content['answers_points_wrong'];
	}
	
}

$tpl->assign("score", $score);


?>