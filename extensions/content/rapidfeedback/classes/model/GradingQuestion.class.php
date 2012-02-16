<?php
namespace Rapidfeedback\Model;
class GradingQuestion extends MatrixQuestion {
	protected $columns = array("sehr gut", "gut", "befriedigend", "ausreichend", "mangelhaft", "ungenügend");
	protected $type = 5;
}
?>