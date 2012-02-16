<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Implements the class AutoloaderIndex_SerializedHashtable_GZ
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
 * @version    SVN: $Id: AutoloaderIndex_SerializedHashtable_GZ.php,v 1.5 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The parent class is needed.
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndex_SerializedHashtable',
    dirname(__FILE__) . '/AutoloaderIndex_SerializedHashtable.php'
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
 * Extends AutoloaderIndex_SerializedHashtable by compressing the
 * serialized hashtable
 *
 * This index works similar to AutoloaderIndex_SerializedHashtable. Its only
 * difference is that the index file is compressed. In environments with
 * a hugh count of class definitions a plain text index file would produce
 * too much IO costs.
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
 * @see        gzfile()
 */
class AutoloaderIndex_SerializedHashtable_GZ
    extends AutoloaderIndex_SerializedHashtable
{

    private
    /**
     * @var int The level of compression
     * @see gzopen()
     */
    $_compressionLevel = 1;

    /**
     * Reads the content of a file and decompresses it
     *
     * @param String $file the path to a file
     *
     * @return String
     * @throws AutoloaderException_Index_IO_FileNotExists
     * @throws AutoloaderException_Index_IO
     */
    protected function readFile($file)
    {
        $content = @gzfile($file);
        if (! $content) {
            if (! file_exists($file)) {
                throw new AutoloaderException_Index_IO_FileNotExists($file);

            }

            $error = error_get_last();

            if (! @file_get_contents($file)) {
                throw new AutoloaderException_Index_IO(
                    "Could not read '$file': $error[message]"
                );

            } else {
                throw new AutoloaderException_Index_IO(
                    "Could not decompress '$file': $error[message]"
                );

            }
        }
        return implode('', $content);
    }

    /**
     * Stores the string $data compressed into a file
     *
     * @param String $file The path to a file
     * @param String $data The uncompressed content
     *
     * @return int written Bytes
     * @see $_compressionLevel
     * @see gzopen()
     * @see gzwrite()
     * @throws AutoloaderException_Index_IO
     */
    protected function saveFile($file, $data)
    {
        $zp = @gzopen($file, "w{$this->_compressionLevel}");
        if (! $zp) {
            $error = error_get_last();
            throw new AutoloaderException_Index_IO(
                "Could not write to $file: $error[message]"
            );

        }
        chmod($file, 0777);
        $bytes = gzwrite($zp, $data);
        if (! @gzclose($zp)) {
            $error = error_get_last();
            throw new AutoloaderException_Index_IO(
                "Could not close $file: $error[message]"
            );

        }
        return $bytes;
    }

}