<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the AutoloaderFileIterator_PriorityList
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
 * @subpackage FileIterator
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    SVN: $Id: AutoloaderFileIterator_PriorityList.php,v 1.5 2011/01/11 19:42:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * These classes must be loaded.
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileIterator',
    dirname(__FILE__) . '/AutoloaderFileIterator.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileIterator_Simple',
    dirname(__FILE__) . '/AutoloaderFileIterator_Simple.php'
);

/**
 * Searches all files and returns them in a priority list
 *
 * The AutoloaderFileIterator_PriorityList searches all files in advance and
 * orders them. It may not be practicable on a huge file base.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage FileIterator
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        Autoloader::searchPath()
 */
class AutoloaderFileIterator_PriorityList extends AutoloaderFileIterator
{

    private
    /**
     * @var Array
     */
    $_preferedFiles = array(),
    /**
     * @var Array
     */
    $_unpreferedFiles = array(),
    /**
     * @var String
     */
    $_classname = '',
    /**
     * @var Array
     */
    $_preferedPatterns = array('~\.(php|inc)$~i'),
    /**
     * @var ArrayIterator
     */
    $_iterator;

    /**
     * Sets the class name for the prefered file names
     *
     * Iteration tries to return an ordered list to
     * have potential class definition candidates first.
     *
     * @param String $classname The class name which is searched
     *
     * @return void
     */
    public function setClassname($classname)
    {
        $this->_classname = strtolower($classname);
    }

    /**
     * Adds a pattern for prefered files
     *
     * Files which match agaings $pattern are prefered
     * during iteration.
     *
     * @param String $pattern a RegExp
     *
     * @return void
     */
    public function addPreferedPattern($pattern)
    {
        $this->_preferedPatterns[] = $pattern;
        $this->reset();
    }

    /**
     * Resets the cache of this object
     *
     * As this implementation uses a cache, any configuration change will discard
     * the cache.
     *
     * @see AutoloaderFileIterator::reset()
     * @return void
     */
    protected function reset()
    {
        parent::reset();

        unset($this->_preferedFiles);
        unset($this->_unpreferedFiles);
        unset($this->_iterator);
    }

    /**
     * Returns the path of the current file
     *
     * @see Iterator::current()
     * @return String
     */
    public function current ()
    {
        return $this->_iterator->current();
    }

    /**
     * Returns the key of the current Iterator object.
     *
     * This key is not meant to be used or to be distinct.
     *
     * @see Iterator::key()
     * @return String
     */
    public function key()
    {
        return $this->_iterator->key();
    }

    /**
     * Calls next() on the iterator
     *
     * @see Iterator::next()
     * @return void
     */
    public function next()
    {
        $this->_iterator->next();
    }

    /**
     * Does the ordering and the actual iteration
     *
     * After calling rewind() iteration from outside is done on the ordered
     * array.
     *
     * @see _initFileArrays()
     * @see Iterator::rewind()
     * @return void
     */
    public function rewind()
    {
        $this->_initFileArrays();

        // order by Levenshtein distance to $classname
        $levArray = array();
        foreach ($this->_preferedFiles as $file) {
            $levArray[] = levenshtein(
                strtolower(basename($file)),
                $this->_classname
            );

        }
        array_multisort($levArray, $this->_preferedFiles);


        // merge ordered and unordered files
        $files = array_merge($this->_preferedFiles, $this->_unpreferedFiles);

        $this->_iterator = new ArrayIterator($files);
    }

    /**
     * Returns valid() on the iterator
     *
     * Iteration was already done in rewind().
     *
     * @see rewind()
     * @see Iterator::valid()
     * @return bool
     */
    public function valid()
    {
        return ! is_null($this->_iterator) && $this->_iterator->valid();
    }

    /**
     * Iterates through the class path with an AutoloaderFileIterator_Simple object.
     * The found paths are stored in the arrays $_preferedFiles and
     * $_unpreferedFiles.
     *
     * @see AutoloaderFileIterator_Simple
     * @return Array
     */
    private function _initFileArrays()
    {
        if (! empty($this->_preferedFiles) || ! empty($this->_unpreferedFiles)) {
            return;

        }
        $simpleIterator = new AutoloaderFileIterator_Simple();
        $simpleIterator->setAutoloader($this->autoloader);
        $simpleIterator->skipFilesize = $this->skipFilesize;
        $simpleIterator->skipDirPatterns = $this->skipDirPatterns;
        $simpleIterator->skipFilePatterns = $this->skipFilePatterns;
        $simpleIterator->onlyDirPattern = $this->onlyDirPattern;
        $simpleIterator->onlyFilePattern = $this->onlyFilePattern;

        $this->_preferedFiles   = array();
        $this->_unpreferedFiles = array();
        foreach ($simpleIterator as $file) {
            foreach ($this->_preferedPatterns as $pattern) {
                if (preg_match($pattern, $file)) {
                    $this->_preferedFiles[] = $file;
                    continue 2;

                }
            }
            $this->_unpreferedFiles[] = $file;

        }
    }

}