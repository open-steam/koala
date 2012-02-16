<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the AutoloaderException_IndexBuildCollision
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
 * @version    SVN: $Id: AutoloaderException_IndexBuildCollision.php,v 1.4 2011/01/11 14:25:30 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The parent class must be loaded.
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException',
    dirname(__FILE__) . '/AutoloaderException.php'
);

/**
 * Occurs during a collision while building the index
 *
 * A collision happens if a class definition is not unique in a class path.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Exception
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        Autoloader::buildIndex()
 */
class AutoloaderException_IndexBuildCollision extends AutoloaderException
{

    private
    /**
     * @var Array
     */
    $_paths = array(),
    /**
     * @var String
     */
    $_class = '';

    /**
     * Knows the ambiguous class and its definitions
     *
     * @param String $class The ambiguous class name
     * @param Array  $paths The paths for the found class definitions
     */
    public function __construct($class, array $paths)
    {
        parent::__construct(
            "class $class was defined in several files:" . implode(', ', $paths)
        );

        $this->_class = $class;
        $this->_paths = $paths;
    }

    /**
     * Returns the ambiguous class name which caused this exception
     *
     * @return String
     */
    public function getClass()
    {
        return $this->_class;
    }

    /**
     * Returns a list of files which provide class definitions to the ambiguous
     * class
     *
     * @return Array
     */
    public function getPaths()
    {
        return $this->_paths;
    }

}