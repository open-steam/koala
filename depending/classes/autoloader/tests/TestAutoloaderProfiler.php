<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the test cases for Autoloader_Profiler
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
 * @subpackage Test
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    SVN: $Id: TestAutoloaderProfiler.php,v 1.3 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The Autoloader is used for class loading.
 */
require_once dirname(__FILE__) . "/../Autoloader.php";

/**
 * Autoloader_Profiler is not not registered in the InternalAutoloader.
 */
require_once dirname(__FILE__) . "/classes/Autoloader_Profiler.php";

/**
 * Autoloader_Profiler test cases
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Test
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        Autoloader_Profiler
 */
class TestAutoloaderProfiler extends PHPUnit_Framework_TestCase
{

    private
    /**
     * @var AutoloaderTestHelper
     */
    $_autoloaderTestHelper;

    /**
     * Removes all Autoloaders from the stack and initializes
     * the AutoloaderTestHelper $_autoloaderTestHelper.
     *
     * @see $_autoloaderTestHelper
     * @return void
     */
    public function setUp()
    {
        $this->_autoloaderTestHelper = new AutoloaderTestHelper($this);
        Autoloader::removeAll();
    }

    /**
     * Asserts that in an environment with several registered instances of
     * Autoloader, every Autoloader uses its index first before they start iterating
     * through their class paths
     *
     * @return void
     */
    public function testUseIndexInMultiAutoLoaderEnvironment()
    {
        $classA = $this->_autoloaderTestHelper->makeClass('A', 'a');
        $classB = $this->_autoloaderTestHelper->makeClass('B', 'b');

        $alA = new Autoloader_Profiler(
            dirname($this->_autoloaderTestHelper->getGeneratedClassPath($classA)));
        $alA->register();
        $alA->addClassToIndex($classA);

        $alB = new Autoloader_Profiler(
            dirname($this->_autoloaderTestHelper->getGeneratedClassPath($classB)));
        $alB->register();
        $alB->addClassToIndex($classB);

        $this->_autoloaderTestHelper->assertLoadable($classA);
        $this->_autoloaderTestHelper->assertLoadable($classB);

        $this->assertEquals(array(), $alA->getSearchedClasses());
        $this->assertEquals(array(), $alB->getSearchedClasses());
    }

}