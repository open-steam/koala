<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the AutoloaderFileParser
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
 * @subpackage Parser
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    SVN: $Id: AutoloaderFileParser.php,v 1.4 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * These classes must be loaded.
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Parser_IO',
    dirname(__FILE__) . '/exception/AutoloaderException_Parser_IO.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileParser_Tokenizer',
    dirname(__FILE__) . '/AutoloaderFileParser_Tokenizer.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileParser_RegExp',
    dirname(__FILE__) . '/AutoloaderFileParser_RegExp.php'
);

/**
 * An abstract parser for class definitions
 *
 * An implementation of this class should be able to parse a file and
 * find a class definition.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Parser
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        Autoloader::searchPath()
 */
abstract class AutoloaderFileParser
{

    /**
     * Returns a list of classes, which are defined in $source
     *
     * @param String $source code which is parsed for class definitions
     *
     * @throws AutoloaderException_Parser
     * @see getClassesInFile()
     * @return Array
     */
    abstract public function getClassesInSource($source);

    /**
     * Returns true if the implementation is supported in your environment
     *
     * @return bool
     */
    static public function isSupported()
    {
        return false;
    }

    /**
     * Returns an implementaion of AutoloaderFileParser
     *
     * If AutoloaderFileParser_Tokenizer is supported it is returned.
     * Else AutoloaderFileParser_RegExp will be returned.
     *
     * @see AutoloaderFileParser_Tokenizer
     * @see AutoloaderFileParser_RegExp
     * @return AutoloaderFileParser
     */
    static public function getInstance()
    {
        if (AutoloaderFileParser_Tokenizer::isSupported()) {
            return new AutoloaderFileParser_Tokenizer();

        } else {
            return new AutoloaderFileParser_RegExp();

        }
    }

    /**
     * Returns true if $source defines the class $class
     *
     * @param String $class  A class name
     * @param String $source The source as a string. This is the content of a file.
     *
     * @throws AutoloaderException_Parser
     * @see isClassInFile()
     * @return bool
     */
    public function isClassInSource($class, $source)
    {
        $normalizedClass    = $class;
        $classes            = $this->getClassesInSource($source);

        $this->_normalizeClass($normalizedClass);
        array_walk($classes, array($this, '_normalizeClass'));
        return in_array($normalizedClass, $classes);
    }

    /**
     * Returns true if $file defines the class $class
     *
     * @param String $class A class name
     * @param String $file  A file which might contain the class definitions
     *
     * @throws AutoloaderException_Parser_IO
     * @throws AutoloaderException_Parser
     * @see isClassInSource()
     * @return bool
     */
    public function isClassInFile($class, $file)
    {
        return $this->isClassInSource($class, $this->_getSource($file));
    }

    /**
     * Returns a list of classes which is defined in $file
     *
     * @param String $file A file which might contain class definitions
     *
     * @throws AutoloaderException_Parser_IO
     * @throws AutoloaderException_Parser
     * @see getClassesInSource()
     * @see _getSource()
     * @return Array
     */
    public function getClassesInFile($file)
    {
        return $this->getClassesInSource($this->_getSource($file));
    }

    /**
     * Normalizes a class name by reference
     *
     * Normalization is implemented with strtolower($class).
     *
     * @param String &$class A reference to the class name
     * @param bool   $index  unused array index of array_walk()
     *
     * @return void
     */
    private function _normalizeClass(&$class, $index = false)
    {
        $class = strtolower($class);
    }

    /**
     * Returns the content of $file
     *
     * This method is a wrapper for file_get_contents(). Errors are handled by
     * exceptions.
     *
     * @param String $file A file
     *
     * @throws AutoloaderException_Parser_IO
     * @throws AutoloaderException_Parser
     * @return String
     */
    private function _getSource($file)
    {
        $source = @file_get_contents($file);
        if ($source === false) {
            $error = error_get_last();
            throw new AutoloaderException_Parser_IO(
                "Could not read $file while searching for classes: $error[message]"
            );

        }
        return $source;
    }

}