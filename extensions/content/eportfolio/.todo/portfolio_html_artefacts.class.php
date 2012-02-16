<?php
class portfolio_html_artefacts extends portfolio_html {



	public function __construct($path = "") {
		parent::__construct(PORTFOLIO_PATH_TEMPLATES . "index.template.html", $path, "artefacts");
	}

	public function set_content(){
		//			ini_set("post_max_size", "50M");
		//			ini_set("upload_max_filesize", "50M");
		//			ini_set("memory_limit", "50M" );
		$user = lms_steam::get_current_user();

		if ($this->path == "upload/") {
			/*
			 * Artefacts Upload Content
			 */
			if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" ){
				$values = isset( $_POST[ "values" ] ) ? $_POST[ "values" ] : array();
				$problems = "";
				$hints    = "";
				if ( empty( $_FILES ) || (!empty( $_FILES["material"]["error"] ) && $_FILES["material"]["error"] > 0 ) ) {
					if ( !empty($_FILES) && empty( $_FILES["material"]["name"] ) ) {
						$problems = gettext( "No file chosen." ) . " ";
					} else {
						$problems = gettext( "Could not upload document." ) . " ";
					}
					$_SESSION[ "confirmation" ] = str_replace("%DOCUMENT",h($filename),$problems);
				}
				if ( empty( $problems ) ){
					$content = file_get_contents( $_FILES["material"]["tmp_name"] );
					$filename = str_replace( array( "\\", "'" ), array( "", "" ), $_FILES[ "material" ][ "name" ]  );
					$artefacts_container = $user->get_workroom()->get_object_by_name("portfolio")->get_object_by_name("artefacts");
					$new_container = steam_factory::create_container(
						$GLOBALS[ "STEAM" ]->get_id(),
						$filename,
						$artefacts_container
					);
					$new_material = steam_factory::create_document(
						$GLOBALS[ "STEAM" ]->get_id(),
						$filename,
						$content,
						$_FILES[ "material" ][ "type" ],
						FALSE
					);
					print $values[ "dsc" ]."hhhh";
					$new_material->set_attribute("DESCRIPTION", $values[ "dsc" ] );
					$new_container->set_attribute("DESCRIPTION", $values[ "dsc" ] );
					$new_material->move( $new_container );
					$_SESSION[ "confirmation" ] = str_replace("%DOCUMENT",h($filename),gettext( "'%DOCUMENT' has been uploaded."));
//					header( "Location: " . PATH_SERVER . "/portfolio/artefacts/" );
					exit;
				}
			}	else {
				/*
				 * Artefacts Upload Form
				 */
				$template = new HTML_TEMPLATE_IT();
				$template->loadTemplateFile(PORTFOLIO_PATH_TEMPLATES . "artefacts_upload.template.html");
				$template->setVariable( "LABEL_UPLOAD", gettext( "Upload" ) );
				$template->setVariable( "LABEL_FILE", gettext( "Local file" ) );
				$template->setVariable( "LABEL_DSC", gettext( "Description" ) );
				$template->setVariable( "FORM_ACTION", PATH_SERVER . "/portfolio/artefacts/upload/" );
				$this->template->setVariable("HTML_CODE_LEFT", $template->get());
			}
		} else {
			/*
			 * Artefacts List
			 */
			$template = new HTML_TEMPLATE_IT();
			$template->loadTemplateFile(PORTFOLIO_PATH_TEMPLATES . "artefacts.template.html");
			$user = lms_steam::get_current_user();


			/*
			 * ALLE Artefakte holen
			 */
			$workroom = $user->get_workroom();
			$portfolio_container = $workroom->get_object_by_name("portfolio");
			$artefacts_container = $portfolio_container->get_object_by_name("artefacts");
			$all_container = $artefacts_container->get_inventory();
			foreach ($all_container as $artefact_container) {
				//				$artefact_container->get_inventory();
				$id = $artefact_container->get_object_by_name( $artefact_container->get_name())->get_id();
				$template->setCurrentBlock("BLOCK_ARTEFACT_ROW");
				$template->setVariable("ARTEFACT_PATH", PATH_SERVER . "/download/" .$id . "/" . $artefact_container->get_name());
				$template->setVariable("ARTEFACT_NAME", $artefact_container->get_name());
				$template->setVariable("ARTEFACT_SIZE", $artefact_container->get_name());
				$template->setVariable("ARTEFACT_DESCRIPTION", $artefact_container->get_attribute("DESCRIPTION") === 0 ? "" : $artefact_container->get_attribute("DESCRIPTION"));
				$template->parseCurrentBlock("BLOCK_ARTEFACT_ROW");
			}

			/*
			 * Artefakte eines Portfolios holen
			 */

			//$artefacts =) $this->portfolio->get_artefactes();
			//			if (count($artefacts) > 0) {
			//				foreach ($artefacts as $artefact) {
			//					$template->setCurrentBlock("BLOCK_ARTEFACT_ROW");
			//					$template->setVariable("ARTEFACT_NAME", "HUND");
			//					$template->parseCurrentBlock("BLOCK_ARTEFACT_ROW");
			//				}
			//			} else {
			//				//N$template->setVariable(_ARTEFACTS_TEXT
			//			}

			$this->template->setVariable("HTML_CODE_LEFT", $template->get());
		}
	}

	public function get_context_menu( $context, $params = array() ) {
		return array(array("link" => PATH_SERVER . "/portfolio/artefacts/upload/", "name" => "neues Artefakt hochladen"));
	}


}


?>