<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the AutoloaderIndexSetFilter
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
 * @version    SVN: $Id: AutoloaderIndexSetFilter.php,v 1.4 2011/01/11 14:25:32 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The AutoloaderIndexSetFilter interface
 *
 * When a path is stored in an AutoloaderIndex, this filter is applied on
 * that path.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Index
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        AutoloaderIndex::addSetFilter()
 * @see        AutoloaderIndex::setPath()
 * @see        AutoloaderIndexGetFilter
 */
interface AutoloaderIndexSetFilter
{

    /**
     * Returns a filtered path
     *
     * @param String $path A path
     *
     * @see AutoloaderIndex::setPath()
     * @return String
     */
    public function filterSetPath($path);

}