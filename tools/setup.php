#!/usr/bin/php
<?php
session_name("koala-3");
session_start();  // prevent warnings concerning previous output (session_start() is called in koala.conf.php, which is included later in the file)


define("TIMEZONE", "Europe/Berlin");

function output($msg) {
    fputs(STDOUT, $msg);
}


include_once( "../core/etc/version.php" );
include_once( "../core/lib/config_handling.inc.php" );

output("koaLA " . KOALA_VERSION . " setup\n\n");

$success = TRUE;


// check file system permissions
$required_paths = array();
$required_paths[] = new RequiredPath(RequiredPath::$DIR, "../platforms/bid/cache", 0777);
$required_paths[] = new RequiredPath(RequiredPath::$DIR, "../platforms/bid/log", 0777);
$required_paths[] = new RequiredPath(RequiredPath::$DIR, "../platforms/bid/temp", 0777);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/bid/log/403.log", 0666);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/bid/log/404.log", 0666);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/bid/log/errors.log", 0666);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/bid/log/messages.log", 0666);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/bid/log/security.log", 0666);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/bid/public/.htaccess", 0644, "../platforms/bid/public/.htaccess.example");

$required_paths[] = new RequiredPath(RequiredPath::$DIR, "../platforms/elab/cache", 0777);
$required_paths[] = new RequiredPath(RequiredPath::$DIR, "../platforms/elab/log", 0777);
$required_paths[] = new RequiredPath(RequiredPath::$DIR, "../platforms/elab/temp", 0777);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/elab/log/403.log", 0666);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/elab/log/404.log", 0666);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/elab/log/errors.log", 0666);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/elab/log/messages.log", 0666);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/elab/log/security.log", 0666);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/elab/public/.htaccess", 0644, "../platforms/elab/public/.htaccess.example");

$required_paths[] = new RequiredPath(RequiredPath::$DIR, "../platforms/schulen-gt/cache", 0777);
$required_paths[] = new RequiredPath(RequiredPath::$DIR, "../platforms/schulen-gt/log", 0777);
$required_paths[] = new RequiredPath(RequiredPath::$DIR, "../platforms/schulen-gt/temp", 0777);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/schulen-gt/log/403.log", 0666);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/schulen-gt/log/404.log", 0666);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/schulen-gt/log/errors.log", 0666);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/schulen-gt/log/messages.log", 0666);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/schulen-gt/log/security.log", 0666);
$required_paths[] = new RequiredPath(RequiredPath::$FILE, "../platforms/schulen-gt/public/.htaccess", 0644, "../platforms/schulen-gt/public/.htaccess.example");

$required_paths[] = new RequiredPath(RequiredPath::$DIR, "../extensions/content/worksheet/templates_c", 0777);
$required_paths[] = new RequiredPath(RequiredPath::$DIR, "../tools/bid-tools/asciisvg/imgs", 0777);

$required_paths_okay = TRUE;
output("Checking files and directories for existance and permissions ...\n");
foreach ($required_paths as $path) {
    if (!$path->check(TRUE))
        $required_paths_okay = FALSE;
}
if (!$required_paths_okay) {
    $ask_fix = new ConfigEntry("fix", "YesNo", "Create missing files/directories and fix permissions?", NULL, "yes");
    if ($ask_fix->ask() == "no")
        $success = FALSE;
    else {
        $required_paths_okay = TRUE;
        foreach ($required_paths as $path)
            if (!$path->fix(TRUE))
                $required_paths_okay = FALSE;
        if (!$required_paths_okay) {
            output("Could not fix all files/directories.\n");
            exit(1);
        }
    }
}
else
    output("Files and directory permissions are okay.\n\n");

// utility class for checking required files and directories:

class RequiredPath {

    public static $FILE = "File";
    public static $DIR = "Directory";
    public $path;
    public $template_path;
    public $mode;
    public $type;

    function RequiredPath($type, $path, $mode, $template_path = FALSE) {
        $this->type = $type;
        $this->path = $path;
        $this->mode = $mode;
        $this->template_path = $template_path;
    }

    function check($verbose = FALSE) {
        $okay = TRUE;
        if (!$this->check_exists($verbose))
            $okay = FALSE;
        if (!$this->check_mode($verbose))
            $okay = FALSE;
        return $okay;
    }

    function check_exists($verbose = FALSE) {
        if ($this->type == self::$DIR && is_dir($this->path))
            return TRUE;
        if ($this->type == self::$FILE && file_exists($this->path))
            return TRUE;
        if ($verbose)
            output("  " . $this->type . " " . $this->path . " is missing.\n");
        return FALSE;
    }

    function check_mode($verbose = FALSE) {
        if (!file_exists($this->path))
            return FALSE;
        $stat = stat($this->path);
        $mode = $stat["mode"] & 0777;
        if ($mode == $this->mode)
            return TRUE;
        if ($verbose)
            output("  " . $this->type . " " . $this->path . " has wrong permissions: " . sprintf("%o", $mode) . ".\n");
        return FALSE;
    }

    function fix($verbose = FALSE) {
        if (!$this->check_exists(FALSE)) {
            if ($this->type == self::$FILE) {
                if (is_string($this->template_path)) {
                    if (!copy($this->template_path, $this->path)) {
                        if ($verbose)
                            output("Could not copy " . $this->template_path . " to " . $this->path . "\n");
                        return FALSE;
                    }
                    if ($verbose)
                        output("Copied " . $this->template_path . " to " . $this->path . "\n");
                }
                else {
                    if (!touch($this->path)) {
                        if ($verbose)
                            output("Could not create file " . $this->path . "\n");
                        return FALSE;
                    }
                    if ($verbose)
                        output("Created file " . $this->path . "\n");
                }
            }
            else if ($this->type == self::$DIR) {
                if (is_string($this->template_path)) {
                    RecursiveCopy($this->template_path, $this->path);
                } else {
                    if (!mkdir($this->path, $this->mode)) {
                        if ($verbose)
                            output("Could not create directory " . $this->path . "\n");
                        return FALSE;
                    }
                    if ($verbose)
                        output("Created directory " . $this->path . "\n");
                }
            }
        }
        if (!$this->check_mode(FALSE)) {
            if (!chmod($this->path, $this->mode)) {
                if ($verbose)
                    output("Could not set permissions of " . $this->path . " to " . sprintf("%o", $this->mode) . "\n");
                return FALSE;
            }
            if ($verbose)
                output("Changed permissions of " . $this->path . " to " . sprintf("%o", $this->mode) . "\n");
        }
        return TRUE;
    }

}

// utility functions for checking open-sTeam structures:

function check_steam_group($group_name, $parent_group_name = NULL, $description = NULL, $fix = FALSE) {
    $parent_group = NULL;
    if (is_string($parent_group_name)) {
        $parent_group = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), $parent_group_name);
        if (!is_object($parent_group)) {
            output("Error: could not find parent group '" . $parent_group_name . "' for group '" . $group_name . "'.\n");
            return FALSE;
        }
    }
    $group_fullname = ( is_string($parent_group_name) ? $parent_group_name . "." : "" ) . $group_name;
    $group = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), $group_fullname);
    if (is_object($group))
        return $group;
    if (!$fix)
        return FALSE;
    $group = steam_factory::create_group($GLOBALS["STEAM"]->get_id(), $group_name, $parent_group, NULL, $description);
    if (is_object($group))
        output("Created group '" . $group_fullname . "'.\n");
    else
        output("Error: could not create group '" . $group_fullname . "'.\n");
    return $group;
}

function check_steam_container($container_path, $description = "", $fix = FALSE) {
    $container = steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $container_path);
    if (!is_object($container)) {
        if (!$fix)
            return FALSE;
        if (!is_object($container_environment = steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), dirname($container_path)))) {
            output("Error: could not get parent directory for container: '" . dirname($container_path) . "'\n");
            return FALSE;
        }
        if (!is_object($container = steam_factory::create_container($GLOBALS["STEAM"]->get_id(), basename($container_path), $container_environment, $description))) {
            output("Error: could not create container: '" . $container_path . "'.\n");
            return FALSE;
        }
        output("Created container: '" . $container_path . "'.\n");
    }
    return $container;
}

function check_steam_access($steam_object, $permissions, $fix = FALSE) {
    if (!is_object($steam_object))
        return NULL;
    $okay = TRUE;
    foreach ($permissions as $who_id => $what) {
        $who = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $who_id);
        if ($steam_object->query_sanction($who) != $what) {
            if (!$fix)
                $okay = FALSE;
            else {
                $steam_object->set_sanction($who, $what);
                if ($steam_object->query_sanction($who) == $what)
                    output("Fixed permissions for '" . $steam_object->get_name() . "'.\n");
                else {
                    $okay = FALSE;
                    output("Error: could not fix permissions for '" . $steam_object->get_name() . "'.\n");
                }
            }
        }
    }
    return $okay;
}

function RecursiveCopy($source, $dest, $diffDir = '') {
    $sourceHandle = opendir($source);
    mkdir($dest . '/' . $diffDir);

    while ($res = readdir($sourceHandle)) {
        if ($res == '.' || $res == '..')
            continue;

        if (is_dir($source . '/' . $res)) {
            RecursiveCopy($source . '/' . $res, $dest, $diffDir . '/' . $res);
        } else {
            copy($source . '/' . $res, $dest . '/' . $diffDir . '/' . $res);
        }
    }
}
?>