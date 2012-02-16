<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the test cases for AutoloaderCallbackDummy
 *
 * PHP version 5
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.
 * If not, see <http://php-autoloader.malkusch.de/en/license/>.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Test
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    SVN: $Id: AutoloaderCallbackDummy.php,v 1.3 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * Callbacks for TestAutoloadAPI
 *
 * These Autoloaders create any demanded class with the method
 * <code>
 * public function getName()
 * {
 *     return get_class($this);
 * }
 * </code>
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Test
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        TestAutoloadAPI
 */
class AutoloaderCallbackDummy
{

    /**
     * Creates an empty class
     *
     * @param String $classname The name of the class
     *
     * @return void
     */
    static public function createClass($classname)
    {
        eval(
            "
            class $classname
            {

                public function getName()
                {
                    return get_class(\$this);
                }

            }
            "
        );
    }

    /**
     * A static callback
     *
     * @param String $classname The name of the class
     *
     * @return void
     */
    static public function staticAutoload1($classname)
    {
        self::createClass($classname);
    }

    /**
     * A static callback
     *
     * @param String $classname The name of the class
     *
     * @return void
     */
    static public function staticAutoload2($classname)
    {
        self::createClass($classname);
    }

    /**
     * An object callback
     *
     * @param String $classname The name of the class
     *
     * @return void
     */
    public function objectAutoload1($classname)
    {
        self::createClass($classname);
    }

    /**
     * An object callback
     *
     * @param String $classname The name of the class
     *
     * @return void
     */
    public function objectAutoload2($classname)
    {
        self::createClass($classname);
    }

}

/**
 * A function callback
 *
 * @param String $classname The name of the class
 *
 * @return void
 */
function functionCallback1($classname)
{
    AutoloaderCallbackDummy::createClass($classname);
}

/**
 * A function callback
 *
 * @param String $classname The name of the class
 *
 * @return void
 */
function functionCallback2($classname)
{
    AutoloaderCallbackDummy::createClass($classname);
}