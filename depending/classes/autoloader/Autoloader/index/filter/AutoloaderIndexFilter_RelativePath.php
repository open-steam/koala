<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the AutoloaderIndexFilter_RelativePath
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
 * @version    SVN: $Id: AutoloaderIndexFilter_RelativePath.php,v 1.4 2011/01/11 14:25:32 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * These classes must be loaded.
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndexFilter',
    dirname(__FILE__) . '/AutoloaderIndexFilter.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Index_Filter_RelativePath_InvalidBasePath',
    dirname(__FILE__)
    . '/exception/AutoloaderException_Index_Filter_RelativePath_InvalidBasePath.php'
);

/**
 * Transforms absolute paths into relative paths and vice versa
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Index
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        AutoloaderIndex::addFilter()
 * @see        AutoloaderIndex::getPath()
 * @see        AutoloaderIndex::setPath()
 */
class AutoloaderIndexFilter_RelativePath implements AutoloaderIndexFilter
{

    private
    /**
     * @var String
     */
    $_basePath = '',
    /**
     * @var Array
     */
    $_basePathArray = array();

    /**
     * Constructed with a base path
     *
     * The base path is the base for the relative paths.
     *
     * @param String $basePath The base path
     *
     * @throws AutoloaderException_Index_Filter_RelativePath_InvalidBasePath
     */
    public function __construct($basePath = '')
    {
        if (empty($basePath)) {
            $root       = str_repeat(DIRECTORY_SEPARATOR . '..', 3);
            $basePath   = dirname(__FILE__) . $root;

        }
        $this->_basePath = realpath($basePath);
        if ($this->_basePath === false) {
            throw new AutoloaderException_Index_Filter_RelativePath_InvalidBasePath(
                $basePath
            );

        }
        $this->_basePathArray = explode(DIRECTORY_SEPARATOR, $this->_basePath);
    }

    /**
     * Returns the base path
     *
     * @return String
     */
    public function getBasePath()
    {
        return $this->_basePath;
    }

    /**
     * Transforms an absolute path to a relative path
     *
     * filterSetPath() is inverse to filterGetPath().
     *
     * @param String $path An absolute path $path
     * 
     * @see AutoloaderIndex::setPath()
     * @return String
     */
    public function filterSetPath($path)
    {
        $pathArray = explode(DIRECTORY_SEPARATOR, $path);
        foreach ($this->_basePathArray as $level => $directory) {
            if ($pathArray[$level] !== $directory) {
                $level--;
                break;

            }
            unset($pathArray[$level]);

        }
        $prefix = str_repeat(
            '..' . DIRECTORY_SEPARATOR,
            count($this->_basePathArray) - $level - 1
        );
        return $prefix . implode(DIRECTORY_SEPARATOR, $pathArray);
    }

    /**
     * Transforms a relative to an absolute path
     *
     * filterGetPath() is inverse to filterSetPath().
     *
     * @param String $path A relative path
     *
     * @see AutoloaderIndex::setPath()
     * @return String
     */
    public function filterGetPath($path)
    {
        return $this->_basePath . DIRECTORY_SEPARATOR . $path;
    }

}