<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the AutoloaderException_SearchFailed
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
 * @version    SVN: $Id: AutoloaderException_SearchFailed.php,v 1.4 2011/01/11 14:25:30 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The parent class must be loaded.
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException',
    dirname(__FILE__).'/AutoloaderException.php'
);

/**
 * Raised if searching for a class fails
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Exception
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        Autoloader::searchPath()
 */
class AutoloaderException_SearchFailed extends AutoloaderException
{

    private
    /**
     * @var String The class which couldn't be found
     */
    $_class = '';

    /**
     * Knows the class which couldn't be found
     *
     * @param String $class The class which couldn't be found
     */
    public function __construct($class)
    {
        parent::__construct("The class '$class' could not be found.");

        $this->_class = $class;
    }

    /**
     * Returns the class which wasn't found
     *
     * @return String
     */
    public function getClass()
    {
        return $this->_class;
    }

}