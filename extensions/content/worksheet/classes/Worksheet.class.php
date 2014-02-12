<?php

    namespace Worksheet;

    /**
    * worksheet dataclass
    * -------------
    *
    * by Tobias Kempkensteffen <tobias.kempkensteffen@gmail.com>
    *
    */
    class Worksheet
    {

        protected $id;
        protected $steamObj;

        public function getId()
        {
            return $this->id;
        }

        public function setup()
        {

            $this->steamObj->set_attribute("worksheet_valid", true);
            $this->steamObj->set_attribute("worksheet_role", "build");

        }

        public function validateRole($role)
        {
            $data = $this->getRole();
            if ($data != $role) {
                header("Location: ".PATH_URL."/worksheet/Index/".$this->id);
            }
        }

        public function validateStatus($status)
        {
            $data = $this->getStatus();
            if ($data != $status) {
                header("Location: ".PATH_URL."/worksheet/Index/".$this->id);
            }
        }

        public function setName($name)
        {
            $this->steamObj->set_name($name);
        }

        public function getName()
        {
            return $this->steamObj->get_name();
        }

        public function getStatus()
        {
            /*
            * 1 = edit by user
            * 2 = correction
            * 3 = closed
            */

            $data = $this->steamObj->get_attribute("worksheet_status");

            if (!isset($data) OR $data == "") {
                $data = "1";
                $this->steamObj->set_attribute("worksheet_status", $data);
            }

            return $data;

        }

        public function setStatus($status)
        {
            $this->steamObj->set_attribute("worksheet_status", $status);
        }

        public function getRole()
        {
            return $this->steamObj->get_attribute("worksheet_role");
        }

        public function setRole($role)
        {
            $this->steamObj->set_attribute("worksheet_role", $role);
        }

        public function resetEditCopiesList()
        {
            $this->steamObj->set_attribute("worksheet_edit_copies", Array());
        }

        public function getEditCopiesList()
        {

            $data = $this->steamObj->get_attribute("worksheet_edit_copies");

            $ret = Array();

            if (is_array($data)) {

                foreach ($data as $d) {

                    $user = $d['userId']; //TODO: should be replaced by user object
                    $worksheet = new \Worksheet\Worksheet($d['objId']);

                    $ret[] = Array(
                        "user" => $user,
                        "worksheet" => $worksheet
                    );

                }

                return $ret;

            } else return Array();

        }

        public function getEditCopies()
        {

            $data = $this->steamObj->get_attribute("worksheet_edit_copies");

            if (is_array($data)) {
                return $data;

            } else return Array();

        }

        public function addEditCopy($userId, $objId)
        {

            $copies = $this->getEditCopies();

            $copies[] = Array(
                "objId" => $objId,
                "userId" => $userId
            );

            $this->steamObj->set_attribute("worksheet_edit_copies", $copies);

        }

        /*
        * create an object representing a worksheet
        */
        public function __construct($id)
        {
            $this->getById($id);
        }

        /*
        *  Load the worksheet by id
        */
        function getById($id)
        {

            $obj = \steam_factory::get_object($GLOBALS[ "STEAM" ]->get_id(), $id);

            $this->id = $id;
            $this->steamObj = $obj;

            if ($this->getRole() == "") {
                $this->setRole("build");
            }

            return true;

        }

        public function archiveSolutions()
        {

            $objects = $this->steamObj->get_inventory();

            $blocks = Array();

            foreach ($objects as $object) {

                if ($object instanceof \steam_document) {

                    $block = new Block($object->get_id());
                    $block->archiveSolution();

                }

            }

        }

        public function getMaxScore()
        {
            $score = 0;

            $blocks = $this->getBlocks();

            foreach ($blocks as $block) {

                $s = $block->getMaxScore();
                if ($s === false) return false;

                $score += $s;

            }

            return $score;

        }

        public function getScore()
        {
            $score = 0;

            $blocks = $this->getBlocks();

            foreach ($blocks as $block) {

                $s = $block->getScore();
                if ($s === false) return false;

                $score += $s;

            }

            return $score;

        }

        public function getBlocks()
        {

            $objects = $this->steamObj->get_inventory();

            $blocks = Array();

            foreach ($objects as $object) {

                if ($object instanceof \steam_document) {
                    $blocks[] = new Block($object->get_id());
                }

            }

            usort($blocks, "self::blockSort");

            return $blocks;

        }

        public static function blockSort($a, $b)
        {
            $aOrder = $a->getOrder();
            $bOrder = $b->getOrder();

            if ($aOrder == -1) return 1; //a < b
            if ($bOrder == -1) return -1; //a > b

            if ($aOrder == $bOrder) return 0; //a == b

            if ($aOrder > $bOrder) {
                return 1;
            } else {
                return -1;
            }

        }

        public function createBlock($type)
        {
            return \Worksheet\Block::create($type, $this->steamObj);

        }

        public function deleteBlock($blockId)
        {

            $doc = \steam_factory::get_object($GLOBALS[ "STEAM" ]->get_id(), $blockId);

            $doc->delete();

        }

        public function deploy()
        {

            if ($this->getRole() != "build") throw new Exception("only worksheets with role 'build' can be deployed!");

            $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
            $userWorkroom = $currentUser->get_workroom();

            $newObj = $this->steamObj->copy();

            $newObj->move($userWorkroom);

            $newWorksheet = new self($newObj->get_id());

            $newWorksheet->setup();

            $newWorksheet->setRole("view");

            $name = $newWorksheet->getName();
            $name = preg_replace('!(\ )*\(Vorlage\)!isU', '', $name);
            $name = $name." (Verteilkopie)";

            $newWorksheet->setName($name);

            return $newWorksheet;

        }

        public function startEdit()
        {

            if ($this->getRole() != "view") throw new Exception("only worksheets with role 'view' can be edited!");

            $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
            $userWorkroom = $currentUser->get_workroom();

            $newObj = $this->steamObj->copy();

            $newObj->move($userWorkroom);

            $newWorksheet = new self($newObj->get_id());

            $newWorksheet->setRole("edit");

            $name = $newWorksheet->getName();
            $name = preg_replace('!(\ )*\(Vorlage\)!isU', '', $name);
            $name = preg_replace('!(\ )*\(Verteilkopie\)!isU', '', $name);
            $name = $name." (Arbeitskopie)";

            $newWorksheet->setName($name);

            $this->addEditCopy($currentUser->get_id(), $newWorksheet->getId());

            return $newWorksheet;

        }

        /* finish editing the worksheet --> teacher will be able to correct it */
        public function finish()
        {

            $this->setStatus(2);

        }

        public function correctionFinish($lock=true)
        {
            $this->archiveSolutions();

            if ($lock) {
                $this->setStatus(3);
            } else {
                $this->setStatus(1);
            }

        }

    }
