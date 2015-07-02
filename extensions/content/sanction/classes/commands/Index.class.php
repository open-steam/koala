<?php
namespace Sanction\Commands;


/*
 * 
 * icons von der extension explorer beziehen oder dublizieren?
 * 
 */


class Index extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;
    private $owner;
    private $ownerFullName;
    private $creatorId;
    private $steamObject;
    private $steamUser;
    private $object;
    private $sanctionRaw;
    private $environment;
    private $environmentName ="";
    private $environmentSanction = false;
    
    //acquire is an object, if the rights are acquired, otherwise it is an integer value
    private $acquire;
    
    //if $this->acquire is an object of the type steam_container
    private $rightsAreAcquired = false;
    
    
    private static function generateCheckboxRaw($id, $sanctionString, $checked, $readOnly, $onChangeFunction=""){        
        $id = "sanction_".$id."_".$sanctionString;
        $checkedValue = ($checked)? "checked=\"checked\"":'';
        $readOnlyValue = ($readOnly)? "disabled":'';
        
        if($sanctionString == "sanction"){
            $onChangeFunction = "sanctionCancelWarning(id, checked);";
            
        }
        
        return "<input ".$readOnlyValue." id=\"".$id."\" name=\"".$id."\" type=\"checkbox\" ".$checkedValue." onChange=\"".$onChangeFunction."\">";
    }


    private function getRightsForGroup($group){
        return Array(
            "read" => $this->isInheritedSanction($group, SANCTION_READ),
            "write" => $this->isInheritedSanction($group, SANCTION_WRITE),
            "execute" => $this->isInheritedSanction($group, SANCTION_EXECUTE),
            "move" => $this->isInheritedSanction($group, SANCTION_MOVE),
            "insert" => $this->isInheritedSanction($group, SANCTION_INSERT),
            "annotate" => $this->isInheritedSanction($group, SANCTION_ANNOTATE),
            "sanction" => $this->isInheritedSanction($group, SANCTION_SANCTION)
        ); 
    }
    
    
    
    private function isInheritedSanction($steamUserOrGroup, $sanction){
        //wenn check_access true liefert und der Wert mit dem in dem 
        //sanction array nicht übereinstimmt, muss der Wert geerbt sein
        //gucke, ob ich überhaupt die Berechitugung zu $sanction habe
        //return Array(false, true);
        
        if(is_object($this->object) && ($steamUserOrGroup instanceof \steam_group OR $steamUserOrGroup instanceof \steam_user))
        {
            $access = $this->object->check_access($sanction, $steamUserOrGroup);
        }
        if($access){
            
            //Zu der Gruppe / dem Nutzer existiert in dem Array, das $steamObject->get_sanction()
            //liefert ein Eintrag.
            //Daraus folgt, dass mindestens ein Recht für die Gruppe / den Nutzer
            //explizit gesetzt wurde (ins. nicht geerbt ist)
          
            if(isset($this->sanctionRaw[$steamUserOrGroup->get_id()])){
            
                //jetzt ist zu prüfen ob es das Recht ist, das übergeben wurde
                //und dieses explizit gesetzt wurde
                $comparasion = $this->sanctionRaw[$steamUserOrGroup->get_id()] & $sanction;
                if($comparasion === $sanction){
                    //return false if the value is set explicitly due to the function name is "isInheritedSanction"
                    return Array(true, false);
                }

            } 
            //der Nutzer hat das Recht in Sanction, es wurde allerdings geerbt und kann somit 
            //hier nicht entzogen werden. (=> Checkbox ist ausgegraut)
            return Array(true, true);
            

        }
        //der Nutzer/ die Gruppe hat weder das Recht in $sanction noch ist der Wert geerbt
        return Array(false, false);
        
    }

    
    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : $this->id = "";
    }

    
    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        

        $this->steamObject = $GLOBALS["STEAM"];
        $this->steamUser = \lms_steam::get_current_user();
        
        //check if an ID of an object was assigned else throw an exception
        if ($this->id === "") {
            $errorHtml = new \Widgets\RawHtml();
            $errorHtml->setHtml("Es wurde keine ID eines Objektes übergeben.");
            $frameResponseObject->addWidget($errorHtml);
            return $frameResponseObject;
        }

        $this->object = \steam_factory::get_object($this->steamObject->get_id(), $this->id);
        
        //check if it was possible to build an object for that ID
        if(!is_object($this->object)){
            $noObjectHtml = new \Widgets\RawHtml();
            $noObjectHtml->setHtml("Es konnte kein Objekt mit der ID ".$this->id." erzeugt werden.");
            $frameResponseObject->addWidget($noObjectHtml);
            return $frameResponseObject;
        }
        
        //check if it is allowed to grant rights
        if (!$this->object->check_access(SANCTION_SANCTION)) {
            $labelDenied = new \Widgets\RawHtml();
            $labelDenied->setHtml("Sie haben keine Berechtigung die Rechte einzusehen oder zu verändern!");
            $frameResponseObject->addWidget($labelDenied);
            return $frameResponseObject;
        }
        

        //GET OWNER OF THE CURRENT OBJECT
        $this->owner = $this->object->get_creator();
        $this->creatorId = $this->owner->get_id();
        if($this->owner instanceof \steam_user){
            $this->ownerFullName = $this->owner->get_full_name();
        } else {
            $this->ownerFullName = getCleanName($this->owner);
        }
        
        
        
        //ACQUIRE SETTINGS
        //check if the environment is a steam_container otherwise it sould not be 
        //possible to inherit the rights from the environment
        //get acquire returns the object, the rights are acquired from
        //or an integervalue if no rights are acquired
        $this->acquire = $this->object->get_acquire();
        $this->rightsAreAcquired = $this->acquire instanceof \steam_container ? true : false;
        

        //Get the environmentObject
        $this->environment = $this->object->get_environment();

        if ($this->environment instanceof \steam_container) {
            $this->environmentSanction = $this->environment->get_sanction();
            $this->environmentName = getCleanName($this->environment);
        }
        
        
        //GET FAVORITES
        $favs = $this->steamUser->get_buddies();
        $favorites = array();
        foreach ($favs as $fav) {
            $favorites[$fav->get_id()] = $fav;
        }

        //get groups, the user is member of
        $usersGroups = $this->steamUser->get_groups();
        $groups = array();
        foreach ($usersGroups as $group) {
            $groups[$group->get_id()] = $group;
        }
        
       
        //GET GROUP 'EVERYONE'
        $everyone = \steam_factory::groupname_to_object($this->steamObject->get_id(), "everyone");
        $everyoneId = $everyone->get_id();
        
        
        //GET GROUP 'STEAM'
        $steamgroup = \steam_factory::groupname_to_object($this->steamObject->get_id(), "sTeam");
        $steamgroupId = $steamgroup->get_id();
        
        
        
        //Set the SANCTION objectiv-wide
        $this->sanctionRaw = $this->object->get_sanction();

        
        
        
        //check if there are additional users or grous that have rights 
        //concerning the current object
        $user = array();
        foreach ($this->sanctionRaw as $id => $sanct) {
            if (!array_key_exists($id, $groups) &&
                    !array_key_exists($id, $favorites) &&
                    $id != $this->creatorId && $id != 0 &&
                    $id != $everyoneId) {
                
                $additionalObject = \steam_factory::get_object($this->steamObject->get_id(), $id);
                if ($additionalObject instanceof \steam_group) {
                    $groups[$id] = $additionalObject;

                } else if ($additionalObject instanceof \steam_user) {
                    $user[$id] = $additionalObject;
                    
                } else {
                    throw new \Exception("Ungültiger Objekttyp hat Rechte an dem aktuellen Objekt.");
                }
            }
        }


        foreach ($favorites as $id => $favi) {
            if ($favi instanceof \steam_user) {
                $user[$id] = $favi;
            } else if ($favi instanceof \steam_group) {
                $groups[$id] = $favi;
            } else {
                throw new \Exception("Favoriten beeinhalten das Objekt einer ungültigen Klasse!");
            }
        }

      
        
        $groupMappingA = array();
        $groupMappingName = array();
        foreach ($groups as $g) {
            $id = $g->get_id();
            $name = $g->get_groupname();
            $groupMappingA[$id] = $name;
            $groupMappingName[$name] = $id;
        }
        
        foreach ($groupMappingA as $name) {
            
            $array = explode(".", $name);
            $length = count($array);
            if ($length > 1) {

                $string = "";
                for ($i = 0; $i < $length; $i++) {
                    if ($i == 0) {
                        $string .= $array[$i];
                    } else {
                        $string .= "." . $array[$i];
                    }
                    if (!isset($groupMappingName[$string])) {
                        $group = \steam_factory::get_group($this->steamObject->get_id(), $string);
                        $groupId = $group->get_id();
                        $groupMappingName[$string] = $groupId;
                        $groupMappingA[$groupId] = $string;
                        $groups[$groupId] = $group;
                    }    
                }
            }
        }
        
        
        asort($groupMappingA);
        
        $groupMapping = array();
        foreach ($groupMappingA as $id => $name) {
            $groupMapping[$id] = $groups[$id];
        }

        
        //MAPPING USER
        $userMapping = array();
        foreach ($user as $id => $u) {
            if ($u instanceof \steam_user) {
                $userMapping[$id] = $u->get_full_name();
            }
        }
        asort($userMapping);


        //load the template for the view and assign variabels 
        $content = \Sanction::getInstance()->loadTemplate("sanction.template.html");
        


        //***PICTURES**//
        
        //paths to the images
        
        $userPicUrl = PATH_URL . "explorer/asset/icons/user.png";
        $groupPicUrl = PATH_URL . "explorer/asset/icons/group.png";
        $favPicUrl = PATH_URL . "explorer/asset/icons/red.png";
        
        //assign template variables
        $content->setVariable("OBJECT_ID", $this->id);        
        $content->setVariable("OWNER_FULL_NAME", $this->ownerFullName);
        $content->setVariable("TITLE", getCleanName($this->object));
        $content->setVariable("IS_ACQUIRED", ($this->acquire instanceof \steam_container)? "checked":"");
        $content->setVariable("IS_ACQUIRED_DISABLE_FORM", ($this->acquire instanceof \steam_container)? "color:#AAA;":"");
        $content->setVariable("IS_ACQUIRED_DISABLE_BUTTONS", ($this->acquire instanceof \steam_container)? "disabled":"");
        
        
        $content->setVariable("INHERIT_FROM", getCleanName($this->environment));
        $content->setVariable("NO_ENVIRONMENT_ADAPT_TEXT", ($this->environmentName == "")? "S": "Oder s");
         if ($this->environmentName == "") {
            $content->setVariable("NO_ENVIRONMENT", "disabled");
            $content->setVariable("DISPLAY_INHERIT_FROM", "style=\"display:none\" ");
        }

        
        //***Check access for every user regardless of whether logged in or not***//
        //id of the usergroup 'everyone'
        $sanctionsEveryone = $this->getRightsForGroup($everyone);
        foreach ($sanctionsEveryone as $key => $value) {
                    $checkbox = $this::generateCheckboxRaw($everyoneId,$key, $value[0], ($value[1] OR $this->acquire instanceof \steam_container));
                    $content->setVariable("EVERYONE_".$key, $checkbox);
                }
                
        $sanctionsSteamUser = $this->getRightsForGroup($steamgroup);
        foreach ($sanctionsSteamUser as $key => $value) {
                    $checkbox = $this::generateCheckboxRaw($steamgroupId, $key, $value[0], ($value[1] OR $this->acquire instanceof \steam_container));
                    $content->setVariable("STEAM_USER_".$key, $checkbox);
                }
        


        //TEMPLATE GROUPS

        if (count($groupMapping) == 0) {
            $content->setVariable("NO_GROUP_MEMBER", "Sie sind kein Mitglied einer Gruppe");
        } else {
            
            
            //for every group the user is a member of
            foreach ($groupMapping as $groupId => $group) {
                $name = $group->get_attribute("OBJ_DESC");
                $realGroupName = $group->get_name();
                if ($name == "" || $name == "0") {
                    $name = $group->get_name();
                }
                $groupVisibility = $group->get_attribute("GROUP_INVISIBLE");
                if($groupVisibility != 0){
                    unset($groupMapping[$groupId]); 
                   continue;
                }
                $groupName = $group->get_groupname();
                
                //the sTeamgroup is already covered as "Angemeldete Benutzer" and is not listed in the groups section
                if($groupName == "sTeam"){
                    continue;
                } 
                
                $sanctions = $this->getRightsForGroup($group);


                $content->setCurrentBlock("GROUPS");
                //generate in every group-block in the template the checkboxes 
                //and check them if the specificright is granted
                foreach ($sanctions as $key => $value) {
                    $checkbox = $this::generateCheckboxRaw($groupId, $key, $value[0], ($value[1] OR $this->acquire instanceof \steam_container));
                    $content->setVariable($key, $checkbox);
                }
                
        
                
                if (isset($favorites[$groupId])) {
                    $content->setVariable("IMG_PATH", $favPicUrl);
                } else {
                    $content->setVariable("IMG_PATH", $groupPicUrl);
                }
                
                //a '.' in the group name means that the group is a sub-group
                //to visualise that order, intend the subgroup
                $intend = count(explode(".", $groupName));
                $content->setVariable("INDENTINDEX", $intend);
                
                $content->setVariable("GROUPNAME",  $realGroupName. " (". $name . ")" );
                    
                    
                $content->setVariable("GROUPID", $groupId);
                $content->setVariable("GROUP_ID", $groupId);
                
    
                $content->parse("GROUPS");

            }
        }
        

        //TEMPLATE FAVORITES
        if (count($userMapping) == 0) {
            $content->setVariable("NO_FAV_MEMBER", "Es können keinem Benutzer Rechte zugewiesen werden. ");
        } else { 
            foreach ($userMapping as $id => $name) {
                $favo = \steam_factory::get_object($this->steamObject->get_id(), $id);
                if ($favo instanceof \steam_user) {
                   
                    $sanctions = $this->getRightsForGroup($favo);

                    
                    $content->setCurrentBlock("FAVORITES");
                    //generate in every group-block in the template the checkboxes 
                    //and check them if the specificright is granted
                    foreach ($sanctions as $key => $value) {
                        $checkbox = $this::generateCheckboxRaw($id, $key, $value[0], ($value[1] OR $this->acquire instanceof \steam_container));
                        $content->setVariable($key, $checkbox);
                  
                    }
                    $intend = count(explode(".", $groupName));
                    $content->setVariable("INDENTINDEX", $intend);
                
                    $content->setVariable("GROUPNAME", $favo->get_full_name());
                    if (isset($favorites[$id])) {
                        $content->setVariable("IMG_PATH", $favPicUrl);
                    } else {
                        $content->setVariable("IMG_PATH", $userPicUrl);
                    }
                    $content->parse("FAVORITES");
                }
            }
        }
        
        //template attributes
        $attributes = $this->object->get_all_attributes();
        
        foreach($attributes as $key => $value){
            $content->setCurrentBlock("ATTRIBUTES");
            $content->setVariable("KEY", $key);
            if(is_array($value)){
                $content->setVariable("VALUE", "<pre>".var_export($value, true)."</pre>");
            }
            else if(is_object($value)){
                $content->setVariable("VALUE", "Objekt mit der Id ".$value);
            }
            else {
                $content->setVariable("VALUE", $value);
            }
            if($this->object->is_locked($key)) $content->setVariable("LOCKED", "x");
            $content->parse("ATTRIBUTES");
    
        }
        
        $annotations = $this->object->get_annotations();
        
        foreach($annotations as $key => $value){
            $content->setCurrentBlock("ANNOTATIONS");
            $content->setVariable("KEY", $key);
            if(is_array($value)){
                $content->setVariable("VALUE", "<pre>".var_export($value, true)."</pre>");
            }
            else if(is_object($value)){
                $content->setVariable("VALUE", "Objekt mit der Id ".$value);
            }
            else {
                $content->setVariable("VALUE", $value);
            }
            
            $content->parse("ANNOTATIONS");
    
        }
        
        $content->setVariable("SERIALIZE", $this->object->get_references());

        //start generating the output
        $output = new \Widgets\RawHtml();
        $output->setHtml($content->get());
        
        $frameResponseObject->addWidget($output);
       
        return $frameResponseObject;
        
    }

}

?>