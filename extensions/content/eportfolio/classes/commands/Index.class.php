<?php
namespace Portfolio\Commands;
class Index extends \AbstractCommand implements \IFrameCommand {
	
	private $user;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$params = $requestObject->getParams();
		if (isset($params) && isset($params[0])) {
			$this->user = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $params[0]);
		}
		if (!isset($this->user) || !($this->user instanceof \steam_user)) {
			$this->user = \lms_steam::get_current_user();
		}
	}
	
	private function getKompetenzHtml($no, $text, $isAdd = false, $anno = "") {
		$html = <<<END
			<div style="clear:both">
				<div style="float:left; width:20px">$no</div>
				<div style="float:left; width:450px; margin-left:23px">$text</div>
				<!--<div style="float:left;"><select><option></option><option>Weiterbildungsbedarf</option></select></div>-->
				<div style="float:right;"><input style="width:210px" placeholder="Bemerkung"><input type="checkbox" title="Weiterbildung notwendig"><img src="http://dawinci-v3.dev:8888/portfolio/asset/images/learn16.jpg"></div>
			</div>
END
;
		return $html;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$portfolioExtension = \Portfolio::getInstance();
		$content = $portfolioExtension->loadTemplate("portfolio.template.html");
		
		$portfolio = \Portfolio\Model\Portfolio::getInstanceForUser($this->user);
				
		$rawHtml = new \Widgets\RawHtml();
		
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name"=>"Kompetenzportfolio")));
		
		$infobar = new \Widgets\InfoBar();
		$infobar->setHeadline("");
		$infobar->addParagraph(<<<END
Mithilfe dieses Kompetenzportfolio-Systems können  zentrale chemieberufliche
Kompetenzen zur Bilanzierung gesichtet, bestimmt, geordnet und dokumentiert werden.<br><br>
Das Kompetenzportfolio ist durch seine Bilanzierungs- und Dokumentationsfunktionen 
dafür geeignet, Ausbilder, Dozenten, Auszubildende, Schüler, Personalreferenten oder 
Angestellte von Berufen der chemischen Industrie bei Fragestellungen der Aus- und 
Weiterbildungseignung/-vorbereitung , der Anrechnung von Aus- und Weiterbildungszielen,
der Personalauswahl, der Personalentwicklung, der Berufswahl sowie bei der Bewerbung zu unterstützen.				
END
				);
		$content->setVariable("INFOBAR", $infobar->getHtml());
		
		// Profilbild
		$captionImage = new \Widgets\CaptionImage();
		$captionImage->setLink(PATH_URL . "user/index/" . $this->user->get_name() . "/");
		$captionImage->setLinkText($this->user->get_attribute("USER_FIRSTNAME") . " " . $this->user->get_attribute("USER_FULLNAME"));
		$captionImage->setImageSrc(\lms_user::get_user_image_url(140,185, $this->user->get_attribute("OBJ_ICON")));
		$captionImage->setImageAlt(gettext("Profile Image"));
		$captionImage->setImageTitle(gettext("Complete your Profile"));
		$content->setVariable("PROFILEIMAGE", $captionImage->getHtml());
		$rawHtml->addWidget($captionImage);
		
		// schulische Abschlüsse
		$schoolBox = \Portfolio\Model\EntrySchool::getViewWidget($portfolio);
		$content->setCurrentBlock("PORTFOLIO_BOX");
		$content->setVariable("PORTFOLIO_CONTENT", $schoolBox->getHtml());
		$content->parse("PORTFOLIO_BOX");
		$rawHtml->addWidget($schoolBox);

		// berufliche Abschlüsse
		$jobBox = \Portfolio\Model\EntryEducation::getViewWidget($portfolio);
		$content->setCurrentBlock("PORTFOLIO_BOX");
		$content->setVariable("PORTFOLIO_CONTENT", $jobBox->getHtml());
		$content->parse("PORTFOLIO_BOX");
		$rawHtml->addWidget($jobBox);
/*		$jobBox = new \Widgets\Box();
		$jobBox->setId(\PortfolioHome::getInstance()->getId());
		$jobBox->setTitle(\Portfolio::getInstance()->getText("Berufliche Aus- und Weiterbildungsgänge"));
		$jobBox->setTitleLink(PATH_URL . "portfolio/");
		$html = <<<END
		<div style="text-align: center; color: gray; font-size: 80%">
			Hier können relevante Informationen zu den beruflichen Aus- und Weiterbildungsgängen (wie die Art des Ausbildungsberufes sowie ggf. des Weiterbildungsberufes) hinterlegt, erläutert und belegt werden.
		</div>
		<br>
		<div style="border: 3px dotted lightblue; padding: 5px; background-color: #ffe">
			Ausbildungsberuf: <em>Chemikant</em> <br>
			durchschnittliche Abschlussnote: <em>Gut (2)</em> <br>
			Jahrgang: <em>2005</em> <br>
			Ausbildungsbetrieb: <em>Bayer</em> <br>
			Ausbildungsstätte: <em>Infracor</em> <br>
			Bemerkung: <em>Schwerpunkt Anlagen</em>
			<br clear=all>
			<div style="float: right; display: inline">
				<a href="">Eintrag bearbeiten</a> |
				<a href="">Beleg anfügen</a> 
			</div>
			<br clear=all>
			Kompetenzen:<br><br>
			<em>Tätigkeitsfeld II (Chemische/biologische Produktionsverfahren vorbereiten/planen, durchführen und optimieren)</em><br>
			<div style="font-size:80%">Das Tätigkeitsfeld beinhaltet Kompetenzen, die für die erfolgreiche Planung, Durchführung, Optimierung und Dokumentation chemischer/biologischer Produktionsverfahren vorhanden sein sollten.</div>
			<div style="font-size:80%; text-align:right">Kompetenzniveau (gemäß DQR): 4<br>
			Er/Sie ist in der Lage  kompetenzbezogene Aufgabenstellungen im Rahmen der Vorbereitung/Planung, Durchführung und Optimierung von chemischer/biologischer Produktionsverfahren (bspw. Feststoffe zu zerkleinern und klassieren)  auch unter sich verändernden und nicht eindeutigen Rahmenbedingungen selbständig zu planen, zu bearbeiten, ggf. hierbei auftretende Probleme zu lösen sowie Arbeitsergebnisse unter Beachtung von Wechselwirkungen zu beurteilen.     
			</div>
			<hr>
END;
		$html .= $this->getKompetenzHtml("II.1", "Kann betriebsübliche verfahrenstechnische mechanische Grundoperationen durchführen");
		$html .= $this->getKompetenzHtml("II.2", "Kann betriebsübliche verfahrenstechnische thermische Grundoperationen durchführen");
		$html .= "<br clear=all><br><em>Tätigkeitsfeld III</em><hr>";
		$html .= $this->getKompetenzHtml("III.1", "Kann betriebsübliche Arbeitsmitteln (z.B. Fördersysteme, Werkstoffe, Anlagenteile und Geräte usw.) handhaben, pflegen, instandhalten sowie  funktionell relevante Faktoren (z.B. Korrosion, Verschleiß usw.) beurteilen");
		$html .= $this->getKompetenzHtml("III.2", "Kann Installationstechnische Arbeiten (z.B. Rohre und Rohrleitungsteile verbinden und abdichten) planen und durchführen");
		$html .="<br clear=all></div>";
		$jobBox->setContent($html);
		$jobBox->setContentMoreLink(PATH_URL . "portfolio/");
		$content->setCurrentBlock("PORTFOLIO_BOX");
		$content->setVariable("PORTFOLIO_CONTENT", $jobBox->getHtml());
		$content->parse("PORTFOLIO_BOX");
		$rawHtml->addWidget($jobBox);*/
		
		// Studium
		$studyBox = \Portfolio\Model\EntryAcademic::getViewWidget($portfolio);
		$content->setCurrentBlock("PORTFOLIO_BOX");
		$content->setVariable("PORTFOLIO_CONTENT", $studyBox->getHtml());
		$content->parse("PORTFOLIO_BOX");
		$rawHtml->addWidget($studyBox);
		
		// Zertifikate
		$certBox = \Portfolio\Model\EntryCertificate::getViewWidget($portfolio);
		$content->setCurrentBlock("PORTFOLIO_BOX");
		$content->setVariable("PORTFOLIO_CONTENT", $certBox->getHtml());
		$content->parse("PORTFOLIO_BOX");
		$rawHtml->addWidget($certBox);
		
		// Berufserfahrung
		$practiceBox = \Portfolio\Model\EntryEmployment::getViewWidget($portfolio);
		$content->setCurrentBlock("PORTFOLIO_BOX");
		$content->setVariable("PORTFOLIO_CONTENT", $practiceBox->getHtml());
		$content->parse("PORTFOLIO_BOX");
		$rawHtml->addWidget($practiceBox);
		
/*		$practiceBox = new \Widgets\Box();
		$practiceBox->setId(\PortfolioHome::getInstance()->getId());
		$practiceBox->setTitle(\Portfolio::getInstance()->getText("Berufliche Erfahrungen"));
		$practiceBox->setTitleLink(PATH_URL . "portfolio/");
		$html = <<<END
		<div style="text-align: center; color: gray; font-size: 80%">
			Hier können relevante Informationen zu den beruflichen Erfahrungen (Art der Erfahrung) hinterlegt, erläutert und belegt werden.
		</div>
		<br>
		<div style="border: 3px dotted lightblue; padding: 5px; background-color: #ffe">
			Art: <em>Praktikum oder Beschäftigung</em> <br>
			Betrieb: <em>Bayer</em> <br>
			Dauer: <em>2 Jahr</em> <br>
			Position: <em>Chemikant</em><br>
			Bemerkung: <em>Schwerpunkt Anlagen</em>
			<br clear=all>
			<div style="float: right; display: inline">
				<a href="">Eintrag bearbeiten</a> |
				<a href="">Beleg anfügen</a> 
			</div>
			<br clear=all>
			Kompetenzen:<br><br>
			<em>Tätigkeitsfeld II (Chemische/biologische Produktionsverfahren vorbereiten/planen, durchführen und optimieren)</em><br>
			<div style="font-size:80%">Das Tätigkeitsfeld beinhaltet Kompetenzen, die für die erfolgreiche Planung, Durchführung, Optimierung und Dokumentation chemischer/biologischer Produktionsverfahren vorhanden sein sollten.</div>
			<div style="font-size:80%; text-align:right">Kompetenzniveau (gemäß DQR): 4<br>
			Er/Sie ist in der Lage  kompetenzbezogene Aufgabenstellungen im Rahmen der Vorbereitung/Planung, Durchführung und Optimierung von chemischer/biologischer Produktionsverfahren (bspw. Feststoffe zu zerkleinern und klassieren)  auch unter sich verändernden und nicht eindeutigen Rahmenbedingungen selbständig zu planen, zu bearbeiten, ggf. hierbei auftretende Probleme zu lösen sowie Arbeitsergebnisse unter Beachtung von Wechselwirkungen zu beurteilen.     
			</div>
			<hr>
END;
		$html .= $this->getKompetenzHtml("II.1<br><div style=\"font-size:60%\">Chemikant</div>", "Kann betriebsübliche verfahrenstechnische mechanische Grundoperationen durchführen");
		$html .="<br clear=all>
		<div style=\"float: right; display: inline\">
				<a href=\"\">Kompetenzen verwalten</a> 
		</div>
		<br clear=all>
		</div>";
		$practiceBox->setContent($html);
		$practiceBox->setContentMoreLink(PATH_URL . "portfolio/");
		$content->setCurrentBlock("PORTFOLIO_BOX");
		$content->setVariable("PORTFOLIO_CONTENT", $practiceBox->getHtml());
		$content->parse("PORTFOLIO_BOX");
		$rawHtml->addWidget($practiceBox);*/
		
		// Sonstige
		$otherBox = \Portfolio\Model\EntryOther::getViewWidget($portfolio);
		$content->setCurrentBlock("PORTFOLIO_BOX");
		$content->setVariable("PORTFOLIO_CONTENT", $otherBox->getHtml());
		$content->parse("PORTFOLIO_BOX");
		$rawHtml->addWidget($otherBox);
		
		$frameResponseObject->setTitle("Kompetenzportfolio");
		$frameResponseObject->addWidget($breadcrumb);
		$actionBar = new \Widgets\ActionBar();
		$actionBar->setActions(array(
									array("name"=>"Bildungsbiographie", "ajax"=>array("onclick"=>array("command"=>"properties", "params"=>array("id"=>"1"), "requestType"=>"popup"))),
									array("name"=>"Kompetenzübersicht", "ajax"=>array("onclick"=>array("command"=>"newElement", "params"=>array("id"=>"1"), "requestType"=>"popup"))), 							
									array("name"=>"Diskussionen", "ajax"=>array("onclick"=>array("command"=>"newElement", "params"=>array("id"=>"1"), "requestType"=>"popup"))),
									array("name"=>"Kompetenzmodell", "ajax"=>array("onclick"=>array("command"=>"newElement", "params"=>array("id"=>"1"), "requestType"=>"popup"))),
									array("name"=>"Import der Belege", "ajax"=>array("onclick"=>array("command"=>"Sanctions", "params"=>array("id"=>"1"), "requestType"=>"popup"))),
									array("name"=>"Export der Belege", "ajax"=>array("onclick"=>array("command"=>"Sanctions", "params"=>array("id"=>"1"), "requestType"=>"popup"))),
									array("name"=>"Drucken", "ajax"=>array("onclick"=>array("command"=>"Sanctions", "params"=>array("id"=>"1"), "requestType"=>"popup")))
		));
		$frameResponseObject->addWidget($actionBar);
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>