<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de"><head><meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link href="../../../../css/static_pages.css" rel="stylesheet" type="text/css" />
<?php include ("../../../../../config/config.php"); ?>
<title>FTP unter Windows XP</title>
</head>

<body>

<h1>FTP unter Windows XP
<a href="../../index.php"><img alt="zum Inhaltsverzeichnis" src="../../../../css/top.gif" /></a></h1>

<p>Im Windows Explorer können Sie zur Verbindung mit dem Server per FTP einfach die Netzwerkumgebung anwählen, die Sie in der Standardkonfiguration von Windows XP im Startmenü finden. Klicken Sie dort den Punkt »Netzwerkumgebung hinzufügen« an.</p>

<p><img src="Bilder/winxp_netzwerkumgebung_hinzufuegen_1.gif" alt="Netzwerkumgebung" /></p>

<p>Zunächst begrüßt Sie der »Assistent zum Hinzufügen von Netzwerkressourcen«. Klicken Sie auf die Schaltfläche »Weiter«, so gelangen Sie zu einem weiteren Dialog, in dem Sie den Punkt »Eine andere Netzwerkressource auswählen« anklicken sollten. Nachdem Sie erneut auf »Weiter« geklickt haben, erscheint ein Dialog, in dem Sie angeben müssen, welche Adresse diese Ressource verwendet.</p>

<p><img src="Bilder/winxp_netzwerkumgebung_hinzufuegen_2_ftp.gif" alt="Netzwerkressource angeben" /></p>

<p>Als Netzwerkadresse geben Sie dort »ftp://« an gefolgt vom Namen des Servers sowie dem Namen des Ordners, mit dem Sie sich verbinden wollen. Um unmittelbar auf Ihren persönlichen Arbeitsbereich zuzugreifen, geben Sie als Verzeichnis /home/<em>benutzername</em> an, wobei Sie Ihren Benutzernamen anstelle des Worts <em>benutzername</em> angeben. Die Adresse sollte also beispielsweise folgendermaßen aussehen: »ftp://<?php echo $config_server_ip ?>/home/hase«. Klicken Sie erneut auf »Weiter« und Sie erhalten die Gelegenheit zur Eingabe Ihres Benutzernamens.</p>

<p><img src="Bilder/winxp_netzwerkumgebung_hinzufuegen_3_ftp.gif" alt="Benutzernamen angeben" /></p>

<p>Geben Sie hier Ihren Benutzernamen an. Ansonsten werden Sie als Gastbenutzer auf dem Server angemeldet und besitzen dann keine Schreibrechte. Entfernen Sie daher den Haken vor »Anonym anmelden« und tragen Sie in das dann aktivierte Feld Ihren Benutzernamen ein, also beispielsweise »hase«. Ein Kennwort müssen Sie an dieser Stelle noch nicht angeben. Klicken Sie wieder auf »Weiter«, um im nächsten Schritt die Netzwerkressource zu benennen.</p>

<p><img src="Bilder/winxp_netzwerkumgebung_hinzufuegen_4_ftp.gif" alt="Netzwerkressource benennen" /></p>

<p>Benennen Sie nun die Netzwerkressource mit einem beliebigen Namen. Unter diesem Namen wird Ihnen der Server zukünftig in Ihrer Netzwerkumgebung angezeigt. Zum Abschluss sollte Ihnen der Assistent nun mitteilen, dass die Netzwerkumgebung erfolgreich erstellt wurde. Der von Ihnen gewählte Name wird Ihnen ebenfalls angezeigt. Sie können nun auf »Fertig stellen« klicken; wenn Sie zuvor das Häkchen in dem Dialog nicht entfernt haben, wird nun unmittelbar eine Verbindung zum Server aufgebaut. Da Sie noch kein Kennwort angegeben haben, wird ein weiterer Dialog geöffnet.</p>

<p><img src="Bilder/winxp_netzwerkumgebung_hinzufuegen_5_ftp.gif" alt="Anmelden" /></p>

<p>Lassen Sie sich von der Mitteilung, dass die Anmeldung nicht durchgeführt werden konnte, nicht irritieren; dies ist keine Fehlermeldung. Als FTP-Server wird Ihnen in dem Dialog der Name des Servers <?php echo $config_server_ip ?> angezeigt. Darunter finden Sie zwei Eingabefelder für Ihren Benutzernamen, der dort schon eingetragen sein sollte, sowie Ihr Kennwort, das Sie nun eingeben müssen. Beachten Sie, dass das Kennwort nicht verschlüsselt übertragen wird; für eine verschlüsselte Kommunikation können Sie den <a href="WebDAV.php">WebDAV-Zugang</a> verwenden. Sie können Ihr Kennwort vom Betriebssystem speichern lassen, damit Sie es zukünftig nicht erneut eingeben müssen. Wenn ein anderer Benutzer jedoch Zugang zu Ihrem Computer hat, kann er auf diese Weise ebenfalls unter Ihrer Benutzerkennung auf den Server zugreifen.</p>

<p>Nach erfolgter Anmeldung sehen Sie ein Explorer-Fenster, in das Sie Dokumente und Ordner von beliebigen Verzeichnissen auf Ihrem Computer durch Ziehen oder mit den Menübefehlen kopieren können. Sie können Dokumente und Ordner auch in umgekehrter Richtung vom Server auf Ihren Computer herunterladen.</p>

<p>Wenn Sie zu einem späteren Zeitpunkt erneut auf den Server per FTP zugreifen mächten, brauchen Sie nicht erneut alle diese Schritte auszuführen. Nach Öffnen der Netzwerkumgebung sehen Sie dort einen neuen Eintrag unter dem von Ihnen gewählten Namen. Durch einen Doppelklick auf das entsprechende Symbol verbinden Sie sich erneut mit dem Server und werden – falls Sie sich in dieser Windows-Sitzung noch nicht per FTP mit dem Server verbunden haben – nach Ihrem Kennwort gefragt. Wenn Sie Ihr Kennwort vom Betriebssystem haben speichern lassen, werden Sie ohne weitere Nachfrage gleich angemeldet.</p>

<p><img src="Bilder/winxp_netzwerkumgebung_hinzufuegen_6_ftp.gif" alt="Netzwerkumgebung mit neuer Verbindung" /></p>

</body>
</html>