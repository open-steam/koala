#!/usr/bin/php
<?php

try {
    include dirname(__FILE__) . '/../../Autoloader.php';

    $a = new ClassA();
    $b = new ClassB();

    var_dump($a, $b);

} catch (ExceptionB $e) {

}
