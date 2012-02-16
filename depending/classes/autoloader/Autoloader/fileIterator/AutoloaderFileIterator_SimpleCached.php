<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the AutoloaderFileIterator_SimpleCached
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
 * @version    SVN: $Id: AutoloaderFileIterator_SimpleCached.php,v 1.4 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * These classes must be loaded.
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileIterator',
    dirname(__FILE__).'/AutoloaderFileIterator.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileIterator_Simple',
    dirname(__FILE__).'/AutoloaderFileIterator_Simple.php'
);

/**
 * Extends AutoloaderFileIterator_Simple with caching
 *
 * It caches the result in an array.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage FileIterator
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        Autoloader::searchPath()
 * @see        AutoloaderFileIterator_Simple
 */
class AutoloaderFileIterator_SimpleCached extends AutoloaderFileIterator
{

    private
    /**
     * @var Array A cache of the found files
     */
    $_foundFiles = array(),
    /**
     * @var Iterator A AutoloaderFileIterator_Simple or an ArrayIterator
     */
    $_iterator;

    /**
     * Resets the cache
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

        $this->_foundFiles  = array();
        $this->_iterator    = new AutoloaderFileIterator_Simple();

        $this->_iterator->setSkipFilesize($this->skipFilesize);
        $this->_iterator->skipPatterns = $this->skipPatterns;
        if (! is_null($this->autoloader)) {
            $this->_iterator->setAutoloader($this->autoloader);

        }
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
     * Returns the key of the current Iterator object
     *
     * This key is not meant to be used or to be distinct.
     *
     * @see AutoloaderFileIterator_Simple::key()
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
     * Clears the $_foundFiles Array and calls rewind on the iterator
     *
     * If $_iterator is already an ArrayIterator it won't be discarded.
     *
     * @see Iterator::rewind()
     * @return void
     */
    public function rewind()
    {
        $this->_foundFiles = array();
        $this->_iterator->rewind();
    }

    /**
     * Puts file paths into the cache during iteration
     *
     * Found files are put in the Array $_foundFiles. If the
     * AutoloaderFileIterator_Simple becomes invalid the $_iterator becomes an
     * ArrayIterator with all found files of $_foundFiles.
     *
     * @see Iterator::valid()
     * @return bool
     */
    public function valid()
    {
        if (! $this->_iterator instanceof AutoloaderFileIterator_Simple) {
            return $this->_iterator->valid();

        }
        if ($this->_iterator->valid()) {
            $this->_foundFiles[$this->current()] = $this->current();
            return true;

        } else {
            $this->_iterator = new ArrayIterator($this->_foundFiles);
            return false;

        }
    }

}