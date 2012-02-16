<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the test cases for implementations of AutoloaderIndexFilter
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
 * @version    SVN: $Id: TestIndexFilter.php,v 1.3 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The Autoloader is used for class loading.
 */
require_once dirname(__FILE__) . "/../Autoloader.php";

/**
 * AutoloaderIndexFilter_RelativePath test cases
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Test
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        AutoloaderIndexFilter
 * @see        AutoloaderIndexFilter_RelativePath
 */
class TestIndexFilter extends PHPUnit_Framework_TestCase
{

    /**
     * Asserts that AutoloaderIndexFilter_RelativePath::getBasePath() returns the
     * expected path
     *
     * @param String                             $expectedBasePath The expected path
     * @param AutoloaderIndexFilter_RelativePath $filter           A configured
     * filter
     *
     * @dataProvider provideTestRelativePathBasePath
     * @return void
     * @see AutoloaderIndexFilter_RelativePath::getBasePath()
     */
    public function testRelativePathBasePath(
        $expectedBasePath, AutoloaderIndexFilter_RelativePath $filter
    ) {
        $this->assertEquals(
            realpath($expectedBasePath),
            $filter->getBasePath()
        );
    }

    /**
     * Provides test cases for testRelativePathBasePath()
     *
     * A test case is the expected base path and a filter which would return
     * this base path.
     *
     * @return array
     * @see testRelativePathBasePath()
     */
    public function provideTestRelativePathBasePath()
    {
        return array(
            array(
                dirname(__FILE__) . '/../',
                new AutoloaderIndexFilter_RelativePath()
            ),
            array(
                dirname(__FILE__),
                new AutoloaderIndexFilter_RelativePath(dirname(__FILE__))
            ),
        );
    }

    /**
     * Creates an instance of AutoloaderIndexFilter_RelativePath with an non
     * existing path
     * 
     * This should raise an
     * AutoloaderException_Index_Filter_RelativePath_InvalidBasePath.
     *
     * @param String $basePath non existing path
     *
     * @dataProvider provideTestFailRelativePathBasePath
     * @return void
     */
    public function testFailRelativePathBasePath($basePath)
    {
        $this->setExpectedException(
            'AutoloaderException_Index_Filter_RelativePath_InvalidBasePath'
        );
        new AutoloaderIndexFilter_RelativePath($basePath);
    }

    /**
     * Provides non existing paths for testFailRelativePathBasePath()
     *
     * @return array non existing paths
     * @see testFailRelativePathBasePath()
     */
    public function provideTestFailRelativePathBasePath()
    {
        return array(
            array(
                dirname(__FILE__) . '/' . uniqid(),
            )
        );
    }

    /**
     * Asserts that AutoloaderIndexFilter_RelativePath::filterSetPath() converts an
     * absolute path into the expected relative path
     *
     * @param String $relativePath expected relative path
     * @param String $absolutePath an absolute path which will be converted
     *
     * @dataProvider provideTestRelativePath
     * @see AutoloaderIndexFilter_RelativePath::filterSetPath()
     * @return void
     */
    public function testSetRelativePath($relativePath, $absolutePath)
    {
        $filter = new AutoloaderIndexFilter_RelativePath();
        $filteredPath = $filter->filterSetPath($absolutePath);
        $this->assertEquals($relativePath, $filteredPath);
    }

    /**
     * Asserts that AutoloaderIndexFilter_RelativePath::filterGetPath() converts a
     * relative path into the expected absolute path
     *
     * @param String $relativePath a relative path which will be converted
     * @param String $absolutePath the expected absolute path
     *
     * @dataProvider provideTestRelativePath
     * @see AutoloaderIndexFilter_RelativePath::filterGetPath()
     * @return void
     */
    public function testGetRelativePath($relativePath, $absolutePath)
    {
        $filter = new AutoloaderIndexFilter_RelativePath();
        $filteredPath = $filter->filterGetPath($relativePath);

        $pathArray = explode(DIRECTORY_SEPARATOR, $filteredPath);
        while ($parent = array_search('..', $pathArray)) {
            unset($pathArray[$parent], $pathArray[$parent - 1]);
            $pathArray = array_values($pathArray);

        }
        $filteredPath = implode(DIRECTORY_SEPARATOR, $pathArray);

        $this->assertEquals($absolutePath, $filteredPath);
    }

    /**
     * Provides test cases for testSetRelativePath() and testGetRelativePath()
     *
     * A test case is a relative path and its absolute correspondent. The base path
     * for the relative paths is __DIR__ . '/..'.
     *
     * @see testSetRelativePath()
     * @see testGetRelativePath()
     * @return Array
     */
    public function provideTestRelativePath()
    {
        return array(
            array(
                '../../Foo',
                realpath(dirname(__FILE__) . "/../../../") . "/Foo"
            ),
            array(
                '../../Foo/Bar',
                realpath(dirname(__FILE__) . "/../../../") . "/Foo/Bar"
            ),
            array(
                '../Foo',
                realpath(dirname(__FILE__) . "/../../") . "/Foo"
            ),
            array(
                '../Foo/Bar',
                realpath(dirname(__FILE__) . "/../../") . "/Foo/Bar"
            ),
            array(
                'Foo',
                realpath(dirname(__FILE__) . "/..") . "/Foo"
            ),
            array(
                'Foo/Bar',
                realpath(dirname(__FILE__) . "/..") . "/Foo/Bar"
            ),
            array(
                'tests/Foo',
                dirname(__FILE__) . "/Foo"
            ),
            array(
                'tests/Foo/Bar',
                dirname(__FILE__) . "/Foo/Bar"
            )
        );
    }

}