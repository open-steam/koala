<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the test cases for the class InternalAutoloader
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
 * @version    SVN: $Id: TestInternalAutoloader.php,v 1.3 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The Autoloader is used for class loading.
 */
require_once dirname(__FILE__) . "/../Autoloader.php";

/**
 * InternalAutoloader test cases.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Test
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        InternalAutoloader
 */
class TestInternalAutoloader extends PHPUnit_Framework_TestCase
{

    /**
     * Asserts that the class $class is loadable after
     * registration of its class definition $path
     *
     * @param String $class The class which is autoloaded
     * @param String $path  The class defintion
     * 
     * @dataProvider provideTestAutoload
     * @return void
     * @see InternalAutoloader::registerClass()
     */
    public function testAutoload($class, $path)
    {
        $autoloaderTestHelper = new AutoloaderTestHelper($this);

        Autoloader::removeAll();

        $autoloaderTestHelper->assertNotLoadable($class);
        InternalAutoloader::getInstance()->registerClass($class, $path);
        $autoloaderTestHelper->assertLoadable($class);
    }

    /**
     * Asserts that InternalAutoloader::getInstance() returns
     * an instance of InternalAutoloader and that this instance is registered
     * in the autoloader stack
     *
     * @return void
     * @see InternalAutoloader::getInstance()
     */
    public function testGetInstance()
    {
        $this->assertTrue(
            InternalAutoloader::getInstance() instanceof InternalAutoloader
        );
        $this->assertTrue(
            InternalAutoloader::getInstance()->isRegistered()
        );
    }

    /**
     * Asserts that there exists only one registered instance of InternalAutoloader
     *
     * @return void
     */
    public function testSingleton()
    {
        $this->assertEquals(
            1,
            count(InternalAutoloader::getRegisteredAutoloaders())
        );
    }

    /**
     * Asserts that after calling InternalAutoloader::removeAll() no instance of
     * InternalAutoloader is registered
     *
     * @return void
     * @see InternalAutoloader::removeAll()
     */
    public function testRemoveAll()
    {
        InternalAutoloader::removeAll();
        $this->_assertRemoved();
    }

    /**
     * Asserts that after calling InternalAutoloader::getInstance()->remove() the
     * instance (which is the only existing one) is removed from the autoloader
     * stack
     *
     * @return void
     * @see InternalAutoloader::remove()
     */
    public function testRemove()
    {
        InternalAutoloader::getInstance()->remove();
        $this->_assertRemoved();
    }

    /**
     * Returns test cases for testAutoload()
     *
     * A test case is class name and a path to the definition of this
     * class name.
     *
     * @see testAutoload()
     * @return array
     */
    public function provideTestAutoload()
    {
        $autoloaderTestHelper = new AutoloaderTestHelper($this);
        $return               = array();

        $class    = $autoloaderTestHelper->makeClass(
            "ClassA",
            'testInternal'
        );
        $return[] = array(
            $class,
            $autoloaderTestHelper->getGeneratedClassPath($class)
        );
        $class    = $autoloaderTestHelper->makeClass(
            "ClassA2",
            'testInternal'
        );
        $return[] = array(
            $class,
            $autoloaderTestHelper->getGeneratedClassPath($class)
        );
        $class    = $autoloaderTestHelper->makeClass(
            "ClassB",
            'testInternal/sub'
        );
        $return[] = array(
            $class,
            $autoloaderTestHelper->getGeneratedClassPath($class)
        );

        return $return;
    }

    /**
     * Asserts that no instance of InternalAutoloader is registered
     *
     * It also asserts that registration would still work.
     *
     * @return void
     */
    private function _assertRemoved()
    {
        $this->assertFalse(
            InternalAutoloader::getInstance()->isRegistered()
        );
        $this->assertEquals(
            0,
            count(InternalAutoloader::getRegisteredAutoloaders())
        );

        InternalAutoloader::getInstance()->register();
        $this->assertTrue(
            InternalAutoloader::getInstance()->isRegistered()
        );
    }

}