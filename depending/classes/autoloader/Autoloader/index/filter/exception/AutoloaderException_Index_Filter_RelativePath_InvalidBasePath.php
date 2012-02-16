<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the AutoloaderException_Index_Filter_RelativePath_InvalidBasePath
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
 * @version    SVN: $Id: AutoloaderException_Index_Filter_RelativePath_InvalidBasePath.php,v 1.4 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The parent class must be loaded.
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Index_Filter_RelativePath',
    dirname(__FILE__) . '/AutoloaderException_Index_Filter_RelativePath.php'
);

/**
 * Thrown if a AutoloaderIndexFilter_RelativePath is constructored with an invalid
 * base path
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Index
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        AutoloaderIndexFilter_RelativePath::__construct()
 * @see        AutoloaderIndex::getPath()
 * @see        AutoloaderIndex::setPath()
 */
class AutoloaderException_Index_Filter_RelativePath_InvalidBasePath
    extends AutoloaderException_Index_Filter_RelativePath
{

    /**
     * Constructed with an invalid path
     *
     * @param String $basePath An invalid base path
     */
    public function __construct($basePath)
    {
        parent::__construct("$basePath is invalid.");
    }

}