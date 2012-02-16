<?php

	$data['score'] = $solution['correction']['score'];
	$data['max'] = $content['answer_maxpoints'];

	if (isset($content['sample_used']) AND $content['sample_used'] == 1) {
		$data['score'] = $data['score']-$content['sample_points'];
	}


?>