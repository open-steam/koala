<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the AutoloaderException_Include_FileNotExists
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
 * @subpackage Exception
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    SVN: $Id: AutoloaderException_Include_FileNotExists.php,v 1.4 2011/01/11 14:25:30 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The parent class must be loaded. As this exception might be raised in the
 * InternalAutoloader, it is loaded by require_once.
 */
require_once dirname(__FILE__) . '/AutoloaderException_Include.php';

/**
 * Raised if the required class definition does not exist
 *
 * This can happen if the Autoloader tries to load a class definition from
 * a stale index. Normally the Autoloader reacts on this exception to find
 * the new class definition.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Exception
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        AbstractAutoloader::loadClass()
 */
class AutoloaderException_Include_FileNotExists extends AutoloaderException_Include
{

}