<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the test cases for TestAutoloadAPI
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
 * @version    SVN: $Id: TestAutoloadAPI.php,v 1.3 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * Needed classes
 */
require_once dirname(__FILE__) . "/../Autoloader.php";

/**
 * This file needs to be included traditionally as there are global
 * function definitions, which wouldn't be loaded by the autoloader.
 */
require_once dirname(__FILE__) . "/classes/AutoloaderCallbackDummy.php";

/**
 * Tests TestAutoloadAPI
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Test
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        AutoloadAPI
 */
class TestAutoloadAPI extends PHPUnit_Framework_TestCase
{

    /**
     * Asserts that getInstance() returns the correct class
     * 
     * @see AutoloadAPI::getInstance()
     * @return void
     */
    public function testGetInstance()
    {
        $expectedInstance
            = version_compare(PHP_VERSION, "5.2.11", '>=')
            ? 'AutoloadAPI'
            : 'AutoloadAPI_Old';

        $this->assertEquals(
            $expectedInstance,
            get_class(AutoloadAPI::getInstance())
        );
    }

    /**
     * Asserts that registerAutoloader registers callbacks at the stack and
     * can be removed
     *
     * @param AutoloadAPI $api      An AutoloadAPI object
     * @param Mixed       $callback The callback
     *
     * @dataProvider provideCallbacks
     * @return void
     */
    public function testRegisterAndRemove(AutoloadAPI $api, $callback)
    {
        $api->registerAutoloader($callback);
        $this->assertTrue(
            in_array($callback, $api->getRegisteredAutoloaders())
        );

        // The autoloader loads now any class
        $class  = uniqid('Test');
        $object = new $class();
        $this->assertEquals($class, $object->getName());

        $api->removeAutoloader($callback);
        $this->assertFalse(
            in_array($callback, $api->getRegisteredAutoloaders())
        );

        // There is no autoloader left which would load any class
        $class  = uniqid('Test2');
        $helper = new AutoloaderTestHelper($this);
        $helper->assertNotLoadable($class);
    }

    /**
     * Provides test cases for testRegisterAndRemove()
     *
     * A test case is an AutoloadAPI object and a callback
     *
     * @see testRegisterAndRemove()
     * @return Array
     */
    public function provideCallbacks()
    {
        return array(
            array($this->_getAutoloadAPI('AutoloadAPI'),     'functionCallback1'),
            array($this->_getAutoloadAPI('AutoloadAPI_Old'), 'functionCallback2'),

            array(
                $this->_getAutoloadAPI('AutoloadAPI'),
                array('AutoloaderCallbackDummy', 'staticAutoload1')
            ),

            array(
                $this->_getAutoloadAPI('AutoloadAPI_Old'),
                array('AutoloaderCallbackDummy', 'staticAutoload2')
            ),

            array(
                $this->_getAutoloadAPI('AutoloadAPI'),
                array(new AutoloaderCallbackDummy(), 'objectAutoload1')
            ),

            array(
                $this->_getAutoloadAPI('AutoloadAPI_Old'),
                array(new AutoloaderCallbackDummy(), 'objectAutoload2')
            ),
        );
    }

    /**
     * Skips to indicate that not all test cases can be run in this environment
     *
     * @return void
     * @see _getAutoloadAPI()
     */
    public function testComleteTestSupport()
    {
        if (version_compare(PHP_VERSION, "5.2.11", '<')) {
            $this->markTestSkipped();

        }
    }

    /**
     * Creates an AutoloadAPI instance
     *
     * @param String $classname Either AutoloadAPI or AutoloadAPI_Old
     *
     * @see AutoloadAPI_Constructable
     * @see AutoloadAPI_Old_Constructable
     * @return AutoloadAPI
     */
    private function _getAutoloadAPI($classname)
    {
        if (version_compare(PHP_VERSION, "5.2.11", '<')) {
            return AutoloadAPI::getInstance();

        }

        $classname .= "_Constructable";
        return new $classname();
    }

}