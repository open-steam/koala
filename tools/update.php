#!/usr/bin/php
<?php

$pathBase = __DIR__ . "/../";

echo "** updating koala **" . PHP_EOL;
passthru("cd $pathBase; git pull");
echo PHP_EOL;

echo "** installing or updating composer **" . PHP_EOL;
if (file_exists($pathBase . "composer.phar")) {
    passthru("cd $pathBase; php composer.phar --quiet self-update");
} else {
    $returnStatus = false;
    exec('curl 2> /dev/null', $asdf, $returnStatus);
    if ($returnStatus == 127) {
        die("Please install curl (e.g. apt-get install curl or brew install curl");
    }
    passthru("cd $pathBase; curl -s https://getcomposer.org/installer | php");
}
passthru("cd $pathBase; php composer.phar update");
echo "*************************************" . PHP_EOL . PHP_EOL;

echo "** run setup and cleanup **" . PHP_EOL;
passthru("cd $pathBase/tools; php setup.php");
passthru("cd $pathBase/tools; php cleanup.php");
echo "*************************************" . PHP_EOL . PHP_EOL;
