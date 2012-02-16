<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the class Autoloader
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
 * @version   SVN: $Id: Autoloader.php,v 1.6 2011/07/27 16:01:15 nicke Exp $
 * @link      http://php-autoloader.malkusch.de/en/
 */

/**
 * These classes are needed. As autoloading does not work here,
 * they have to be required traditionally.
 */
require_once
    dirname(__FILE__) . '/AbstractAutoloader.php';
require_once
    dirname(__FILE__) . '/InternalAutoloader.php';
require_once
    dirname(__FILE__) . '/exception/AutoloaderException.php';
require_once
    dirname(__FILE__) . '/exception/AutoloaderException_PathNotRegistered.php';

/**
 * An implementation for Autoloading classes in PHP
 *
 * This Autoloader implementation searches recursivly in
 * defined class paths for a class definition.
 *
 * Additionally it provides PHP with the class constructor
 * classConstructor(). If a class has a public and static method
 * classConstructor() the Autoloader will call this method.
 *
 * Actually there's no need to define a class path with
 * Autoloader->addPath() as the constructor uses the path
 * of the debug_backtrace().
 *
 * @category PHP
 * @package  Autoloader
 * @author   Markus Malkusch <markus@malkusch.de>
 * @license  http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version  Release: 1.11
 * @link     http://php-autoloader.malkusch.de/en/
 */
class Autoloader extends AbstractAutoloader
{

    private static
    /**
     * @var array
     */
    $_unregisteredNormalizedAutoloaders = array();

    protected
    /**
     * @var AutoloaderIndex
     */
    $index;

    private
    /**
     * @var int the time in seconds to find a class definition
     */
    $_searchTimeoutSeconds = 0,
    /**
     * @var AutoloaderFileIterator
     */
    $_fileIterator,
    /**
     * @var String
     */
    $_path = '',
    /**
     * @var AutoloaderFileParser
     */
    $_parser;

    /**
     * Sets a AutoloaderFileIterator
     *
     * This is not necessary to call, as the Autoloader initializes itself
     * with an AutoloaderFileIterator.
     * 
     * @param AutoloaderFileIterator $fileIterator The AutoloaderFileIterator
     *
     * @see $_fileIterator
     * @see getFileIterator()
     * @see _initMembers()
     * @return void
     */
    public function setFileIterator(AutoloaderFileIterator $fileIterator)
    {
        $this->_fileIterator = $fileIterator;
        $this->_fileIterator->setAutoloader($this);
    }

    /**
     * Returns the AutoloaderFileIterator
     *
     * @see setFileIterator()
     * @see _initMembers()
     * @see $_fileIterator
     * @return AutoloaderFileIterator
     */
    public function getFileIterator()
    {
        return $this->_fileIterator;
    }

    /**
     * Sets a AutoloaderFileParser
     *
     * This is not necessary to call, as the Autoloader initializes itself
     * with the best available parser.
     *
     * @param AutoloaderFileParser $parser An AutoloaderFileParser object
     *
     * @see $_parser
     * @see getParser()
     * @see _initMembers()
     * @return void
     */
    public function setParser(AutoloaderFileParser $parser)
    {
        $this->_parser = $parser;
    }

    /**
     * Returns the AutoloaderFileParser object
     *
     * @see $_parser
     * @see setParser()
     * @see _initMembers()
     * @return AutoloaderFileParser
     */
    public function getParser()
    {
        return $this->_parser;
    }

    /**
     * Sets the index
     *
     * You might change the index if your not happy with
     * the default index AutoloaderIndex_SerializedHashtable_GZ.
     *
     * @param AutoloaderIndex $index An AutoloaderIndex object
     *
     * @see $index
     * @see getIndex()
     * @see _initMembers()
     * @return void
     */
    public function setIndex(AutoloaderIndex $index)
    {
        $this->index = $index;
        $this->index->setAutoloader($this);
    }

    /**
     * Returns the AutoloaderIndex object
     *
     * @see $index
     * @see setIndex()
     * @see _initMembers()
     * @return AutoloaderIndex
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Returns a registered instance which does autoloding in the path $path
     *
     * If no path is given the path of the caller is taken.
     *
     * If no autoloader was found a AutoloaderException_PathNotRegistered
     * is thrown.
     *
     * @param String $path An optional path in the file system
     *
     * @return Autoloader
     * @see register()
     * @see setPath()
     * @see _getCallersPath()
     * @throws AutoloaderException_PathNotRegistered
     */
    static public function getRegisteredAutoloader($path = null)
    {
        $path = realpath(is_null($path) ? self::_getCallersPath() : $path);

        foreach (self::getRegisteredAutoloaders() as $autoloader) {
            if (strpos($path, $autoloader->getPath()) === 0) {
                return $autoloader;

            }
        }
        throw new AutoloaderException_PathNotRegistered($path);
    }

    /**
     * Returns all registered Autoloader instances which are doing their jobs
     *
     * @return Array
     * @see register()
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
     * Removes all instances of Autoloader from the stack
     *
     * @see remove()
     * @return void
     */
    static public function removeAll()
    {
        self::$_unregisteredNormalizedAutoloaders = array();
        //TODO use static:: in PHP 5.3 and remove the other implementations
        foreach (self::getRegisteredAutoloaders() as $autoloader) {
            $autoloader->remove();

        }
    }

    /**
     * Sets the class path
     *
     * if $path is null the class path is the path of the file which created this
     * instance.
     *
     * @param String $path The class path
     *
     * @see _getCallersPath()
     * @throws AutoloaderException_GuessPathFailed
     * @throws AutoloaderException_ClassPath_NotExists
     * @throws AutoloaderException_ClassPath
     */
    public function __construct($path = null)
    {
        $this->_setPath(is_null($path) ? self::_getCallersPath() : $path);
    }

    /**
     * Returns the path of the file which calls any method on this class
     *
     * @see __construct()
     * @see getRegisteredAutoloader()
     * @return String
     * @throws AutoloaderException_GuessPathFailed
     */
    static private function _getCallersPath()
    {
        $autoloaderPaths = array(
            realpath(dirname(__FILE__)),
            realpath(dirname(__FILE__) . '/..'),
        );
        foreach (debug_backtrace() as $trace) {
            $path = realpath(dirname($trace['file']));
            if (! in_array($path, $autoloaderPaths)) {
                return $path;

            }
        }
        throw new AutoloaderException_GuessPathFailed();
    }

    /**
     * Registers this Autoloader at the stack
     *
     * After registration, this Autoloader is autoloading class definitions.
     *
     * There is no need to configure this object. All missing
     * members are initialized before registration:
     * -The index would be an AutoloaderIndex_SerializedHashtable_GZ.
     * -The parser will be (if PHP has tokenizer support)
     *  an AutoloaderFileParser_Tokenizer.
     * -The class path was set by the constructor to the directory
     *  of the calling file.
     * -The timeout for finding a class is set to max_execution_time.
     * -The AutoloaderFileIterator searches for files in the filesystem.
     *
     * {@link spl_autoload_register()} disables __autoload(). This might be
     * unwanted, so register() also adds __autoload() to the stack.
     *
     * @throws AutoloaderException_GuessPathFailed
     * @see initMembers()
     * @see setIndex()
     * @see AutoloaderIndex_SerializedHashtable_GZ
     * @see setParser()
     * @see _initMembers()
     * @see AutoloaderFileParser_Tokenizer
     * @see spl_autoload_register()
     * @return void
     */
    public function register()
    {
        $this->_initMembers();

        parent::register();

        self::_normalizeSearchPaths();
    }

    /**
     * Builds an index in advance
     * 
     * You can use it to build your index before deployment in a productive
     * environment. The Autoloader does not have to be registered. All missing
     * members are initialized like in register().
     *
     * @throws AutoloaderException_IndexBuildCollision
     * @throws AutoloaderException_Index
     * @return void
     */
    public function buildIndex()
    {
        $this->_initMembers();

        // The index should be clean before building
        try {
            $this->index->delete();

        } catch (AutoloaderException_Index $e) {
            // The index might not exist.

        }

        // All found classes are saved in the index
        foreach ($this->_fileIterator as $file) {
            foreach ($this->_parser->getClassesInFile($file) as $class) {
                // A collision throws an AutoloaderException_IndexBuildCollision.
                if ($this->index->hasPath($class)) {
                    throw new AutoloaderException_IndexBuildCollision(
                        $class,
                        array($this->index->getPath($class), $file));

                }
                $this->index->setPath(strtolower($class), $file);

            }
        }
        $this->index->save();
    }

    /**
     * Initializes the members with a working configuration
     *
     * @see register()
     * @see buildIndex()
     * @return void
     */
    private function _initMembers()
    {
        // set the default index
        if (empty($this->index)) {
            $this->setIndex(new AutoloaderIndex_SerializedHashtable_GZ());

        }

        // set the default parser
        if (empty($this->_parser)) {
            $this->setParser(AutoloaderFileParser::getInstance());

        }

        // set the timeout for finding a class to max_execution_time
        if (empty($this->_searchTimeoutSeconds)) {
            $this->_searchTimeoutSeconds = ini_get('max_execution_time');

        }

        // set the AutoloaderFileIterator
        if (empty($this->_fileIterator)) {
            $this->setFileIterator(new AutoloaderFileIterator_PriorityList());

        }
    }

    /**
     * Is called during _normalizeSearchPaths()
     *
     * The autoloader is removed from the stack. It is added to
     * $_unregisteredNormalizedAutoloaders so it could later be readded by
     * remove() if the reason for this removal was removed.
     *
     * @see _normalizeSearchPaths()
     * @see remove()
     * @see $_unregisteredNormalizedAutoloaders
     * @return void
     */
    private function _removeByNormalization()
    {
        parent::remove();

        self::$_unregisteredNormalizedAutoloaders[$this->getPath()] = $this;
    }

    /**
     * Removes this Autoloader from the stack
     *
     * If this was resposnible for removing any other autoloaders during
     * _normalizeSearchPaths() the other autoloaders is readded again.
     *
     * @see _removeByNormalization()
     * @see _normalizeSearchPaths()
     * @see removeAll()
     * @see $_unregisteredNormalizedAutoloaders
     * @return void
     */
    public function remove()
    {
        parent::remove();

        $autoloaders = self::$_unregisteredNormalizedAutoloaders;
        self::$_unregisteredNormalizedAutoloaders = array();
        foreach ($autoloaders as $autoloader) {
            $autoloader->register();

        }
    }

    /**
     * Removes paths which are included in other search paths
     *
     * For example a /var/tmp would be removed if /var is already
     * a search path.
     *
     * @see remove()
     * @see register()
     * @return void
     */
    private static function _normalizeSearchPaths()
    {
        foreach (self::getRegisteredAutoloaders() as $removalCandidate) {
            foreach (self::getRegisteredAutoloaders() as $parentCandidate) {
                $strpos = strpos(
                    $removalCandidate->getPath(),
                    $parentCandidate->getPath()
                );
                $isIncluded
                    = $strpos === 0
                    && $removalCandidate !== $parentCandidate;
                if ($isIncluded) {
                    $removalCandidate->_removeByNormalization();

                }
            }
        }
    }

    /**
     * Defines a class paths in which the Autoloader will search for classes
     *
     * The constructor did this automatically.
     *
     * @param String $path A class path
     *
     * @see $_path
     * @throws AutoloaderException_ClassPath_NotExists
     * @throws AutoloaderException_ClassPath
     * @return void
     */
    private function _setPath($path)
    {
        $realpath = realpath($path);
        if (! $realpath) {
            if (! file_exists($path)) {
                throw new AutoloaderException_ClassPath_NotExists($path);

            }
            throw new AutoloaderException_ClassPath($path);

        }
        $this->_path = $realpath;
    }

    /**
     * Gets the search path of this autoloader
     *
     * @see $_path
     * @return String
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Returns true if this instance is the first registered instance of
     * this class
     * 
     * It might return TRUE if there are Autoloaders registered of different
     * classes.
     *
     * @return bool
     * @see _getAutoloaderPosition()
     * @throws AutoloaderException_PathNotRegistered
     */
    private function _isFirstRegisteredInstance()
    {
        return $this->_getAutoloaderPosition() == 0;
    }

    /**
     * Returns the position in the autoloader stack
     * 
     * The offset is 0. It considers only instances of this class.
     * That means a returned position of 0 doesn't implie it is the first Autoloader
     * in the autoloader stack. It's only the first instance of this Autoloader
     * class.
     *
     * @return int
     * @throws AutoloaderException_PathNotRegistered
     */
    private function _getAutoloaderPosition()
    {
        $position = array_search($this, self::getRegisteredAutoloaders());
        if ($position === false) {
            throw new AutoloaderException_PathNotRegistered($this->_path);

        }
        return $position;
    }

    /**
     * Returns the path of a class from the index
     *
     * This method acts actually statically. If this object is the first instance
     * in the autoloader stack it will iterate through all indexes and return
     * the path of any index.
     *
     * @param String $class The class name
     *
     * @return String
     * @throws AutoloaderException_Index_NotFound
     */
    private function _searchPathInIndexes($class)
    {
        // Only iterate once per __autoload call
        if (! $this->_isFirstRegisteredInstance()) {
            throw new AutoloaderException_Index_NotFound($class);

        }
        foreach (self::getRegisteredAutoloaders() as $autoloader) {
            try {
                return $autoloader->getIndex()->getPath($class);

            } catch (AutoloaderException_Index_NotFound $e) {
                continue;

            }
        }
        throw new AutoloaderException_Index_NotFound($class);
    }

    /**
     * Implements autoloading with an dynamic index
     *
     * @param String $class The normalized class name
     *
     * @throws AutoloaderException
     * @return void
     */
    protected function doAutoload($class)
    {
        if (empty($this->index)) {
            throw new AutoloaderException_Index_NotDefined();

        }

        try {
            $path = $this->_searchPathInIndexes($class);

        } catch (AutoloaderException_Index_NotFound $e) {
        	if (DEVELOPMENT_MODE) {
            	$path = $this->searchPath($class);
            	$this->index->setPath($class, $path);
        	} else {
        		return;
        	}
        }

        try {
            $this->loadClass($class, $path);

        } catch (AutoloaderException_Include $e) {
            $this->index->unsetPath($class);
            $path = $this->searchPath($class);
            $this->index->setPath($class, $path);
            $this->loadClass($class, $path);

        }
    }

    /**
     * Finds a class definition in the search paths
     *
     * This methods resets the max_execution_time to $searchTimeoutSeconds.
     *
     * @param String $class The class name
     *
     * @throws AutoloaderException
     * @throws AutoloaderException_SearchFailed
     * @see set_time_limit()
     * @return String
     */
    protected function searchPath($class)
    {
        set_time_limit($this->_searchTimeoutSeconds);

        $caughtExceptions = array();
        try {
            $isInstance
                = $this->_fileIterator
                instanceof AutoloaderFileIterator_PriorityList;
            if ($isInstance) {
                $this->_fileIterator->setClassname($class);

            }
            foreach ($this->_fileIterator as $file) {
                if ($this->_parser->isClassInFile($class, $file)) {
                    return $file;

                }
            }
        } catch (AutoloaderException $e) {
            /*
             * An exception shouldn't stop the file search.
             * But if no files were found it could be thrown.
             */
            $caughtExceptions[] = $e;

        }


        if (! empty($caughtExceptions)) {
            throw $caughtExceptions[0]; // just throw the first one

        } else {
            throw new AutoloaderException_SearchFailed($class);

        }
    }

}

/**
 * These classes are needed by the Autoloader itself.
 * They have to be registered statically.
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndex',
    dirname(__FILE__) . '/index/AutoloaderIndex.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_SearchFailed',
    dirname(__FILE__) . '/exception/AutoloaderException_SearchFailed.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_SearchFailed_EmptyClassPath',
    dirname(__FILE__)
    . '/exception/AutoloaderException_SearchFailed_EmptyClassPath.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Include',
    dirname(__FILE__) . '/exception/AutoloaderException_Include.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Include_FileNotExists',
    dirname(__FILE__) . '/exception/AutoloaderException_Include_FileNotExists.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Include_ClassNotDefined',
    dirname(__FILE__) . '/exception/AutoloaderException_Include_ClassNotDefined.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_ClassPath',
    dirname(__FILE__) . '/exception/AutoloaderException_ClassPath.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_ClassPath_NotExists',
    dirname(__FILE__) . '/exception/AutoloaderException_ClassPath_NotExists.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Index_NotDefined',
    dirname(__FILE__) . '/index/exception/AutoloaderException_Index_NotDefined.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_GuessPathFailed',
    dirname(__FILE__) . '/index/exception/AutoloaderException_GuessPathFailed.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndex_Dummy',
    dirname(__FILE__) . '/index/AutoloaderIndex_Dummy.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndex_PDO',
    dirname(__FILE__) . '/index/AutoloaderIndex_PDO.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndex_CSV',
    dirname(__FILE__) . '/index/AutoloaderIndex_CSV.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndex_IniFile',
    dirname(__FILE__) . '/index/AutoloaderIndex_IniFile.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndex_PHPArrayCode',
    dirname(__FILE__) . '/index/AutoloaderIndex_PHPArrayCode.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndex_SerializedHashtable',
    dirname(__FILE__) . '/index/AutoloaderIndex_SerializedHashtable.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndex_SerializedHashtable_GZ',
    dirname(__FILE__) . '/index/AutoloaderIndex_SerializedHashtable_GZ.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileParser',
    dirname(__FILE__) . '/parser/AutoloaderFileParser.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileIterator_Simple',
    dirname(__FILE__) . '/fileIterator/AutoloaderFileIterator_Simple.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileIterator_SimpleCached',
    dirname(__FILE__) . '/fileIterator/AutoloaderFileIterator_SimpleCached.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileIterator_PriorityList',
    dirname(__FILE__) . '/fileIterator/AutoloaderFileIterator_PriorityList.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_IndexBuildCollision',
    dirname(__FILE__) . '/exception/AutoloaderException_IndexBuildCollision.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndexFilter_RelativePath',
    dirname(__FILE__) . '/index/filter/AutoloaderIndexFilter_RelativePath.php'
);

