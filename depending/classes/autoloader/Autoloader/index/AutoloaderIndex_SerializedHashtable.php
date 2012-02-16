<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Implements the class AutoloaderIndex_SerializedHashtable
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
 * @version    SVN: $Id: AutoloaderIndex_SerializedHashtable.php,v 1.4 2011/01/11 14:25:31 nicke Exp $
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
 * Implements AutoloaderIndex_File with a serialized hashtable
 *
 * This index is working in every PHP environment. It should be fast enough
 * for most applications. The index is a file in the temporary directory.
 * The content of this file is a serialized Hashtable.
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
class AutoloaderIndex_SerializedHashtable extends AutoloaderIndex_File
{
    
    /**
     * Reads the serialized array content and generates the index array
     *
     * @param String $data The serialized content
     *
     * @return Array
     * @throws AutoloaderException_Index
     * @see unserialize()
     */
    protected function buildIndex($data)
    {
        $index = unserialize($data);
        if (! is_array($index)) {
            $error = "Can not unserialize {$this->getIndexPath()}:"
                   . " $data";
            throw new AutoloaderException_Index($error);

        }
        return $index;
    }

    /**
     * Serializes the index array
     *
     * @param Array $index The index array
     *
     * @see serialize()
     * @return String
     */
    protected function serializeIndex(Array $index)
    {
        return serialize($index);
    }

}