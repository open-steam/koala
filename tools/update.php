#!/usr/bin/php
<?php
$pathComposer = __DIR__ . "/../";

echo "** installing or updating composer **" . PHP_EOL;

if (file_exists($pathComposer . "composer.phar")) {
    passthru("cd $pathComposer; php composer.phar --quiet self-update");
} else {
    $returnStatus = false;
    exec('curl 2> /dev/null', $asdf, $returnStatus);
    if ($returnStatus == 127) {
        die("Please install curl (e.g. apt-get install curl or brew install curl");
    }
    passthru("cd $pathComposer; curl -s https://getcomposer.org/installer | php");
}
echo "*************************************" . PHP_EOL . PHP_EOL;
