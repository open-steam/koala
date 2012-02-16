<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the test cases for the AutoloaderFileParser
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
 * @version    SVN: $Id: TestParser.php,v 1.3 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The Autoloader is used for class loading.
 */
require_once dirname(__FILE__) . "/../Autoloader.php";

/**
 * AutoloaderFileParser test cases
 * 
 * @category   PHP
 * @package    Autoloader
 * @subpackage Test
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        AutoloaderFileParser
 */
class TestParser extends PHPUnit_Framework_TestCase
{

    /**
     * Asserts that the tokenizer is used as default
     *
     * @return void
     */
    public function testAutoloaderUsesTokenizer()
    {
        $this->assertTrue(AutoloaderFileParser_Tokenizer::isSupported());

        $autoloader = new Autoloader();
        $autoloader->register();
        $autoloader->remove();

        $this->assertTrue(
            $autoloader->getParser() instanceof AutoloaderFileParser_Tokenizer
        );
    }

    /**
     * Asserts that the Parser finds all classes in a string
     *
     * @param AutoloaderFileParser $parser  This AutoloaderFileParser is tested.
     * @param Array                $classes Theses classes are expected to be found.
     * @param String               $source  This content will be search for classes.
     * 
     * @return void
     * @dataProvider provideTestGetClasses
     */
    public function testGetClassesInSource(
        AutoloaderFileParser $parser,
        Array $classes,
        $source
    ) {
        $this->assertEquals($classes, $parser->getClassesInSource($source));
    }

    /**
     * Asserts that the Parser finds all classes in a file
     *
     * The file is created dynamically from the given source.
     *
     * @param AutoloaderFileParser $parser  This AutoloaderFileParser is tested.
     * @param Array                $classes Theses classes are expected to be found.
     * @param String               $source  This content will be search for classes.
     *
     * @return void
     * @dataProvider provideTestGetClasses
     */
    public function testGetClassesInFile(
        AutoloaderFileParser $parser,
        Array $classes,
        $source
    ) {
        $file = $this->_createFile($source);
        $this->assertEquals($classes, $parser->getClassesInFile($file));
        unlink($file);
    }

    /**
     * Asserts that the Parser finds a class in a string
     *
     * @param AutoloaderFileParser $parser This AutoloaderFileParser is tested.
     * @param String               $class  This class is expected to be found.
     * @param String               $source This content will be search for the class.
     *
     * @return void
     * @dataProvider provideTestIsClassInSource
     */
    public function testIsClassInSource(
        AutoloaderFileParser $parser,
        $class,
        $source
    ) {
        $this->assertTrue($parser->isClassInSource($class, $source));
        $this->assertFalse($parser->isClassInSource($class.uniqid(), $source));
    }

    /**
     * Asserts that the Parser finds a class in a file
     *
     * The file is created dynamically from the given source.
     *
     * @param AutoloaderFileParser $parser This AutoloaderFileParser is tested.
     * @param String               $class  This class is expected to be found.
     * @param String               $source This content will be search for the class.
     *
     * @return void
     * @dataProvider provideTestIsClassInSource
     */
    public function testIsClassInFile(
        AutoloaderFileParser $parser,
        $class,
        $source
    ) {
        $file = $this->_createFile($source);
        $this->assertTrue(
            $parser->isClassInFile($class, $file),
            "$class not found in $file. These classes where found: "
            . print_r($parser->getClassesInSource($source), true)
        );
        $this->assertFalse($parser->isClassInSource($class.uniqid(), $file));
        unlink($file);
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
     * Provides test cases for TestIsClassIn* tests
     *
     * @see testIsClassInSource()
     * @see testIsClassInFile()
     * @return Array
     */
    public function provideTestIsClassInSource()
    {
        $provider = array();
        foreach ($this->provideParser() as $parser) {
            foreach ($this->provideSource() as $source) {
                foreach ($source[0] as $class) {
                    $provider[] = array($parser[0], $class, $source[1]);

                }
            }
        }
        return $provider;
    }

     /**
     * Provides test cases for TestTestGetClasses* tests
     *
     * @see testGetClassesInFile()
     * @see testGetClassesInSource()
     * @return Array
     */
    public function provideTestGetClasses()
    {
        $provider = array();
        foreach ($this->provideParser() as $parser) {
            foreach ($this->provideSource() as $source) {
                $provider[] = array($parser[0], $source[0], $source[1]);

            }
        }
        return $provider;
    }

    /**
     * Provides code with an array of defined classes
     *
     * @return Array
     */
    public function provideSource()
    {
        $cases = array(
            array(array("Test"), "<?php interface Test{}?>"),
            array(array("teSt"), "<?php interface teSt{}?>"),
            array(array("Test"), "<?php abstract class Test{}?>"),
            array(array("Test"), "<?php\nclass Test{\n}?>"),
            array(array("Test"), "<?php\n class Test {\n}?>"),
            array(array("Test"), "<?php\nclass Test\n {\n}?>"),
            array(array("Test"), "<?php\nclass Test \n {\n}?>"),
            array(array("Test"), "<?php\nClass Test \n {\n}?>"),
            array(array("Test"), "<?php\nclass Test \n {\n}?>"),


            array(
                array("Test1", "Test"),
                "<?php\nclass Test1 \n {\n}\nclass Test \n {\n} ?>"
            ),
            array(
                array("Test1", "Test"),
                "<?php\nclass Test1 \n {\n}\interface Test \n {\n} ?>"
            ),
            array(
                array("Test1", "Test"),
                "<?php\nabstract class Test1 \n {\n}\interface Test \n {\n} ?>"
            ),
            array(
                array("Test1", "Test"),
                "<?php\ninterface Test1 \n {\n}\interface Test \n {\n} ?>"
            ),
        );

        // These tests works only if PHP >= 5.3
        $helper = new AutoloaderTestHelper($this);
        if ($helper->hasNamespaceSupport()) {
            $cases[] = array(
                array(
                    'de\malkusch\autoloader\test\ns\bracket\Test1',
                    'de\malkusch\autoloader\test\ns\bracket\Test2'
                ),
                file_get_contents(
                    dirname(__FILE__) . "/namespaceDefinitions/Bracket.php"
                )
            );
            $cases[] = array(
                array(
                    'de\malkusch\autoloader\test\ns\multibracket\A\Test1',
                    'de\malkusch\autoloader\test\ns\multibracket\A\Test2',
                    'de\malkusch\autoloader\test\ns\multibracket\B\Test1',
                    'de\malkusch\autoloader\test\ns\multibracket\B\Test2',
                ),
                file_get_contents(
                    dirname(__FILE__) . "/namespaceDefinitions/MultiBracket.php"
                )
            );
            $cases[] = array(
                array(
                    'de\malkusch\autoloader\test\ns\multinobracket\A\Test1',
                    'de\malkusch\autoloader\test\ns\multinobracket\A\Test2',
                    'de\malkusch\autoloader\test\ns\multinobracket\B\Test1',
                    'de\malkusch\autoloader\test\ns\multinobracket\B\Test2',
                ),
                file_get_contents(
                    dirname(__FILE__) . "/namespaceDefinitions/MultiNoBracket.php"
                )
            );
            $cases[] = array(
                array(
                    'de\malkusch\autoloader\test\ns\nobracket\Test1',
                    'de\malkusch\autoloader\test\ns\nobracket\Test2'
                ),
                file_get_contents(
                    dirname(__FILE__) . "/namespaceDefinitions/NoBracket.php"
                )
            );

        }
        return $cases;
    }

    /**
     * Creates a file from and puts the content of $source into the file
     *
     * @param String $source The content for the new file
     *
     * @return String
     */
    private function _createFile($source)
    {
        $file = tempnam(sys_get_temp_dir(), "AutoloaderTestParser");
        $this->assertTrue((bool) file_put_contents($file, $source));
        return $file;
    }

    /**
     * Returns a list of AutoloaderFileParser objects which are will be tested in
     * all test cases
     *
     * @return Array
     */
    public function provideParser()
    {
        return array(
            array(new AutoloaderFileParser_RegExp()),
            array(new AutoloaderFileParser_Tokenizer())
        );
    }

}