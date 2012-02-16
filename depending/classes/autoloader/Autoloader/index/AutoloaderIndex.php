<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the class AutoloaderIndex
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
 * @version    SVN: $Id: AutoloaderIndex.php,v 1.4 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * Stores the location of class defintions for speeding up recurring searches
 *
 * Searching a class definition in the filesystem takes a lot of time, as every
 * file is read. To avoid these long searches, a found class definition will be
 * stored in an index. The next search for an already found class definition
 * will take no time.
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
abstract class AutoloaderIndex implements Countable
{

    private
    /**
     * @var String Distinguishes different indexes
     */
    $_context = '',
    /**
     * @var Array
     */
    $_getFilters = array(),
    /**
     * @var Array
     */
    $_setFilters = array(),
    /**
     * @var int counts how often getPath() is called
     * @see getPath()
     */
    $_getPathCallCounter = 0,
    /**
     * @var bool
     */
    $_isChanged = false;

    protected
    /**
     * @var Autoloader
     */
    $autoloader;

    /**
     * Returns the unfiltered path to the class definition of $class
     *
     * @param String $class The class name
     *
     * @throws AutoloaderException_Index
     * @throws AutoloaderException_Index_NotFound the class is not in the index
     * @return String The absolute path of the found class $class
     * @see getPath()
     */
    abstract protected function getRawPath($class);

    /**
     * Returns true if the class $class is already stored in the index
     *
     * @param String $class The class name
     *
     * @throws AutoloaderException_Index
     * @return bool
     */
    abstract public function hasPath($class);

    /**
     * Returns all paths of the index
     *
     * The returned array has the class name as keys and the paths as values.
     *
     * @throws AutoloaderException_Index
     * @return Array() All paths in the index
     */
    abstract public function getPaths();

    /**
     * Deletes the index
     *
     * @throws AutoloaderException_Index
     * @return void
     */
    abstract public function delete();

    /**
     * Sets the path for the class $class to $path
     *
     * This must not yet be persistent to the index. The Destructor
     * will call save() to make it persistent.
     *
     * @param String $class The class name
     * @param String $path  The path
     *
     * @throws AutoloaderException_Index
     * @see save()
     * @see unsetRawPath()
     * @return void
     */
    abstract protected function setRawPath($class, $path);

    /**
     * Unsets the path for the class $class
     *
     * This must not yet be persistent to the index. The Destructor
     * will call save() to make it persistent.
     *
     * @param String $class The class name
     *
     * @throws AutoloaderException_Index
     * @see setRawPath()
     * @see save()
     * @return void
     */
    abstract protected function unsetRawPath($class);

    /**
     * Makes the changes to the index persistent
     *
     * The destructor is calling this method.
     *
     * @throws AutoloaderException_Index
     * @see save()
     * @return void
     */
    abstract protected function saveRaw();

    /**
     * Adds an AutoloaderIndexGetFilter instance to the index
     *
     * You can add a filter which modifies the path which is read
     * from the index. This could for example produce absolute paths from
     * relative paths.
     *
     * @param AutoloaderIndexGetFilter $getFilter An AutoloaderIndexGetFilter object
     *
     * @see addSetFilter()
     * @return void
     */
    public function addGetFilter(AutoloaderIndexGetFilter $getFilter)
    {
        $this->_getFilters[] = $getFilter;
    }

    /**
     * Adds an AutoloaderIndexSetFilter instance to the index
     *
     * You can add a filter which modifies the path which is stored
     * into the index. This could for example store relative paths instead
     * of absolute paths.
     *
     * @param AutoloaderIndexSetFilter $setFilter An AutoloaderIndexSetFilter object
     *
     * @see addGetFilter()
     * @return void
     */
    public function addSetFilter(AutoloaderIndexSetFilter $setFilter)
    {
        $this->_setFilters[] = $setFilter;
    }

    /**
     * Adds an AutoloaderIndexFilter instance to the index
     * 
     * These filters are used to modify the stored and read paths.
     *
     * @param AutoloaderIndexFilter $filter An AutoloaderIndexFilter filter
     *
     * @see addGetFilter()
     * @see addSetFilter()
     * @return void
     */
    public function addFilter(AutoloaderIndexFilter $filter)
    {
        $this->addSetFilter($filter);
        $this->addGetFilter($filter);
    }

    /**
     * Returns the path of a class definition
     *
     * All AutoloaderIndexGetFilter instances are applied on the returned path.
     *
     * If no path is stored in der index, an AutoloaderException_Index_NotFound
     * is thrown.
     *
     * @param String $class The class name
     *
     * @throws AutoloaderException_Index
     * @throws AutoloaderException_Index_NotFound the class is not in the index
     * @return String The absolute path of the found class $class
     * @see getRawPath()
     * @see addGetFilter()
     */
    final public function getPath($class)
    {
        $this->_getPathCallCounter++;
        $path = $this->getRawPath($class);
        foreach ($this->_getFilters as $filter) {
            $path = $filter->filterGetPath($path);

        }
        return $path;
    }

    /**
     * Returns how often class definitions were read from the index
     *
     * @return int A counter how often getPath() has been called
     * @see getPath()
     */
    public function getGetPathCallCounter()
    {
        return $this->_getPathCallCounter;
    }

    /**
     * Makes the changes to the index persistent
     *
     * The destructor is calling this method.
     *
     * @throws AutoloaderException_Index
     * @see setRawPath()
     * @see unsetRawPath()
     * @see __destruct()
     * @see saveRaw()
     * @return void
     */
    public function save()
    {
        if (! $this->_isChanged) {
            return;

        }
        $this->saveRaw();
        $this->_isChanged = false;
    }

    /**
     * Sets the Autoloader instance for this index
     *
     * The Autoloader calls this to set itself to this index.
     *
     * @param Autoloader $autoloader an Autoloader instance
     *
     * @see Autoloader::setIndex()
     * @see $autoloader
     * @return void
     */
    public function setAutoloader(Autoloader $autoloader)
    {
        $this->autoloader = $autoloader;
    }

    /**
     * Returns the autoloader
     *
     * @see Autoloader::setIndex()
     * @see setAutoloader()
     * @see $autoloader
     * @return Autoloader
     */
    public function getAutoloader()
    {
        return $this->autoloader;
    }

    /**
     * Make changes persistent
     *
     * @throws AutoloaderException_Index
     * @see save()
     */
    public function __destruct()
    {
        $this->save();
    }

    /**
     * Sets the path for the class $class to $path
     *
     * This must not yet be persistent to the index. The Destructor
     * will call save() to make it persistent.
     *
     * All AutoloaderIndexSetFilter are applied before saving.
     *
     * @param String $class The class name
     * @param String $path  The path to the class definition
     *
     * @throws AutoloaderException_Index
     * @see save()
     * @see __destruct()
     * @see setRawPath()
     * @see unsetPath()
     * @see addSetFilter()
     * @return void
     */
    final public function setPath($class, $path)
    {
        foreach ($this->_setFilters as $filter) {
            $path = $filter->filterSetPath($path);

        }
        $this->setRawPath($class, $path);
        $this->_isChanged = true;
    }

    /**
     * Unsets the path for the class
     *
     * This must not yet be persistent to the index. The Destructor
     * will call save() to make it persistent.
     *
     * @param String $class The class name
     *
     * @throws AutoloaderException_Index
     * @see unsetRawPath()
     * @see __destruct()
     * @see setPath()
     * @see save()
     * @return void
     */
    public function unsetPath($class)
    {
        $this->unsetRawPath($class);
        $this->_isChanged = true;
    }

    /**
     * Returns the Autoloader class path context
     *
     * A context distinguishes indexs. Only Autoloaders with an equal class
     * path work in the same context.
     *
     * If no context is given, the class path of the autoloader creates the context.
     *
     * @see setContext()
     * @see $_context
     * @see Autoloader::getPath()
     * @return String A context to distinguish different autoloaders
     */
    final protected function getContext()
    {
        return empty($this->_context)
            ? md5($this->autoloader->getPath())
            : $this->_context;
    }

    /**
     * Sets the context
     *
     * Setting the context is optional. The class path of the autoloader is already
     * a context. Setting the context to a known name is useful in a prebuild
     * index environment. You can build your index on a different system and use
     * the built index with the known context on any other system.
     *
     * @param String $context
     *
     * @see getContext()
     * @see $_context
     * @return void
     */
    final public function setContext($context)
    {
        $this->_context = $context;
    }

}