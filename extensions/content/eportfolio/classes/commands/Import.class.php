<?php
namespace Portfolio\Commands;
class Import extends \AbstractCommand implements \IFrameCommand{

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$user = \lms_steam::get_current_user();
		$username = $user->get_name();

		$zip->open($filename, \ZIPARCHIVE::CREATE);
		$time = date(DATE_ATOM, time());

		foreach ($allArtefacts as $artefact) {
			$uniqid = uniqid();
			$ctime = date(DATE_ATOM, $artefact->get_attribute("OBJ_CREATION_TIME"));
			$mtime = date(DATE_ATOM, $artefact->get_attribute("OBJ_MODIFICATION_TIME"));
			$artefactname = $uniqid . $artefact->getName();
			$content = "";
			$handle = fopen(PATH_TEMP . $uniqid . $artefact->getName(), 'w');
			fwrite($handle, $artefact->getContent());
			fclose($handle);
			$zipFileName = $artefact->getName();
			$zipFileName .= "." . getMimeTypeExtension($artefact->getMimeType());
			$zip->addFile(PATH_TEMP. $artefactname , "/files/" . $artefactname);
		}
//		echo "numfiles: " . $zip->numFiles . "\n";
//		echo "status:" . $zip->status . "\n";

		//print "<pre>" . $xmlOutput . "</pre>";
		return $frameResponseObject;
	}
}
?>