<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de"><head><meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link href="../../../../css/static_pages.css" rel="stylesheet" type="text/css" />
<?php include ("../../../../../config/config.php"); ?>
<title>WebDav unter MacOS X 10.5</title>
</head>

<body>

<h1>WebDAV unter MacOS X 10.5
<a href="../../index.php"><img alt="zum Inhaltsverzeichnis" src="../../../../css/top.gif" /></a></h1>

<p><img src="Bilder/leopard_mit_server_verbinden_1.gif" alt="Findermenü Gehe zu" /></p>

<p>Im Finder können Sie im Menü »Gehe zu« den Punkt »Mit Server verbinden« auswählen.</p>

<p><img src="Bilder/leopard_mit_server_verbinden_2_webdav.gif" alt="Mit Server verbinden" /></p>

<p>Geben Sie in dem Dialog, der sich daraufhin öffnet, in das Feld Serveradresse »https://« ein (beachten Sie das »s«!) gefolgt vom Namen des Servers. Da der Server für den WebDAV-Zugang einen speziellen Port verwendet, müssen Sie hinter dem Servernamen einen Doppelpunkt und die Zahl 8443 angeben. Danach geben Sie den Namen des Ordners an, mit dem Sie sich verbinden wollen. Um unmittelbar auf Ihren persönlichen Arbeitsbereich zuzugreifen, geben Sie als Verzeichnis /home/<em>benutzername</em> an, wobei Sie Ihren Benutzernamen anstelle des Worts <em>benutzername</em> angeben. Die Adresse sollte also beispielsweise folgendermaßen aussehen: »https://<?php echo $config_server_ip ?>:8443/home/hase«. Klicken Sie auf »Verbinden«, so erscheint eventuell als nächstes eine Warnung, dass das Zertifikat nicht vertrauenswürdig sei. Überprüfen Sie, ob Sie wirklich den richtigen Server angegeben haben und bestätigen Sie anschließend, dass der Vorgang fortgesetzt werden soll. Anschließend können Sie sich am Server anmelden.</p>

<p><img src="Bilder/leopard_mit_server_verbinden_3_webdav.gif" alt="Identifizierung" /></p>

<p>Geben Sie nun Ihren Benutzernamen und Ihr Kennwort an. Sie können Ihr Kennwort im Schlüsselbund sichern lassen, damit Sie es zukünftig nicht erneut eingeben müssen. Wenn ein anderer Benutzer jedoch Zugang unter Ihrer Benutzerkennung zu Ihrem Computer hat, kann er auf diese Weise ebenfalls unter Ihrer Benutzerkennung auf den Server zugreifen.</p>

<p>Nach erfolgter Anmeldung sehen Sie ein Finder-Fenster, in das Sie Dokumente und Ordner von beliebigen Verzeichnissen auf Ihrem Computer durch Ziehen oder mit den Menübefehlen kopieren können. Sie können Dokumente und Ordner auch in umgekehrter Richtung vom Server auf Ihren Computer herunterladen.</p>

<p>Wenn Sie zu einem späteren Zeitpunkt erneut auf den Server per FTP zugreifen mächten, können Sie in dem Dialog »Mit Server verbinden« auf das Pluszeichen rechts neben der Serveradresse klicken, um den Server zu der Liste Ihrer bevorzugten Server hinzuzufügen.</p>

</body>
</html>