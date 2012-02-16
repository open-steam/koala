<?php
include_once( "../etc/koala.conf.php" );


$portal = lms_portal::get_instance();
$portal->initialize( GUEST_ALLOWED );
$portal->set_page_title( "Hinweise zur Anmeldung von Kursen in koaLA" );
$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "requestcourses.template.html" );
//$content->setVariable( "DATE", "18.02.2010" );

$requestcourses = 
"<p>Zum Einrichten ihrer Lehrveranstaltung im koaLA benötigen wir folgende Informationen:</p>
<ul>
<li>Titel der Lehrveranstaltung</li>
<li>Name des Dozenten und Uni-Account (IMT-Benutzername)</li>
<li>Namen der beteiligten Mitarbeiter und Uni-Accounts (IMT-Benutzernamen)</li>
<li>wird der Kurs von PAUL verwaltet oder handelt es sich um eine externe Veranstaltung</li>
</ul>
Für Paul-Kurse sollte zusätzlich angegeben werden:
<ul>
<li>PAUL-Kursnummer</li>
<li>der in PAUL eingetragene Dozent und Uni-Account (IMT-Benutzername)</li>
</ul>
Sende sie eine Email mit diesen Angaben an <a href=\"mailto:elearning@upb.de?subject=[Anmeldung%20koaLA%20Kurs]&body=(Ergänzen%20Sie%20bitte%20folgende%20Punkte:)%0A%0ATitel%20der%20Lehrveranstaltung:%0A%0AName%20des%20Dozenten%20und%20Uni-Account%20(IMT-Benutzername):%0A%0ANamen%20der%20beteiligten%20Mitarbeiter%20und%20Uni-Accounts%20(IMT-Benutzernamen):%0A%0AKurs%20ist%20in%20PAUL%20(ja/nein):%0A%0APAUL-Kursnummer:\">elearning@upb.de</a>.";

$content->setVariable( "REQUEST_COURSES_TEXT", $requestcourses );

$portal->set_page_main(
	array(
		array( "link" => "",
			"name" => "Hinweise zur Anmeldung von Kursen in koaLA"
		)
	),
	$content->get(),
	""
);

$portal->show_html();
?>