<?php
namespace Portfolio\Commands;
class Export extends \AbstractCommand implements \IFrameCommand{

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$user = \lms_steam::get_current_user();
		$username = $user->get_name();

		$allArtefacts = \Artefacts::getAllArtefacts();
		$zip = new \ZipArchive();
		$filename = PATH_TEMP . "DawinciPortfolio_" . $username . ".zip";
		$zip->open($filename, \ZIPARCHIVE::CREATE);
		$time = date(DATE_ATOM, time());
		$xmlOutput = <<<END
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
     xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
     xmlns:leap2="http://terms.leapspecs.org/"
     xmlns:categories="http://wiki.leapspecs.org/2A/categories/"
     xmlns:portfolio="http://www.example.ac.uk/portfolio_system/export_262144/">
<leap2:version>http://www.leapspecs.org/2010-07/2A/</leap2:version>
<id>http://www.dawinci-projekt.de/{$username}</id>
<title>Mein Portfolio</title>
<author> 
  <name>{$username}</name>
  <uri>http://www.dawinci-projekt.de/{$username}</uri>
</author>
<updated>{$time}</updated>

END;

		foreach ($allArtefacts as $artefact) {
			$uniqid = uniqid();
			$ctime = date(DATE_ATOM, $artefact->get_attribute("OBJ_CREATION_TIME"));
			$mtime = date(DATE_ATOM, $artefact->get_attribute("OBJ_MODIFICATION_TIME"));
			$artefactname = $uniqid . $artefact->getName();
			$content = $artefact->getDescription();
			$handle = fopen(PATH_TEMP . $uniqid . $artefact->getName(), 'w');
			fwrite($handle, $artefact->getContent());
			fclose($handle);
			$zipFileName = $artefact->getName();
			$zipFileName .= "." . getMimeTypeExtension($artefact->getMimeType());
			$zip->addFile(PATH_TEMP. $artefactname , "/files/" . $artefactname);
			$xmlOutput .= <<<END
    <entry>
        <title>{$artefact->getName()}</title>
        <id>{$artefact->getId()}</id>

        <updated>{$mtime}</updated>
        <published>{$ctime}</published>

        <summary>{$artefact->getDescription()}</summary>
        <content type="text">{$content}</content>
        <rdf:type rdf:resource="leap2:resource"/>
        <link rel="enclosure" type="{$artefact->getMimeType()}" href="files/{$artefactname}"/>

        <category term="Offline" scheme="categories:resource_type#" label="File"/>
END;
			$competences = $artefact->getCompetences();
			foreach ($competences as $competence) {
				$xmlOutput .= <<<END
        <competence>
			<index>{$competence->getName()}</index>
			<description>{$competence->getDescription()}</description>
			<rating>{$competence->getRating()}</rating>
		</competence>
END;
				;
			}
			$xmlOutput .= <<<END
    </entry>
END;

		}
		//		echo "numfiles: " . $zip->numFiles . "\n";
		//		echo "status:" . $zip->status . "\n";

		$xmlOutput .= "</feed>";
		//print "<pre>" . $xmlOutput . "</pre>";
		$zip->addFromString("leap2a.xml", $xmlOutput);
		$zip->close();
		return $frameResponseObject;
	}
}
?>