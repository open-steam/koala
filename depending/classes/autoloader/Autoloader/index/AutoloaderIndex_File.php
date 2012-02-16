<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the class AutoloaderIndex_File
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
 * @version    SVN: $Id: AutoloaderIndex_File.php,v 1.5 2011/01/11 14:25:31 nicke Exp $
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
    'AutoloaderException_Index',
    dirname(__FILE__) . '/exception/AutoloaderException_Index.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Index_NotFound',
    dirname(__FILE__) . '/exception/AutoloaderException_Index_NotFound.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Index_IO',
    dirname(__FILE__) . '/exception/AutoloaderException_Index_IO.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Index_IO_FileNotExists',
    dirname(__FILE__) . '/exception/AutoloaderException_Index_IO_FileNotExists.php'
);

/**
 * The index is a hashtable.
 *
 * This index is working in every PHP environment. It should be fast enough
 * for most applications. The index is a file in the temporary directory.
 * The content of this generates a Hashtable.
 *
 * This implementation is threadsafe.
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
 * @see        serialize()
 * @see        unserialize()
 */
abstract class AutoloaderIndex_File extends AutoloaderIndex
{

    private
    /**
     * @var String
     */
    $_path = '',
    /**
     * @var Array
     */
    $_index = null;

    /**
     * Generates the index array from the string $data
     *
     * The index array has the class names as keys and the path as values.
     *
     * @param String $data The content of the index file
     *
     * @return Array
     * @throws AutoloaderException_Index
     */
    abstract protected function buildIndex($data);

    /**
     * Creates the content of the index file from the index array
     *
     * The index array has the class names as keys and the path as values.
     *
     * @param array $index The index array
     *
     * @return String
     * @throws AutoloaderException_Index
     */
    abstract protected function serializeIndex(Array $index);

    /**
     * Sets the path to the index file
     *
     * Setting the index file path is optional. Per default
     * it will be a file in the temporary directory.
     *
     * @param String $path the path to the index file
     *
     * @see getIndexPath()
     * @return void
     */
    public function setIndexPath($path)
    {
        $this->_path  = $path;
        $this->_index = null;
    }

    /**
     * Gets the path of the index file
     *
     * @return String The path to the index file
     * @see setIndexPath()
     */
    public function getIndexPath()
    {
        if (empty($this->_path)) {
            $this->setIndexPath(
                sys_get_temp_dir()
                . DIRECTORY_SEPARATOR
                . get_class($this)
                . $this->getContext()
            );

        }
        return $this->_path;
    }

    /**
     * Deletes the index file
     *
     * @throws AutoloaderException_Index Deleting failed
     * @return void
     */
    public function delete()
    {
        if (! @unlink($this->getIndexPath())) {
            $error = error_get_last();
            throw new AutoloaderException_Index(
                "Could not delete {$this->getIndexPath()}: $error[message]"
            );

        }
        $this->_index = null;
    }

    /**
     * Asserts that the index is loaded
     *
     * if the index was not build it is builded by calling buildIndex().
     *
     * @throws AutoloaderException_Index
     * @see buildIndex()
     * @return void
     */
    private function _assertLoadedIndex()
    {
        if (is_array($this->_index)) {
            return;

        }

        try {
            $data = $this->readFile($this->getIndexPath());
            $this->_index = $this->buildIndex($data);

        } catch (AutoloaderException_Index_IO_FileNotExists $e) {
            /*
             * This could happen. The index is reseted to an empty index.
             */
            $this->_index = array();

        }
    }

    /**
     * Reads the content of the index file
     *
     * @param String $file the path of a file
     *
     * @return String
     * @throws AutoloaderException_Index_IO
     * @throws AutoloaderException_Index_IO_FileNotExists
     */
    protected function readFile($file)
    {
        $data = @file_get_contents($file);
        if ($data === false) {
            if (! file_exists($file)) {
                throw new AutoloaderException_Index_IO_FileNotExists($file);

            } else {
                $error = error_get_last();
                throw new AutoloaderException_Index_IO(
                    "Could not read '$file': $error[message]"
                );

            }
        }
        return $data;
    }

    /**
     * Stores data into a file
     *
     * @param String $file The path of the file
     * @param String $data The content
     *
     * @return int written Bytes
     * @throws AutoloaderException_Index_IO
     */
    protected function saveFile($file, $data)
    {
        return @file_put_contents($file, $data);
    }

    /**
     * Stores the content of the index array threadsafe in the index file
     *
     * @see saveFile()
     * @see serializeIndex()
     * @throws AutoloaderException_Index_IO
     * @since 1.1 saveRaw() is threadsafe.
     * @return void
     */
    protected function saveRaw()
    {
        $data = $this->serializeIndex($this->_index);

        /* Avoid race conditions, by writting into a temporary file
         * which will be moved atomically
         */
        $tmpFile = @tempnam(
            dirname($this->getIndexPath()),
            get_class($this) . "_tmp_"
        );
        if (! $tmpFile) {
            $error = error_get_last();
            throw new AutoloaderException_Index_IO(
                "Could not create temporary file in "
                . dirname($this->getIndexPath())
                . " for saving new index atomically: $error[message]"
            );

        }

        $writtenBytes = $this->saveFile($tmpFile, $data);
        if ($writtenBytes !== strlen($data)) {
            $error = error_get_last();
            throw new AutoloaderException_Index_IO(
                "Could not save new index to $tmpFile."
                . " $writtenBytes Bytes written: $error[message]"
            );

        }

        if (! @rename($tmpFile, $this->getIndexPath())) {
            $error = error_get_last();
            throw new AutoloaderException_Index_IO(
                "Could not move new index $tmpFile to {$this->getIndexPath()}:"
                . " $error[message]"
            );

        }
    }

    /**
     * Returns the size of the index
     *
     * @throws AutoloaderException_Index
     * @see Countable
     * @return int
     */
    public function count()
    {
        $this->_assertLoadedIndex();
        return count($this->_index);
    }

    /**
     * Returns the unfiltered path of the class definition for the class $class
     *
     * @param String $class The class name
     *
     * @throws AutoloaderException_Index
     * @throws AutoloaderException_Index_NotFound
     * @return String
     */
    protected function getRawPath($class)
    {
        $this->_assertLoadedIndex();
        if (! $this->hasPath($class)) {
            throw new AutoloaderException_Index_NotFound($class);

        }
        return $this->_index[$class];
    }

    /**
     * Returns all paths in the index
     *
     * @throws AutoloaderException_Index
     * @return Array
     */
    public function getPaths()
    {
        $this->_assertLoadedIndex();
        return $this->_index;
    }

    /**
     * Stores the filtered path of a class definition locally in the index array
     *
     * @param String $class A class name
     * @param String $path  The path of the class definition
     *
     * @throws AutoloaderException_Index
     * @return void
     */
    protected function setRawPath($class, $path)
    {
        $this->_assertLoadedIndex();
        $this->_index[$class] = $path;
    }

    /**
     * Removes the class definition for $class from the index array
     *
     * @param String $class A class name
     * 
     * @throws AutoloaderException_Index
     * @return void
     */
    protected function unsetRawPath($class)
    {
        $this->_assertLoadedIndex();
        unset($this->_index[$class]);
    }

    /**
     * Returns true if the class $class is contained in the index array
     *
     * @param String $class A class name
     *
     * @throws AutoloaderException_Index
     * @return bool
     */
    public function hasPath($class)
    {
        $this->_assertLoadedIndex();
        return array_key_exists($class, $this->_index);
    }

}