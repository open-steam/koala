<?php
namespace Widgets;

class StarRating extends Widget {
	
	static $once = false;
	private $id;
	private $readonly = false;
	private $checked;
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function setReadonly($readonly) {
		$this->readonly = $readonly;
	}
	
	public function setChecked($checked) {
		$this->checked = $checked;
	}
	
	public function getHtml() {
		if (!self::$once) {
			\lms_portal::get_instance()->set_prototype_enabled(false);
			\lms_portal::get_instance()->add_javascript_src("jquery", PATH_URL . "widgets/js/jquery.js");
			\lms_portal::get_instance()->add_javascript_src("StarRating", PATH_URL . "widgets/js/StarRating.js");
			\lms_portal::get_instance()->add_css_style_link(PATH_URL . "widgets/css/StarRating.css");
			self::$once = true;
		}
		
		$js = <<<END
$(function(){ 
	$('.star' + $this->id).rating({
			alert("Hund"); 
		}
	});
});
END;

		$this->getContent()->setVariable("STAR_ID", $this->id);
		if ($this->readonly) {
			$this->getContent()->setVariable("DISABLED", "disabled=\"disabled\"");
		}
		if ($this->checked) {
			$this->getContent()->setVariable("CHECKED{$this->checked}", "checked=\"checked\"");
		}
		return "<script>" . $js . "</script>" . $this->getContent()->get();
	}
}
?>