<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Implements the class AutoloaderIndex_IniFile
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
 * @version    SVN: $Id: AutoloaderIndex_IniFile.php,v 1.5 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The parent class is needed.
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndex_File',
    dirname(__FILE__) . '/AutoloaderIndex_File.php'
);

/**
 * Implements AutoloaderIndex_File with a PHP ini file
 *
 * This index is working in every PHP environment. It should be fast enough
 * for most applications. The index is a file in the temporary directory.
 * The content of this file is a ini file.
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
 * @see        parse_ini_string()
 */
class AutoloaderIndex_IniFile extends AutoloaderIndex_File
{

    /**
     * Reads the content of the ini file and generates the index array
     *
     * @param String $data The content of a ini file
     *
     * @return Array
     * @throws AutoloaderException_Index
     */
    protected function buildIndex($data)
    {
        $index = parse_ini_string($data);
        if (! is_array($index)) {
            $error = "{$this->getIndexPath()} failed to generate the index:"
                   . " $data";
            throw new AutoloaderException_Index($error);

        }
        return $index;
    }

    /**
     * Transforms the index array into a ini string
     *
     * @param Array $index The index array
     *
     * @return String
     */
    protected function serializeIndex(Array $index)
    {
        $lines = array();
        foreach ($index as $class => $path) {
            $lines[] = "$class = $path";
            
        }
        return implode("\n", $lines);
    }

}