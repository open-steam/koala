<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines AutoloaderTestHelper
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
 * @version    SVN: $Id: AutoloaderTestHelper.php,v 1.3 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * A helper for unit tests
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Test
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 */
class AutoloaderTestHelper
{

    const CLASS_DIRECTORY = "testClasses";

    private
    /**
     * @var array
     */
    $_generatedClassPaths = array(),
    /**
     * @var PHPUnit_Framework_TestCase
     */
    $_test;

    /**
     * Creates the directory where tests may create files
     *
     * @return void
     */
    static public function classConstructor()
    {
        if (! file_exists(self::getClassDirectory())) {
            mkdir(self::getClassDirectory());

        }
    }

    /**
     * Returns the path where tests may create temporary files
     *
     * If $subDirectory is given, $subdirectory will be appended to the path.
     *
     * @param String $subDirectory An optional subdirectory
     *
     * @return String
     */
    static public function getClassDirectory($subDirectory = null)
    {
        $classDirectory = dirname(__FILE__) . DIRECTORY_SEPARATOR
                        . '..' . DIRECTORY_SEPARATOR
                        . self::CLASS_DIRECTORY;

        if (! empty($subDirectory)) {
            $classDirectory .= DIRECTORY_SEPARATOR . $subDirectory;

        }
        return $classDirectory;
    }

    /**
     * Assigns $_test with an instance of PHPUnit_Framework_TestCase
     *
     * @param PHPUnit_Framework_TestCase $test The test
     *
     * @see $_test
     */
    public function __construct(PHPUnit_Framework_TestCase $test)
    {
        $this->_test = $test;
    }

    /**
     * Returns if this PHP supports namespaces
     *
     * Some tests might be skipped in an unsupported environment.
     *
     * @return bool
     */
    public function hasNamespaceSupport()
    {
        return version_compare(PHP_VERSION, "5.3", '>=');
    }

    /**
     * Tests if $class is loadable
     * 
     * If not it will call PHPUnit_Framework_TestCase::fail().
     *
     * @param String $class The class name
     *
     * @see PHPUnit_Framework_TestCase::fail()
     * @return void
     */
    public function assertLoadable($class)
    {
        try {
            new ReflectionClass($class);

        } catch (ReflectionException $e) {
            $this->_test->fail("class $class is not loadable.");

        }
    }

    /**
     * Tests if $class is not loadable
     *
     * If it is loadable it will call PHPUnit_Framework_TestCase::fail().
     *
     * @param String $class The class name
     *
     * @see PHPUnit_Framework_TestCase::fail()
     * @return void
     */
    public function assertNotLoadable($class)
    {
        try {
            new ReflectionClass($class);
            new $class();
            $this->_test->fail("class $class is loadable.");

        } catch (ReflectionException $e) {
            // expected

        }
    }

    /**
     * Creates a class definition in a namespace
     *
     * $name is only a prefix of the resulting class name. The class name
     * gets a random string appended. The resulting class name is returned.
     * 
     * The directory will always be a subdirectory of getClassDirectory().
     *
     * getGeneratedClassPath() will return the path of the generated class.
     *
     * @param String $namespace  The namespace of the class
     * @param String $name       The prefix of the class name
     * @param String $directory  The directory where the class will be created
     * @param String $definition The class definition
     *
     * @see makeClass()
     * @see getGeneratedClassPath()
     * @return String The name of the created class
     */
    public function makeClassInNamespace(
        $namespace,
        $name,
        $directory,
        $definition = "<?php namespace %namespace%; class %name%{}?>"
    ) {
        $definition = str_replace('%namespace%', $namespace, $definition);
        $name       = $this->makeClass($name, $directory, $definition);
        return "$namespace\\$name";
    }

    /**
     * Creates a class definition
     *
     * $name is only a prefix of the resulting class name. The class name
     * gets a random string appended. The resulting class name is returned.
     *
     * The directory will always be a subdirectory of getClassDirectory().
     *
     * getGeneratedClassPath() will return the path of the generated class.
     *
     * @param String $name       The prefix of the class name
     * @param String $directory  The directory where the class will be created
     * @param String $definition The class definition
     *
     * @see makeClassInNamespace()
     * @see getGeneratedClassPath()
     * @return String The name of the created class
     */
    public function makeClass(
        $name, $directory, $definition = "<?php class %name%{}?>"
    ) {
        $name     .= uniqid();
        $directory = self::getClassDirectory() . DIRECTORY_SEPARATOR . $directory;
        $path      = $directory . DIRECTORY_SEPARATOR . "$name.test.php";


        $normlizedName = $name;
        AbstractAutoloader::normalizeClass($normlizedName);
        $this->_generatedClassPaths[$normlizedName] = $path;

        if (file_exists($path)) {
            return $name;

        }

        if (! file_exists($directory)) {
            mkdir($directory, 0777, true);

        }
        $definition = str_replace("%name%", $name, $definition);
        file_put_contents($path, $definition);

        return $name;
    }

    /**
     * Returns the path to the class definition
     * 
     * The class definition must be created with makeClass()
     * or makeClassInNamespace().
     *
     * @param String $class The class name
     *
     * @see makeClass()
     * @see makeClassInNamespace()
     * @return String
     */
    public function getGeneratedClassPath($class)
    {
        $normlizedName = $class;
        AbstractAutoloader::normalizeClass($normlizedName);
        return $this->_generatedClassPaths[$normlizedName];
    }

    /**
     * Deletes a directory recursively by calling `rm -rf  $directory`
     *
     * if $isChroot is true (default) $directory will be a subdirectory under
     * getClassDirectory().
     *
     * @param String $directory The deleted directory
     * @param bool   $isChroot  true per default
     *
     * @return void
     */
    public static function deleteDirectory($directory, $isChroot = true)
    {
        if ($isChroot) {
            $directory
                = self::getClassDirectory() . DIRECTORY_SEPARATOR . $directory;

        }
        $directory = realpath($directory);
        if (! file_exists($directory)) {
            return;

        }
        system('rm -rf ' . $directory);
    }

}