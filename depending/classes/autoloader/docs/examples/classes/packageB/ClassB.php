<?php

class ClassB implements InterfaceB
{

    static public function classConstructor()
    {
        echo __CLASS__, " loaded.\n";
    }

} 