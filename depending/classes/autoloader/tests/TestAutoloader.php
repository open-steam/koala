<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the test cases for Autoloader
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
 * @version    SVN: $Id: TestAutoloader.php,v 1.3 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The Autoloader is used for class loading.
 */
require_once dirname(__FILE__) . "/../Autoloader.php";

/**
 * Autoloader test cases
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Test
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        Autoloader
 */
class TestAutoloader extends PHPUnit_Framework_TestCase
{

    static public
    /**
     * @var String Used for class constructor tests
     */
    $testClassConstructorState = '';

    private
    /**
     * @var AutoloaderTestHelper
     */
    $_autoloaderTestHelper;

    /**
     * Tests moving of class definitions
     * 
     * If an Autoloader found a class, the path for the class definition is fetched
     * from the index. If the class definition moves to another path, the new
     * path should be found and stored in the index.
     *
     * @return void
     */
    public function testMoveClassdefinition()
    {
        $class = $this->_autoloaderTestHelper->makeClass('test', 'moveTest');
        $index = Autoloader::getRegisteredAutoloader()->getIndex();

        AbstractAutoloader::normalizeClass($class);

        $index->setPath($class, uniqid('/dev/null/'));

        $this->_autoloaderTestHelper->assertLoadable($class);
        $this->assertEquals(
            realpath($this->_autoloaderTestHelper->getGeneratedClassPath($class)),
            realpath($index->getPath($class))
        );
    }

    /**
     * Tests renaming of class definitions which stay in a file
     *
     * If an Autoloader found a class, the path for the class definition is fetched
     * from the index. The content of this file might change and not define this
     * class anymore.
     *
     * @return void
     */
    public function testRenameClass()
    {
        $index    = Autoloader::getRegisteredAutoloader()->getIndex();
        $oldClass = uniqid("testclass");
        $newClass = $this->_autoloaderTestHelper->makeClass(
            'newClass', 'testRename'
        );

        AbstractAutoloader::normalizeClass($oldClass);
        AbstractAutoloader::normalizeClass($newClass);

        // The index still thinks that the file defines $oldClass
        $index->setPath(
            $oldClass,
            $this->_autoloaderTestHelper->getGeneratedClassPath($newClass)
        );

        $this->_autoloaderTestHelper->assertNotLoadable($oldClass);
        $this->assertFalse($index->hasPath($oldClass));
    }

    /**
     * Tests switching class definition files
     *
     * Classes may switch their files. The index should also switch its content.
     *
     * @return void
     */
    public function testSwitchClasses()
    {
        $index  = Autoloader::getRegisteredAutoloader()->getIndex();
        $classA = $this->_autoloaderTestHelper->makeClass(
            'classA', 'testSwitch'
        );
        $classB = $this->_autoloaderTestHelper->makeClass(
            'classB', 'testSwitch'
        );

        AbstractAutoloader::normalizeClass($classA);
        AbstractAutoloader::normalizeClass($classB);

        /*
         * The index still thinks that the file for classA has class B and
         * vice versa.
         */
        $index->setPath(
            $classA,
            $this->_autoloaderTestHelper->getGeneratedClassPath($classB)
        );
        $index->setPath(
            $classB,
            $this->_autoloaderTestHelper->getGeneratedClassPath($classA)
        );

        // After loading classA the index should know the path to class A
        $this->_autoloaderTestHelper->assertLoadable($classA);
        $this->assertEquals(
            realpath($this->_autoloaderTestHelper->getGeneratedClassPath($classA)),
            realpath($index->getPath($classA))
        );
        // ClassB is loaded, as it was included with the wrong path for classA.
    }

    /**
     * Tests removing of class definitions
     *
     * If an Autoloader found a class, the path for the class definition is fetched
     * from the index. If the class definition is removed, the autoloader should
     * fail.
     *
     * @return void
     */
    public function testRemoveClassdefinition()
    {
        $class = uniqid("testclass");
        $index = Autoloader::getRegisteredAutoloader()->getIndex();

        AbstractAutoloader::normalizeClass($class);

        $index->setPath($class, uniqid('/dev/null/'));

        $this->_autoloaderTestHelper->assertNotLoadable($class);
        $this->assertFalse($index->hasPath($class));
    }

    /**
     * Tests the deprecated class constructor __static()
     *
     * The autoloader loads the class $class and the test expects
     * from the autoloader, that it sets the value of $testClassConstructorState
     * to $expectedState. Additionally an E_USER_DEPRECATED warning is expected.
     *
     * @param String $expectedState The class constructor sets this state
     * @param String $class         A class with a deprecated class constructor
     *
     * @dataProvider provideTestDeprecatedClassConstructor
     * @see $testClassConstructorState
     * @return void
     */
    public function testDeprecatedClassConstructor($expectedState, $class)
    {
        self::$testClassConstructorState = '';
        @$this->_autoloaderTestHelper->assertLoadable($class);
        $lastError = error_get_last();

        $this->assertEquals($expectedState, self::$testClassConstructorState);

        if ($expectedState != '') {
            $this->assertEquals(E_USER_DEPRECATED, $lastError['type']);

        }
    }

    /**
     * Provide test cases for testDeprecatedClassConstructor()
     *
     * A test case is an expected state and a not loaded class with a
     * deprecated class constructor. The class constructor should set the
     * value of $testClassConstructorState to the expected state.
     *
     * @see testDeprecatedClassConstructor()
     * @see $testClassConstructorState
     * @return Array
     */
    public function provideTestDeprecatedClassConstructor()
    {
        $this->_autoloaderTestHelper = new AutoloaderTestHelper($this);

        $cases = array(
            array(
                'da',
                $this->_autoloaderTestHelper->makeClass(
                    'test',
                    '',
                    '<?php class %name% {

                        static public function __static() {
                            TestAutoloader::$testClassConstructorState = "da";
                        }
                    } ?>'
                )
            ),

            array(
                '',
                $this->_autoloaderTestHelper->makeClass(
                    'test',
                    '',
                    '<?php class %name% {

                        public function __static() {
                            TestAutoloader::$testClassConstructorState = "db";
                        }
                    } ?>'
                )
            )
        );

        $helper = new AutoloaderTestHelper($this);
        if ($helper->hasNamespaceSupport()) {
            $cases[] = array(
                'dc',
                $this->_autoloaderTestHelper->makeClassInNamespace(
                    'de\malkusch\autoloader\test',
                    'test',
                    '',
                    '<?php
                        namespace %namespace%;

                        class %name% {

                            static public function __static() {
                                \TestAutoloader::$testClassConstructorState = "dc";
                            }
                        }
                    ?>'
                )
            );
            
        }
        return $cases;
    }

    /**
     * Raises a SkippedTestError to indicate that not all test cases can be
     * run in this environment.
     *
     * @return void
     */
    public function testNamespaceSupport()
    {
        $helper = new AutoloaderTestHelper($this);
        if (! $helper->hasNamespaceSupport()) {
            $this->markTestSkipped(
                "Namespace testcases are skipt on PHP < 5.3 systems."
            );

        }
    }

    /**
     * Asserts that after loading the a new class its class constructor is called
     *
     * The class constructor is expected to set the public atribute
     * $testClassConstructorState to an expected value.
     *
     * @param String $expectedState The expected value of $testClassConstructorState
     * @param String $class         A new class
     *
     * @dataProvider provideTestClassConstructor
     * @see $testClassConstructorState
     * @return void
     */
    public function testClassConstructor($expectedState, $class)
    {
        self::$testClassConstructorState = '';
        $this->_autoloaderTestHelper->assertLoadable($class);
        $this->assertEquals($expectedState, self::$testClassConstructorState);
    }

    /**
     * Provides test cases for testClassConstructor()
     *
     * A test case consists of an expected value for $testClassConstructorState and
     * a new class name. The class constructor of a test case should change the
     * value of $testClassConstructorState to the expected value. If nothing will
     * happen '' is expected.
     *
     * @see $testClassConstructorState
     * @see testClassConstructor()
     * @return Array
     */
    public function provideTestClassConstructor()
    {
        $this->_autoloaderTestHelper = new AutoloaderTestHelper($this);

        $cases = array(
            array(
                'a',
                $this->_autoloaderTestHelper->makeClass(
                    'test',
                    '',
                    '<?php class %name% {

                        static public function classConstructor() {
                            TestAutoloader::$testClassConstructorState = "a";
                        }
                    } ?>'
                )
            ),

            array(
                '',
                $this->_autoloaderTestHelper->makeClass(
                    'test',
                    '',
                    '<?php class %name% {

                        public function classConstructor() {
                            TestAutoloader::$testClassConstructorState = "b";
                        }
                    } ?>'
                )
            )
        );

        $helper = new AutoloaderTestHelper($this);
        if ($helper->hasNamespaceSupport()) {
            $cases[] = array(
                'c',
                $this->_autoloaderTestHelper->makeClassInNamespace(
                    'de\malkusch\autoloader\test',
                    'test',
                    '',
                    '<?php
                        namespace %namespace%;

                        class %name% {

                            static public function classConstructor() {
                                \TestAutoloader::$testClassConstructorState = "c";
                            }
                        }
                    ?>'
                )
            );

        }
        return $cases;
    }

    /**
     * Asserts that Autoloader::buildIndex() stores all class definitions
     * in its index
     *
     * @param Autoloader $autoloader    The tested Autoloader instance
     * @param Array      $expectedPaths A list of all class definitions
     *
     * @dataProvider provideTestBuildIndex
     * @see Autoloader::buildIndex()
     * @return void
     */
    public function testBuildIndex(Autoloader $autoloader, Array $expectedPaths)
    {
        $autoloader->buildIndex();
        $foundPaths = $autoloader->getIndex()->getPaths();
        ksort($foundPaths);
        ksort($expectedPaths);

        $this->assertEquals($expectedPaths, $foundPaths);
    }

    /**
     * Provides test cases for testBuildIndex()
     *
     * A test case is a Autoloader for a certain class path and a list of all
     * class definitions in that class path.
     *
     * @see testBuildIndex()
     * @return Array
     */
    public function provideTestBuildIndex()
    {
        $cases      = array();
        $testHelper = new AutoloaderTestHelper($this);


        $testHelper->deleteDirectory('testBuildIndex/');
        $classes = array(
            $testHelper->makeClass('Test', 'testBuildIndex/'),
            $testHelper->makeClass('Test', 'testBuildIndex/'),
            $testHelper->makeClass('Test', 'testBuildIndex/A'),
            $testHelper->makeClass('Test', 'testBuildIndex/A'),
            $testHelper->makeClass('Test', 'testBuildIndex/A'),
            $testHelper->makeClass('Test', 'testBuildIndex/A/B'),
            $testHelper->makeClass('Test', 'testBuildIndex/A/B'),
            $testHelper->makeClass('Test', 'testBuildIndex/A/C'),
            $testHelper->makeClass('Test', 'testBuildIndex/A/C'),
            $testHelper->makeClass('Test', 'testBuildIndex/D'),
            $testHelper->makeClass('Test', 'testBuildIndex/D/E'),
        );
        $cases[] = array(
            new Autoloader($testHelper->getClassDirectory('testBuildIndex')),
            $this->_getPaths($classes, $testHelper));


        $testHelper->deleteDirectory('testBuildIndex2/');
        $classes = array(
            $testHelper->makeClass('Test', 'testBuildIndex2/'),
        );
        $cases[] = array(
            new Autoloader($testHelper->getClassDirectory('testBuildIndex2')),
            $this->_getPaths($classes, $testHelper));


        $testHelper->deleteDirectory('testBuildIndex3/');
        $classes = array(
            $testHelper->makeClass('Test', 'testBuildIndex3/'),
            $testHelper->makeClass('Test', 'testBuildIndex3/'),
        );
        $cases[] = array(
            new Autoloader($testHelper->getClassDirectory('testBuildIndex3')),
            $this->_getPaths($classes, $testHelper));


        $testHelper->deleteDirectory('testBuildIndex4/');
        $classes = array(
            $testHelper->makeClass('Test', 'testBuildIndex4/A/B'),
            $testHelper->makeClass('Test', 'testBuildIndex4/B/C'),
        );
        $cases[] = array(
            new Autoloader($testHelper->getClassDirectory('testBuildIndex4')),
            $this->_getPaths($classes, $testHelper));


        return $cases;
    }

    /**
     * Returns a list of paths for the list of classes
     *
     * @param array                $testClasses A list of classes
     * @param AutoloaderTestHelper $testHelper  A helper which generated the classes
     *
     * @see provideTestBuildIndex()
     * @return Array
     */
    private function _getPaths(Array $testClasses, AutoloaderTestHelper $testHelper)
    {
        $paths = array();
        foreach ($testClasses as $class) {
            $paths[$class] = realpath($testHelper->getGeneratedClassPath($class));

        }
        return $paths;
    }

    /**
     * Building an index should fail if class definitions are not unique.
     *
     * @param Autoloader $autoloader An Autoloader which should fail
     *
     * @dataProvider provideTestFailBuildIndex
     * @expectedException AutoloaderException_IndexBuildCollision
     * @see Autoloader::buildIndex()
     * @return void
     */
    public function testFailBuildIndex(Autoloader $autoloader)
    {
        $autoloader->buildIndex();
    }

    /**
     * Provides test cases for testFailBuildIndex()
     *
     * A test case is an Autoloader instance. The class path of that instance
     * should contain multiple class definitions for the same class name.
     *
     * @see testFailBuildIndex()
     * @return Array
     */
    public function provideTestFailBuildIndex()
    {
        $cases = array();

        $definition = "<?php class XXXTest".uniqid()." {} ?>";

        $testHelper = new AutoloaderTestHelper($this);
        $testHelper->makeClass('Test', 'testFailBuildIndexA/');
        $testHelper->makeClass('Test', 'testFailBuildIndexA/', $definition);
        $testHelper->makeClass('Test', 'testFailBuildIndexA/', $definition);
        $cases[] = array(new Autoloader(
            $testHelper->getClassDirectory('testFailBuildIndexA')));

        $testHelper = new AutoloaderTestHelper($this);
        $testHelper->makeClass('Test', 'testFailBuildIndexB/A', $definition);
        $testHelper->makeClass('Test', 'testFailBuildIndexB/A');
        $testHelper->makeClass('Test', 'testFailBuildIndexB/B', $definition);
        $testHelper->makeClass('Test', 'testFailBuildIndexB/B');
        $cases[] = array(new Autoloader(
            $testHelper->getClassDirectory('testFailBuildIndexB')));

        return $cases;
    }

    /**
     * Tests several packages of this autoloader
     *
     * This might happen if you use libraries which come with this
     * autoloader in their own class path. The Autoloader should
     * define its classes only once no matter how often it is required
     * and where it has class definitions.
     *
     * @return void
     */
    public function testRequireOnceMultipleAutoloaders()
    {
        if (! $this->_isSameFilesystem(dirname(__FILE__), sys_get_temp_dir())) {

        }

        $copyPath   = sys_get_temp_dir() . '/' . __FUNCTION__;
        $sourcePath = dirname(__FILE__) . "/..";

        /**
         * If the tmp directory is in the same filesystem this test runs
         * faster by creating hard links.
         */
        $linkOption
            = $this->_isSameFilesystem($sourcePath, sys_get_temp_dir())
            ? '--link'
            : '';

        `cp -r $linkOption $sourcePath $copyPath`;

        include dirname(__FILE__) . "/../Autoloader.php";
        include "$copyPath/Autoloader.php";

        `rm -rf $copyPath`;
    }

    /**
     * Returns true if both paths are in the same filesystem.
     *
     * @param String $path1 A path
     * @param String $path2 A path
     *
     * @return bool
     */
    private function _isSameFilesystem($path1, $path2)
    {
        $stat1 = stat($path1);
        $stat2 = stat($path2);

        return $stat1[0] === $stat2[0];
    }

    /**
     * Checks reregistering
     *
     * This Test checks if a normalized Autolader will registered
     * again, after removing its parent Autoloader.
     *
     * @return void
     */
    public function testReregisteringAfterRemoval()
    {
        Autoloader::removeAll();


        $classA = $this->_autoloaderTestHelper->makeClass(
            "A", "testReregisteringAfterRemoval"
        );
        $classB = $this->_autoloaderTestHelper->makeClass(
            "B", "testReregisteringAfterRemoval/B"
        );


        $autoloaderB = new Autoloader(
            AutoloaderTestHelper::getClassDirectory()
            . "/testReregisteringAfterRemoval/B"
        );
        $autoloaderB->register();


        $this->assertTrue($autoloaderB->isRegistered());

        $autoloaderA = new Autoloader(
            AutoloaderTestHelper::getClassDirectory()
            . "/testReregisteringAfterRemoval"
        );
        $autoloaderA->register();


        $this->assertTrue($autoloaderA->isRegistered());
        $this->assertFalse($autoloaderB->isRegistered());

        $autoloaderA->remove();

        $this->assertFalse($autoloaderA->isRegistered());
        $this->assertTrue($autoloaderB->isRegistered());

        $this->_autoloaderTestHelper->assertNotLoadable($classA);
        $this->_autoloaderTestHelper->assertLoadable($classB);

        Autoloader::removeAll();
    }

    /**
     * Tests normalization
     *
     * An autoloader will unregister itself automatically if another autoloader
     * would include it. On the other hand, if including autoloader is unregistered
     * all automatically unregistered autoloaders should be registered again.
     *
     * @see Autoloader::register()
     * @see Autoloader::remove()
     * @see Autoloader::_normalizeSearchPaths()
     * @see Autoloader::_removeByNormalization()
     * @return void
     */
    public function testNormalizedClassPaths()
    {
        $autoloader = Autoloader::getRegisteredAutoloader();
        Autoloader::removeAll();

        $classA = $this->_autoloaderTestHelper->makeClass(
            "A", "testNormalizedClassPaths"
        );
        $classB = $this->_autoloaderTestHelper->makeClass(
            "B", "testNormalizedClassPaths/B"
        );

        $autoloaderA = new Autoloader(
            AutoloaderTestHelper::getClassDirectory() . "/testNormalizedClassPaths"
        );
        $autoloaderA->register();

        $autoloaderB = new Autoloader(
            AutoloaderTestHelper::getClassDirectory()
            . "/testNormalizedClassPaths/B"
        );
        $autoloaderB->register();

        $this->assertTrue($autoloaderA->isRegistered());
        $this->assertFalse($autoloaderB->isRegistered());
        $this->_autoloaderTestHelper->assertLoadable($classA);
        $this->_autoloaderTestHelper->assertLoadable($classB);

        Autoloader::removeAll();


        $classA = $this->_autoloaderTestHelper->makeClass(
            "A", "testNormalizedClassPaths"
        );
        $classB = $this->_autoloaderTestHelper->makeClass(
            "B", "testNormalizedClassPaths/B"
        );


        $autoloaderB = new Autoloader(
            AutoloaderTestHelper::getClassDirectory()
            . "/testNormalizedClassPaths/B"
        );
        $autoloaderB->register();

        $autoloaderA = new Autoloader(
            AutoloaderTestHelper::getClassDirectory()
            . "/testNormalizedClassPaths"
        );
        $autoloaderA->register();

        $this->_autoloaderTestHelper->assertLoadable($classA);
        $this->_autoloaderTestHelper->assertLoadable($classB);
        $this->assertTrue($autoloaderA->isRegistered());
        $this->assertFalse($autoloaderB->isRegistered());

        Autoloader::removeAll();

        $autoloader->register();
    }

    /**
     * Asserts that an autoloader has the expected class path
     *
     * @param Autoloader $autoloader   An instance of Autoloader
     * @param String     $expectedPath The expected class path
     *
     * @dataProvider provideTestClassPath
     * @see Autoloader::getPath()
     * @return void
     */
    public function testClassPath(Autoloader $autoloader, $expectedPath)
    {
        $this->assertEquals(
            realpath($expectedPath), realpath($autoloader->getPath())
        );
    }

    /**
     * Provides test cases for testClassPath()
     *
     * A test case is an instance of Autoloader and its expected class path.
     *
     * @return Array
     * @see testClassPath()
     */
    public function provideTestClassPath()
    {
        $autoPath = realpath(dirname(__FILE__));

        $defaultLoader = new Autoloader();

        $outsidePath = AutoloaderTestHelper::getClassDirectory();
        $loaderWithOutsideOfThisPath = new Autoloader($outsidePath);

        return array(
            array($defaultLoader,                     $autoPath),
            array($loaderWithOutsideOfThisPath,       $outsidePath),
        );
    }

    /**
     * Tests class_exists() and interface_exists()
     *
     * @param String $method   interface_exists or class_exists
     * @param bool   $expected expected result
     * @param String $class    Class name
     *
     * @see class_exists()
     * @see interface_exists()
     * @dataProvider provideTestClassExists
     * @return void
     */
    public function testClassExists($method, $expected, $class)
    {
        $this->assertEquals(
            $expected,
            $method($class)
        );
    }

    /**
     * Provides test cases for testClassExists()
     *
     * @see testClassExists()
     * @return Array
     */
    public function provideTestClassExists()
    {
        $this->_autoloaderTestHelper = new AutoloaderTestHelper($this);
        $cases = array();

        // Non existing cases
        $cases[] = array(
            "class_exists", false, "class_" . uniqid()
        );
        $cases[] = array(
            "interface_exists", false, "interface_" . uniqid()
        );

        // Existing cases
        $cases[] = array(
            "class_exists",
            true,
            $this->_autoloaderTestHelper->makeClass("Class", "")
        );
        $cases[] = array(
            "interface_exists",
            true,
            $this->_autoloaderTestHelper->makeClass(
                "Interface",
                "",
                "<?php interface %name% { } ?>"
            )
        );

        return $cases;
    }

    /**
     * Asserts that $class is loadable
     *
     * @param String $class A loadable class name
     *
     * @dataProvider provideClassNames
     * @return void
     */
    public function testLoadClass($class)
    {
        $this->_autoloaderTestHelper->assertLoadable($class);
    }

    /**
     * Provides testLoadClass() with loadable class names
     *
     * @see testLoadClass()
     * @return Array
     */
    public function provideClassNames()
    {
        $this->_autoloaderTestHelper = new AutoloaderTestHelper($this);

        $classes = array();
        $classes[] = $this->_autoloaderTestHelper->makeClass("TestA", "");
        $classes[] = $this->_autoloaderTestHelper->makeClass("TestB", "");
        $classes[] = $this->_autoloaderTestHelper->makeClass("TestC1", "c");
        $classes[] = $this->_autoloaderTestHelper->makeClass("TestC2", "c");
        $classes[] = $this->_autoloaderTestHelper->makeClass("TestD", "d");
        $classes[] = $this->_autoloaderTestHelper->makeClass("TestE", "e");
        $classes[] = $this->_autoloaderTestHelper->makeClass("TestF1", "e/f");
        $classes[] = $this->_autoloaderTestHelper->makeClass("TestF2", "e/f");

        $classes[] = $this->_autoloaderTestHelper->makeClass(
            "TestInterface", "g", "<?php interface %name%{}?>"
        );
        $classes[] = $this->_autoloaderTestHelper->makeClass(
            "TestAbstract", "g", "<?php abstract class %name%{}?>"
        );
        $classes[] = $this->_autoloaderTestHelper->makeClass(
            "TestG1", "g", "<?php\nclass %name% {\n}?>"
        );
        $classes[] = $this->_autoloaderTestHelper->makeClass(
            "TestG2", "g", "<?php\n class %name% {\n}?>"
        );
        $classes[] = $this->_autoloaderTestHelper->makeClass(
            "TestG3", "g", "<?php\nclass %name%\n {\n}?>"
        );
        $classes[] = $this->_autoloaderTestHelper->makeClass(
            "TestG4", "g", "<?php\nclass %name% \n {\n}?>"
        );
        $classes[] = $this->_autoloaderTestHelper->makeClass(
            "TestG5", "g", "<?php\nClass %name% \n {\n}?>"
        );
        $classes[] = $this->_autoloaderTestHelper->makeClass(
            "TestG6", "g", "<?php\nclass %name% \n {\n}?>"
        );

        $helper = new AutoloaderTestHelper($this);
        if ($helper->hasNamespaceSupport()) {
            $classes[] = $this->_autoloaderTestHelper->makeClassInNamespace(
                "a", "Test", ""
            );
            $classes[] = $this->_autoloaderTestHelper->makeClassInNamespace(
                "a\b", "Test", ""
            );
            $classes[] = $this->_autoloaderTestHelper->makeClassInNamespace(
                "a\b", "Test", ""
            );
            $classes[] = $this->_autoloaderTestHelper->makeClassInNamespace(
                "a\b\c", "Test", ""
            );

        }

        $return = array();
        foreach ($classes as $class) {
            $return[] = array($class);

        }
        return $return;
    }

    /**
     * Asserts that an undefined class is not loadable
     *
     * @return void
     */
    public function testFailLoadClass()
    {
        $this->_autoloaderTestHelper->assertNotLoadable("ClassDoesNotExist");
    }

    /**
     * Asserts that getRegisteredAutoloader() is working
     *
     * @see Autoloader::getRegisteredAutoloader()
     * @return void
     */
    public function testGetRegisteredAutoloader()
    {
        $autoloader = Autoloader::getRegisteredAutoloader();
        $autoloader->remove();

        $autoloaders = array();

        $path = AutoloaderTestHelper::getClassDirectory()
            . "/testGetRegisteredAutoloaderA";
        @mkdir($path);
        @mkdir($path."/sub");
        $tmpAutoloader = new Autoloader($path);
        $tmpAutoloader->register();
        $autoloaders[] = $tmpAutoloader;

        $path = AutoloaderTestHelper::getClassDirectory()
            . "/testGetRegisteredAutoloaderB";
        @mkdir($path);
        @mkdir($path."/sub");
        $tmpAutoloader2 = new Autoloader($path);
        $tmpAutoloader2->register();
        $autoloaders[] = $tmpAutoloader2;

        foreach ($tmpAutoloader2 as $autoloader) {
            Autoloader::getRegisteredAutoloader($autoloader->getPath());
            Autoloader::getRegisteredAutoloader($autoloader->getPath()."/sub");

        }

        $tmpAutoloader->remove();
        $tmpAutoloader2->remove();

        $autoloader->register();
    }

    /**
     * Asserts that calling Autoloader::getRegisteredAutoloader() for a not
     * registered path will fail
     *
     * @param String $path A path wich is not registered
     *
     * @expectedException AutoloaderException_PathNotRegistered
     * @dataProvider provideTestGetRegisteredAutoloaderFailure
     * @see Autoloader::getRegisteredAutoloader()
     * @return void
     */
    public function testGetRegisteredAutoloaderFailure($path)
    {
        Autoloader::getRegisteredAutoloader($path);
    }

    /**
     * Provides testGetRegisteredAutoloaderFailure() with paths where no autoloader
     * is registered
     *
     * @see testGetRegisteredAutoloaderFailure
     * @return Array
     */
    public function provideTestGetRegisteredAutoloaderFailure()
    {
        return array(array(sys_get_temp_dir()));
    }

    /**
     * Tests Autoloader::getRegisteredAutoloader failure
     *
     * @see Autoloader::getRegisteredAutoloader()
     * @return void
     */
    public function testGetDefaultRegisteredAutoloaderFailure()
    {
        $autoloader = Autoloader::getRegisteredAutoloader();
        $autoloader->remove();
        $path = realpath(dirname(__FILE__));

        try {

            Autoloader::getRegisteredAutoloader();
            $this->fail("did not expect an Autoloader for $path.");

        } catch (AutoloaderException_PathNotRegistered $e) {
            $this->assertEquals($path, $e->getPath());

        }
        $autoloader->register();
    }

    /**
     * Asserts that an Autoloader can unregister itself from the stack
     *
     * @see Autoloader::remove()
     * @return void
     */
    public function testUnregisterAutoloader()
    {
        $class = $this->_autoloaderTestHelper->makeClass(
            "TestUnregisterAutoloader", "testUnregisterAutoloader"
        );

        $autoloader = Autoloader::getRegisteredAutoloader();
        $autoloader->remove();
        $this->_autoloaderTestHelper->assertNotLoadable($class);

        $autoloader->register();
        $this->_autoloaderTestHelper->assertLoadable($class);
    }

    /**
     * Tests if Autoloaders with disjunct class paths can be registered and can find
     * their classes
     *
     * @return void
     */
    public function testDifferentClassPaths()
    {
        $pathA = "testDifferentClassPathsA";
        $pathB = "testDifferentClassPathsB";

        $classA = $this->_autoloaderTestHelper->makeClass("A", $pathA);
        $classB = $this->_autoloaderTestHelper->makeClass("B", $pathB);

        $defaultAutoloader = Autoloader::getRegisteredAutoloader();
        $defaultAutoloader->remove();

        $tempLoaderA = new Autoloader(
            AutoloaderTestHelper::getClassDirectory() . "/" . $pathA
        );
        $tempLoaderB = new Autoloader(
            AutoloaderTestHelper::getClassDirectory() . "/" . $pathB
        );

        $this->_autoloaderTestHelper->assertNotLoadable($classA);
        $this->_autoloaderTestHelper->assertNotLoadable($classB);

        $tempLoaderA->register();
        $tempLoaderB->register();
        $this->_autoloaderTestHelper->assertLoadable($classA);
        $this->_autoloaderTestHelper->assertLoadable($classB);

        $tempLoaderA->remove();
        $tempLoaderB->remove();
        $defaultAutoloader->register();
    }

    /**
     * Asserts that Autoloader::getRegisteredAutoloaders() returns all registered
     * instances of Autoloaders
     *
     * @see Autoloader::getRegisteredAutoloaders()
     * @return void
     */
    public function testGetRegisteredAutoloaders()
    {
        $autoloaders = array();
        $autoloaders[] = Autoloader::getRegisteredAutoloader();

        $newAutoloader = new Autoloader(sys_get_temp_dir());
        $newAutoloader->register();
        $autoloaders[] = $newAutoloader;

        foreach ($autoloaders as $expectedAutoloader) {
            foreach (Autoloader::getRegisteredAutoloaders() as $autoloader) {
                if ($autoloader === $expectedAutoloader) {
                    continue 2;

                }
            }
            $this->fail("Autoloader wasn't registered.");
        }
        $newAutoloader->remove();
    }

    /**
     * Registers in each test a new Autoloader
     *
     * @return void
     */
    public function setUp()
    {
        $autoloader = new Autoloader();
        $autoloader->register();

        $this->_autoloaderTestHelper = new AutoloaderTestHelper($this);
    }

    /**
     * Leaves no Autoloader behind
     *
     * @return void
     */
    public function tearDown()
    {
        Autoloader::removeAll();
    }

    /**
     * Asserts that Autoloader::removeAll() removes all instances of Autoloader from
     * the stack
     * 
     * @see Autoloader::removeAll()
     * @return void
     */
    public function testRemoveAllAutoloaders()
    {
        $registeredAutoloaders = Autoloader::getRegisteredAutoloaders();

        $autoloader = new Autoloader();
        $autoloader->register();

        $this->assertEquals(
            count($registeredAutoloaders),
            count(Autoloader::getRegisteredAutoloaders())
        );

        Autoloader::removeAll();

        $this->assertEquals(0, count(Autoloader::getRegisteredAutoloaders()));

        $autoloader = new Autoloader();
        $autoloader->register();

        $this->assertEquals(1, count(Autoloader::getRegisteredAutoloaders()));

        $autoloader = new Autoloader(sys_get_temp_dir());
        $autoloader->register();

        $this->assertEquals(2, count(Autoloader::getRegisteredAutoloaders()));

        Autoloader::removeAll();
        foreach ($registeredAutoloaders as $autoloader) {
            $autoloader->register();

        }
    }

    /**
     * Asserts that including the file Autoloader.php will register each time an
     * instance of Autoloader with the correct class path
     * 
     * @see Autoloader.php
     * @return void
     */
    public function testSeveralRequiredAutoloaders()
    {
        $autoloaders = Autoloader::getRegisteredAutoloaders();
        Autoloader::removeAll();

        $autoloaderPath = dirname(__FILE__) . "/../Autoloader.php";

        $classA   = $this->_autoloaderTestHelper->makeClass("A", "a");
        $classA2  = $this->_autoloaderTestHelper->makeClass("A2", "a");
        $requireA = $this->_autoloaderTestHelper->makeClass(
            "requireA", "a", "<?php require '$autoloaderPath' ?>"
        );

        $classB   = $this->_autoloaderTestHelper->makeClass("B", "b");
        $requireB = $this->_autoloaderTestHelper->makeClass(
            "requireB", "b", "<?php require '$autoloaderPath' ?>"
        );


        $this->_autoloaderTestHelper->assertNotLoadable($classA);
        $this->_autoloaderTestHelper->assertNotLoadable($classA2);

        include AutoloaderTestHelper::getClassDirectory() . DIRECTORY_SEPARATOR
              . "a" . DIRECTORY_SEPARATOR . "$requireA.test.php";

        $this->_autoloaderTestHelper->assertLoadable($classA);
        $this->_autoloaderTestHelper->assertNotLoadable($classB);

        include AutoloaderTestHelper::getClassDirectory() . DIRECTORY_SEPARATOR
              . "b" . DIRECTORY_SEPARATOR . "$requireB.test.php";

        $this->_autoloaderTestHelper->assertLoadable($classA);
        $this->_autoloaderTestHelper->assertLoadable($classA2);
        $this->_autoloaderTestHelper->assertLoadable($classB);

        Autoloader::removeAll();

        foreach ($autoloaders as $autoloader) {
            $autoloader->register();

        }
    }

}