<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de"><head><meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link href="../../../../css/static_pages.css" rel="stylesheet" type="text/css" />
<?php include ("../../../../../config/config.php"); ?>
<title>WebDav unter Windows XP</title>
</head>

<body>

<h1>WebDAV unter Windows XP
<a href="../../index.php"><img alt="zum Inhaltsverzeichnis" src="../../../../css/top.gif" /></a></h1>

<p>Im Windows Explorer können Sie zur Verbindung mit dem Server per WebDAV einfach die Netzwerkumgebung anwählen, die Sie in der Standardkonfiguration von Windows XP im Startmenü finden. Klicken Sie dort den Punkt »Netzwerkumgebung hinzufügen« an.</p>

<p><img src="Bilder/winxp_netzwerkumgebung_hinzufuegen_1.gif" alt="Netzwerkumgebung" /></p>

<p>Zunächst begrüßt Sie der »Assistent zum Hinzufügen von Netzwerkressourcen«. Klicken Sie auf die Schaltfläche »Weiter«, so gelangen Sie zu einem weiteren Dialog, in dem Sie den Punkt »Eine andere Netzwerkressource auswählen« anklicken sollten. Nachdem Sie erneut auf »Weiter« geklickt haben, erscheint ein Dialog, in dem Sie angeben müssen, welche Adresse diese Ressource verwendet.</p>

<p><img src="Bilder/winxp_netzwerkumgebung_hinzufuegen_2_webdav.gif" alt="Netzwerkressource angeben" /></p>

<p>Als Netzwerkadresse geben Sie dort »https://« an (beachten Sie das »s«!) gefolgt vom Namen des Servers. Da der Server für den WebDAV-Zugang einen speziellen Port verwendet, müssen Sie hinter dem Servernamen einen Doppelpunkt und die Zahl 8443 angeben. Danach geben Sie den Namen des Ordners an, mit dem Sie sich verbinden wollen. Um unmittelbar auf Ihren persönlichen Arbeitsbereich zuzugreifen, geben Sie als Verzeichnis /home/<em>benutzername</em> an, wobei Sie Ihren Benutzernamen anstelle des Worts <em>benutzername</em> angeben. Die Adresse sollte also beispielsweise folgendermaßen aussehen: »https://<?php echo $config_server_ip ?>:8443/home/hase«. Klicken Sie erneut auf »Weiter«, so erscheint eventuell als nächstes eine Warnung, dass das Zertifikat nicht vertrauenswürdig sei. Überprüfen Sie, ob Sie wirklich den richtigen Server angegeben haben und bestätigen Sie anschließend, dass der Vorgang fortgesetzt werden soll. Anschließend erhalten Sie die Gelegenheit zur Eingabe Ihres Benutzernamens.</p>

<p><img src="Bilder/winxp_netzwerkumgebung_hinzufuegen_3_webdav.gif" alt="Benutzernamen angeben" /></p>

<p>Geben Sie hier Ihren Benutzernamen und Ihr Kennwort an. Ansonsten werden Sie als Gastbenutzer auf dem Server angemeldet und besitzen dann keine Schreibrechte. Klicken Sie wieder auf »OK«, um im nächsten Schritt die Netzwerkressource zu benennen.</p>

<p><img src="Bilder/winxp_netzwerkumgebung_hinzufuegen_4_webdav.gif" alt="Netzwerkressource benennen" /></p>

<p>Benennen Sie nun die Netzwerkressource mit einem beliebigen Namen. Unter diesem Namen wird Ihnen der Server zukünftig in Ihrer Netzwerkumgebung angezeigt. Zum Abschluss sollte Ihnen der Assistent nun mitteilen, dass die Netzwerkumgebung erfolgreich erstellt wurde. Der von Ihnen gewählte Name wird Ihnen ebenfalls angezeigt. Sie können nun auf »Fertig stellen« klicken; wenn Sie zuvor das Häkchen in dem Dialog nicht entfernt haben, wird nun unmittelbar eine Verbindung zum Server aufgebaut.</p>

<p>Im Regelfall öffnet sich nun ein Explorerfenster, ohne dass etwas weiteres zu passieren scheint. Tatsächlich befindet sich <em>hinter</em> dem Explorerfenster erneut der bereits zuvor angezeigte Sicherheitshinweis. Sie können sich diesen Hinweise durch einen Klick auf den entsprechenden Eintrag in der Taskleiste von Windows XP oder durch Minimieren des Explorerfensters anzeigen lassen und akzeptieren. Anschließend werden Sie erneut zur Eingabe Ihres Benutzernamens und Ihres Kennworts augefordert, wenn Sie das Kennwort nicht zuvor haben speichern lassen. Sie können Ihr Kennwort vom Betriebssystem speichern lassen, damit Sie es zukünftig nicht erneut eingeben müssen. Wenn ein anderer Benutzer jedoch Zugang zu Ihrem Computer hat, kann er auf diese Weise ebenfalls unter Ihrer Benutzerkennung auf den Server zugreifen.</p>

<p>Nach erfolgter Anmeldung sehen Sie ein Explorer-Fenster, in das Sie Dokumente und Ordner von beliebigen Verzeichnissen auf Ihrem Computer durch Ziehen oder mit den Menübefehlen kopieren können. Sie können Dokumente und Ordner auch in umgekehrter Richtung vom Server auf Ihren Computer herunterladen.</p>

<p>Wenn Sie zu einem späteren Zeitpunkt erneut auf den Server per WebDAV zugreifen mächten, brauchen Sie nicht erneut alle diese Schritte auszuführen. Nach Öffnen der Netzwerkumgebung sehen Sie dort einen neuen Eintrag unter dem von Ihnen gewählten Namen. Durch einen Doppelklick auf das entsprechende Symbol verbinden Sie sich erneut mit dem Server und werden – falls Sie sich in dieser Windows-Sitzung noch nicht per WebDAV mit dem Server verbunden haben – nach Ihrem Kennwort gefragt. Wenn Sie Ihr Kennwort vom Betriebssystem haben speichern lassen, werden Sie ohne weitere Nachfrage gleich angemeldet.</p>

<p><img src="Bilder/winxp_netzwerkumgebung_hinzufuegen_6_webdav.gif" alt="Netzwerkumgebung mit neuer Verbindung" /></p>

</body>
</html>