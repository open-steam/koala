<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Implements the class AutoloaderIndex_Dummy
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
 * @subpackage Index
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    SVN: $Id: AutoloaderIndex_Dummy.php,v 1.5 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * These classes are needed.
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndex',
    dirname(__FILE__) . '/AutoloaderIndex.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Index_NotFound',
    dirname(__FILE__) . '/exception/AutoloaderException_Index_NotFound.php'
);

/**
 * A dummy implementation without any persistent abilities
 *
 * There is no sense except testing in using this index.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Index
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        Autoloader::setIndex()
 * @see        Autoloader::getIndex()
 */
class AutoloaderIndex_Dummy extends AutoloaderIndex
{

    private
    /**
     * @var Array
     */
    $_index = array();

    /**
     * Returns the size of the index
     *
     * @return int
     * @see Countable
     */
    public function count()
    {
        return count($this->_index);
    }

    /**
     * Returns all paths in the index
     *
     * @throws AutoloaderException_Index
     * @return Array
     */
    public function getPaths()
    {
        return $this->_index;
    }

    /**
     * Returns the unfiltered path of the class definition
     *
     * @param String $class A class name
     *
     * @throws AutoloaderException_Index_NotFound
     * @return String
     */
    protected function getRawPath($class)
    {
        if (! $this->hasPath($class)) {
            throw new AutoloaderException_Index_NotFound($class);

        }
        return $this->_index[$class];
    }

    /**
     * Stores the filtered path for a class definition
     *
     * @param String $class A class name
     * @param String $path  The path to the class definition
     *
     * @return void
     */
    protected function setRawPath($class, $path)
    {
        $this->_index[$class] = $path;
    }

    /**
     * Removes A class from the index
     *
     * @param String $class A class name
     *
     * @return void
     */
    protected function unsetRawPath($class)
    {
        unset($this->_index[$class]);
    }

    /**
     * Returns true if the index contains the path to the class $class
     *
     * @param String $class A class name
     *
     * @return bool
     */
    public function hasPath($class)
    {
        return array_key_exists($class, $this->_index);
    }

    /**
     * Does nothing in this implementation
     *
     * The index is never stored persistently.
     *
     * @return void
     */
    public function delete()
    {

    }

    /**
     * Does nothing in this implementation
     * 
     * The index is never stored persistently.
     *
     * @return void
     */
    protected function saveRaw()
    {

    }

}