<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the class InternalAutoloader
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
 * @category  PHP
 * @package   Autoloader
 * @author    Markus Malkusch <markus@malkusch.de>
 * @copyright 2009 - 2010 Markus Malkusch
 * @license   http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version   SVN: $Id: InternalAutoloader.php,v 1.4 2011/01/11 14:25:32 nicke Exp $
 * @link      http://php-autoloader.malkusch.de/en/
 */

/**
 * These classes are needed. As autoloading does not work here,
 * they have to be required traditionally.
 */
require_once
    dirname(__FILE__) . '/AbstractAutoloader.php';
require_once
    dirname(__FILE__)
    . '/exception/AutoloaderException_InternalClassNotLoadable.php';

/**
 * The autoloader for internal classes
 *
 * @category PHP
 * @package  Autoloader
 * @author   Markus Malkusch <markus@malkusch.de>
 * @license  http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version  Release: 1.11
 * @link     http://php-autoloader.malkusch.de/en/
 */
class InternalAutoloader extends AbstractAutoloader
{

    static private
    /**
     * @var InternalAutoloader
     */
    $_instance;

    private
    /**
     * @var array
     */
    $_classes = array();

    /**
     * Creates the only instance of this class and registeres it to the autoload
     * stack
     *
     * @see register()
     * @see getInstance()
     * @return void
     */
    static public function classConstructor()
    {
        self::$_instance = new self();
        self::$_instance->register();
    }

    /**
     * Returns the only instance of this class
     *
     * @see classConstructor()
     * @return InternalAutoloader
     */
    static public function getInstance()
    {
        return self::$_instance;
    }

    /**
     * Returns all registered InternalAutoloader instances which are doing their
     * jobs
     *
     * @see register()
     * @return Array
     */
    static public function getRegisteredAutoloaders()
    {
        $autoloaders = array();
        foreach (parent::getRegisteredAutoloaders() as $autoloader) {
            if ($autoloader instanceof self) {
                $autoloaders[] = $autoloader;

            }
        }
        return $autoloaders;
    }

    /**
     * Removes all instances of InternalAutoloader from the stack
     *
     * @see remove()
     * @return void
     */
    static public function removeAll()
    {
        //TODO use static:: in PHP 5.3 and remove the other implementations
        foreach (self::getRegisteredAutoloaders() as $autoloader) {
            $autoloader->remove();

        }
    }

    /**
     * Private constructor as this is a singleton
     */
    private function __construct()
    {

    }

    /**
     * Private __clone() as this is a singleton
     *
     * @return void
     */
    private function __clone()
    {

    }

    /**
     * Used for internal classes, which cannot use the Autoloader
     *
     * They will be required in a traditional way without any index or searching.
     *
     * @param String $class The class name
     * @param String $path  The path of the class
     *
     * @return void
     */
    public function registerClass($class, $path)
    {
        Autoloader::normalizeClass($class);
        $this->_classes[$class] = $path;
    }

    /**
     * Implements autoloading for internal classes
     *
     * @param String $class The class name
     *
     * @throws AutoloaderException_InternalClassNotLoadable
     * @throws AutoloaderException_Include
     * @throws AutoloaderException_Include_FileNotExists
     * @throws AutoloaderException_Include_ClassNotDefined
     * @return void
     */
    protected function doAutoload($class)
    {
        if (!  array_key_exists($class, $this->_classes)) {
            throw new AutoloaderException_InternalClassNotLoadable($class);

        }
        $this->loadClass($class, $this->_classes[$class]);
    }

}

/**
 * The class constructor is called.
 */
InternalAutoloader::classConstructor();