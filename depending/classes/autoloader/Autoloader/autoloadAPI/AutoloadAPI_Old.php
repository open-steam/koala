<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the class AutoloadAPI_Old
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
 * @version    SVN: $Id: AutoloadAPI_Old.php,v 1.4 2011/01/11 14:25:32 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * Requires the parent class
 */
require_once dirname(__FILE__) . '/AutoloadAPI.php';

/**
 * A workaround for a broken spl_autoload_functions()
 *
 * This class keeps internally track of all registered autoloader callbacks.
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
class AutoloadAPI_Old extends AutoloadAPI
{

    private
    /**
     * @var Array callbacks of registerted autoloaders
     */
    $_registeredAutoloaders = array();

    /**
     * Implements the singleton pattern
     */
    protected function __construct()
    {

    }

    /**
     * Returns all autoloaders on the stack
     *
     * As spl_autoload_functions() is broken the internal array
     * $_registeredAutoloaders is returned.
     *
     * @see spl_autoload_functions()
     * @see $_registeredAutoloaders
     * @return Array
     */
    public function getRegisteredAutoloaders()
    {
        return $this->_registeredAutoloaders;
    }

    /**
     * Registers an autoloader at the stack and internally in $_registeredAutoloaders
     *
     * @param Mixed $autoloader The autoloader callback
     *
     * @see $_registeredAutoloaders
     * @return void
     */
    public function registerAutoloader($autoloader)
    {
        parent::registerAutoloader($autoloader);

        $this->_registeredAutoloaders[] = $autoloader;
    }

    /**
     * Removes an autoloader from the stack and from $_registeredAutoloaders
     *
     * @param Mixed $autoloader The autoloader callback
     *
     * @see $_registeredAutoloaders
     * @see spl_autoload_unregister()
     * @return void
     */
    public function removeAutoloader($autoloader)
    {
        parent::removeAutoloader($autoloader);

        $index = array_search(
            $autoloader,
            $this->_registeredAutoloaders,
            true
        );
        if ($index !== false) {
            unset($this->_registeredAutoloaders[$index]);

        }
    }

}