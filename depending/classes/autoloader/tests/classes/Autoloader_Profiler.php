<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the class Autoloader_Profiler
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
 * @version    SVN: $Id: Autoloader_Profiler.php,v 1.3 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The parent class is needed. As autoloading does not work here,
 * it has to be required traditionally.
 */
require_once dirname(__FILE__) . '/../../Autoloader/Autoloader.php';

/**
 * This Autoloader is only for profiling during development of the
 * Autoloaderpackage itself used.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Test
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 */
class Autoloader_Profiler extends Autoloader
{

    private
    /**
     * @var Array
     */
    $_searchedClasses = array();

    /**
     * searchPath() is overwritten to keep track which classes are searched in the
     * file system.
     *
     * @param String $class the class name
     * 
     * @return String
     */
    protected function searchPath($class)
    {
        $this->_searchedClasses[] = $class;
        return parent::searchPath($class);
    }

    /**
     * Returns a list of class names which have been searched in the file system.
     *
     * @return Array
     */
    public function getSearchedClasses()
    {
        return $this->_searchedClasses;
    }

    /**
     * The test will add manually classes to the index.
     *
     * The test will assert with this method, that a class in the index is not
     * searched by the file system.
     *
     * @param String $class The class name
     * 
     * @return void
     */
    public function addClassToIndex($class)
    {
        $this->normalizeClass($class);
        if ($this->index->hasPath($class)) {
            return;

        }
        $this->index->setPath($class, parent::searchPath($class));
    }

}