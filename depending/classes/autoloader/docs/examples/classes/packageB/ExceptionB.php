<?php

class ExceptionB extends Exception
{

    static public function classConstructor()
    {
        echo __CLASS__, " loaded.\n";
    }

}