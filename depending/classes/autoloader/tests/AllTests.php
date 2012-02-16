<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the class AllTests
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
 * @version    SVN: $Id: AllTests.php,v 1.3 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * PHPUnit_Framework_TestSuite is included.
 */
require_once 'PHPUnit/Framework.php';

/**
 * The Autoloader is used for class loading.
 */
require_once dirname(__FILE__) . "/../Autoloader.php";

/**
 * Autoloader test suite
 *
 * The "Exception thrown without a stack frame in Unknown on line 0"
 * is a side effect of the tearDown() which deletes the indexes, before
 * every destructor was called.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Test
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        TestIndexFilter
 * @see        TestAutoloader
 * @see        TestAutoloaderProfiler
 * @see        TestIndex
 * @see        TestParser
 * @see        TestInternalAutoloader
 * @see        TestFileIterator
 * @see        TestOldPHPAPI
 * @see        TestAutoloadAPI
 */
class AllTests extends PHPUnit_Framework_TestSuite
{

    /**
     * Returns a list of test cases to be tested
     *
     * @return AutoloaderSuite
     */
    public static function suite()
    {
        $suite = new self();
 
        $suite->addTestSuite("TestAutoloadAPI");
        $suite->addTestSuite("TestOldPHPAPI");
        $suite->addTestSuite("TestIndexFilter");
        $suite->addTestSuite("TestAutoloader");
        $suite->addTestSuite("TestAutoloaderProfiler");
        $suite->addTestSuite("TestIndex");
        $suite->addTestSuite("TestParser");
        $suite->addTestSuite("TestInternalAutoloader");
        $suite->addTestSuite("TestFileIterator");
 
        return $suite;
    }

    /**
     * Deletes all temporary files
     *
     * @return void
     */
    public function tearDown()
    {
        AutoloaderTestHelper::deleteDirectory('.');
        AutoloaderTestHelper::deleteDirectory(TestIndex::getIndexDirectory(), false);
    }
    
}
