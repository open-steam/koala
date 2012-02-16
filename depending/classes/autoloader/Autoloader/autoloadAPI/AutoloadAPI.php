<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the class AutoloadAPI
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
 * @subpackage AutoloadAPI
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    SVN: $Id: AutoloadAPI.php,v 1.4 2011/01/11 14:25:32 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * Require the needed class
 */
require_once dirname(__FILE__) . '/AutoloadAPI_Old.php';

/**
 * The class constructor is called.
 */
AutoloadAPI::classConstructor();

/**
 * A wrapper for the spl_autoload functions
 *
 * This class implements the singleton pattern. getInstance() returns
 * the propriate class for the PHP environment.
 * That's because in PHP < 5.2.11 spl_autoload_functions() didn't return objects.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage AutoloadAPI
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        spl_autoload_functions()
 * @see        http://bugs.php.net/44144
 */
class AutoloadAPI
{

    static private
    /**
     * @var AutoloadAPI
     */
    $_instance;

    /**
     * Decides which AutoloadAPI is working in your environment
     *
     * For PHP < 5.2.11 an AutoloadAPI_Old is chosen.
     *
     * @see AutoloadAPI_Old
     * @see http://bugs.php.net/44144
     * @return void
     */
    static public function classConstructor()
    {
        self::$_instance
            = version_compare(PHP_VERSION, "5.2.11", '>=')
            ? new self()
            : new AutoloadAPI_Old();
    }

    /**
     * Returns the AutoloadAPI instance
     *
     * @see AutoloadAPI_Old
     * @return AutoloadAPI
     */
    static public function getInstance()
    {
        return self::$_instance;
    }

    /**
     * Implements the singleton pattern
     */
    private function __construct()
    {

    }

    /**
     * Implements the singleton pattern
     *
     * @return void
     */
    private function __clone()
    {

    }

    /**
     * Returns all autoloaders on the stack
     *
     * @see spl_autoload_functions()
     * @return Array
     */
    public function getRegisteredAutoloaders()
    {
        return spl_autoload_functions();
    }

    /**
     * Registers an autoloader at the stack
     *
     * @param Mixed $autoloader The autoloader callback
     *
     * @see spl_autoload_register()
     * @return void
     */
    public function registerAutoloader($autoloader)
    {
        spl_autoload_register($autoloader);
    }

    /**
     * Removes an autoloader from the stack
     *
     * @param Mixed $autoloader The autoloader callback
     *
     * @see spl_autoload_unregister()
     * @return void
     */
    public function removeAutoloader($autoloader)
    {
        spl_autoload_unregister($autoloader);
    }

}