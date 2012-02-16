<?php 
class ChangeLogHome extends AbstractExtension implements IHomeExtension {
	
	public function getName() {
		return "ChangeLogHome";
	}
	
	public function getDesciption() {
		return "Home extension for changelog.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function getWidget() {
		$box = new \Widgets\Box();
		$box->setId(\BookmarksHome::getInstance()->getId());
		$box->setTitle("Letzte Änderungen");
		$box->setTitleLink(PATH_URL . "bookmarks/");
		$box->setContent(<<<END
<b>Version: v3.1-BETA-4 - 24.03.2011</b>
<ul>
	<li>Gallery: Bilder-Upload möglich</li>
	<li>Stabilität und Performance</li>
</ul>
<b>nächste Schritte:</b>
<ul>
	<li>Explorer: anlegen von Objekten</li>
	<li>Portal: weitere Bearbeitungsfunktionen</li>
	<li>Profil: Bid-Felder anzeigen</li>
</ul>
<br>
<b>Version: v3.1-BETA-3 - 14.02.2011</b>
<ul>
	<li>Explorer: Eigenschaftdialog</li>
	<li>Explorer: umbenennen von Objekten</li>
	<li>Portal: erste Bearbeitungsfunktionen</li>
</ul>
<br>
<b>Version: v3.1-BETA-2 - 31.01.2011</b>
<ul>
	<li>Benutzerprofile</li>
	<li>Dokument Download im Explorer</li>
	<li>intern: Performenz-Steigerung</li>
	<li>intern: Command-Strukur neu implementiert</li>
	<li>intern: Grundladen für Ajax Unterstützung geschaffen</li>
</ul>
<b>nächste Schritte:</b>
<ul>
	<li>Bearbeitungsmodus im Portal</li>
	<li>Bearbeitungsmodus im Explorer</li>
</ul>
<br>
<b>Version: v3-BETA-1 - 14.01.2011</b>
<ul>
	<li>Portal Erweiterung im Lesemodus</li>
	<li>Forum Erweiterung im Lesemodus</li>
	<li>Gallerie Erweiterung im Lesemodus</li>
	<li>Explorer Erweiterung im Lesemodus</li>
</ul>
<b>nächste Schritte:</b>
<ul>
	<li>Explorer: Datei Up- und Download</li>
	<li>Benutzerprofile</li> 
</ul>
END
);
		$box->setContentMoreLink(PATH_URL . "bookmarks/");
		return $box;
	}
}
?>