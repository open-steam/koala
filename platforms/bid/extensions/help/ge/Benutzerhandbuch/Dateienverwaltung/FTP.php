<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de"><head><meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link href="../../../../css/static_pages.css" rel="stylesheet" type="text/css" />
<?php include ("../../../../../config/config.php"); ?>
<title>FTP</title>
</head>

<body>

<h1>FTP
<a href="../../index.php"><img alt="zum Inhaltsverzeichnis" src="../../../../css/top.gif" /></a></h1>

<p>Sie können mit jedem FTP-Programm auf den Server zugreifen, indem Sie sich mit dem Server über die Adresse ftp://<?php echo $config_server_ip ?> verbinden. Um unmittelbar auf Ihren persönlichen Arbeitsbereich zuzugreifen, geben Sie als Verzeichnis /home/<em>benutzername</em> an, wobei Sie Ihren Benutzernamen anstelle des Worts <em>benutzername</em> angeben. Zur Anmeldung am Server verwenden Sie dann Ihr Kennwort.</p>

<p><img src="Bilder/ftp.gif" alt="FTP-Zugang" /></p>

<p>In manchen Betriebssystemen ist eine Unterstützung für FTP-Verbindungen bereits eingebaut. Welche Schritte zur Einrichtung des FTP-Zugangs erforderlich sind, können Sie hier nachlesen für <a href="FTP_WinXP.php">Windows XP</a>.</p>

<p>Um den FTP-Zugang nutzen zu können, muss der Standard-FTP-Port 21 in Ihrem lokalen Netzwerk freigeschaltet sein. Für Ihren eigenen Rechner können Sie dies in der Firewall einstellen, sofern Sie über Administrationsrechte verfügen. Ansonsten und wenn Sie in einem Netzwerk arbeiten, bitten Sie Ihren Administrator, diesen Port freizuschalten. Je nach Konfiguration des Netzwerks kann es erforderlich sein, dass Sie einen Proxy konfigurieren; schauen Sie in der Dokumentation des Betriebssystems Ihres Rechners nach, wie dies gemacht wird bzw. fragen Sie auch dazu Ihren Administrator.</p>

</body>
</html>