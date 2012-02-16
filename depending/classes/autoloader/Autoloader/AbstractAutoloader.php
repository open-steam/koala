<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the class AbstractAutoloader
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
 * @category  PHP
 * @package   Autoloader
 * @author    Markus Malkusch <markus@malkusch.de>
 * @copyright 2009 - 2010 Markus Malkusch
 * @license   http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version   SVN: $Id: AbstractAutoloader.php,v 1.4 2011/01/11 14:25:32 nicke Exp $
 * @link      http://php-autoloader.malkusch.de/en/
 */

/**
 * These classes are needed. As autoloading does not work in the abstract
 * autoloader, they have to be required traditionally.
 */
require_once
    dirname(__FILE__) . '/autoloadAPI/AutoloadAPI.php';
require_once
    dirname(__FILE__) . '/exception/AutoloaderException.php';
require_once
    dirname(__FILE__) . '/exception/AutoloaderException_Include.php';
require_once
    dirname(__FILE__) . '/exception/AutoloaderException_Include_FileNotExists.php';
require_once
    dirname(__FILE__) . '/exception/AutoloaderException_Include_ClassNotDefined.php';

/**
 * An abstract autoloader
 *
 * @category PHP
 * @package  Autoloader
 * @author   Markus Malkusch <markus@malkusch.de>
 * @license  http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version  Release: 1.11
 * @link     http://php-autoloader.malkusch.de/en/
 */
abstract class AbstractAutoloader
{

    /**
     * The name of the deprecated class constructor is __static().
     *
     * @deprecated PEAR coding standards forbid the usage of a double underscore.
     */
    const CLASS_CONSTRUCTOR_DEPRECATED = '__static';

    /**
     * The name of the class constructor is classConstructor().
     */
    const CLASS_CONSTRUCTOR = 'classConstructor';

    /**
     * implements autoloading
     *
     * This method should load the definition of $class. On failure it should
     * throw an AutoloaderException.
     *
     * @param String $class The normalized class name
     *
     * @see autoload()
     * @see normalizeClass()
     * @throws AutoloaderException
     * @return void
     */
    abstract protected function doAutoload($class);

    /**
     * Normalizes the reference of a class name with strtolower()
     *
     * Normalizing is needed as PHP is case insensitive.
     *
     * @param String &$class The reference of a class name
     *
     * @see strtolower().
     * @return void
     */
    static public function normalizeClass(& $class)
    {
        $class = strtolower($class);
    }

    /**
     * Registers this Autoloader at the stack
     *
     * After registration, this Autoloader is autoloading class definitions with
     * its method autoload().
     * {@link spl_autoload_register()} disables __autoload(). This might be
     * unwanted, so register() also adds __autoload() to the stack.
     *
     * @see AutoloadAPI::registerAutoloader()
     * @see autoload()
     * @see getCallback()
     * @return void
     */
    public function register()
    {
        // spl_autoload_register() disables __autoload(). This might be unwanted.
        if (function_exists('__autoload')) {
            AutoloadAPI::getInstance()->registerAutoloader("__autoload");

        }
        AutoloadAPI::getInstance()->registerAutoloader($this->getCallback());
    }

    /**
     * Returns true for Autoloaders in the spl_autoload stack
     *
     * @return bool
     */
    public function isRegistered()
    {
        return in_array(
            $this->getCallback(),
            AutoloadAPI::getInstance()->getRegisteredAutoloaders(),
            true
        );
    }

    /**
     * Removes this Autoloader from the stack
     *
     * @see removeAll()
     * @see AutoloadAPI::removeAutoloader()
     * @return void
     */
    public function remove()
    {
        AutoloadAPI::getInstance()->removeAutoloader($this->getCallback());
    }

    /**
     * Removes all instances of Autoloader from the stack
     *
     * @see remove()
     * @return void
     */
    static public function removeAll()
    {
        //TODO use static:: in PHP 5.3 and remove the other implementations
        foreach (self::getRegisteredAutoloaders() as $autoloader) {
            $autoloader->remove();

        }
    }

    /**
     * Returns all registered Autoloader instances which are doing their jobs
     *
     * @see AutoloadAPI::getRegisteredAutoloaders()
     * @see register()
     * @return Array
     */
    static public function getRegisteredAutoloaders()
    {
        $autoloaders = array();
        $callbacks   = AutoloadAPI::getInstance()->getRegisteredAutoloaders();
        foreach ($callbacks as $callback) {
            if (! is_array($callback)) {
                continue;

            }
            //TODO use static:: in PHP 5.3 and remove the other implementations
            if (! $callback[0] instanceof self) {
                continue;

            }
            $autoloaders[] = $callback[0];

        }
        return $autoloaders;
    }

    /**
     * call from the spl_autoload stack
     *
     * This method will try to load the class definition by calling the
     * abstract method doAutoload().
     *
     * @param String $class The class name
     *
     * @see register()
     * @see getCallback()
     * @see doAutoload()
     * @see normalizeClass()
     * @return void
     */
    public function autoload($class)
    {
        self::normalizeClass($class);

        /*
         * spl_autoload_call() runs the complete stack,
         * even though the class is already defined by
         * a previously registered method.
         */
        if (class_exists($class, false) || interface_exists($class, false)) {
            return;

        }


        try {
            $this->doAutoload($class);

        } catch (AutoloaderException $exception) {
            /**
             * Throwing an exception breaks compatibility to functions like
             * class_exists().
             *
             * @see class_exists()
             * @see interface_exists()
             */
            return;

        }
    }

    /**
     * Returns a callback which calls the method autoload() on this object
     *
     * @see autoload()
     * @return Callback
     */
    protected function getCallback()
    {
        return array($this, 'autoload');
    }

    /**
     * Includes the class definition and calls the class constructor
     *
     * If the class $class has the method public static classConstructor(), it
     * will be called.
     *
     * The old class constructor __static() violates the PEAR coding standard.
     * It is still supported but would raise an E_USER_DEPRECATED warning.
     *
     * @param String $class The classname
     * @param String $path  The path to the class definition
     *
     * @throws AutoloaderException_Include
     * @throws AutoloaderException_Include_FileNotExists
     * @throws AutoloaderException_Include_ClassNotDefined
     * @return void
     */
    protected function loadClass($class, $path)
    {
        /**
         * The file_exists() could also be in the if block after the include_once.
         * The include_once must get the @ error suppressor. But then an application
         * is no more debuggable plus the expected error would come in the error log.
         */
        if (! file_exists($path)) {
            throw new AutoloaderException_Include_FileNotExists($path);

        }
        if (! include_once $path) {
            $error = error_get_last();
            throw new AutoloaderException_Include(
                "Failed to include $path for $class: $error[message]"
            );

        }


        if (! (class_exists($class, false) || interface_exists($class, false))) {
            throw new AutoloaderException_Include_ClassNotDefined($class);

        }


        // The class constructor would be called.
        $isClassConstructorCalled = $this->_callClassConstructor(
            $class,
            self::CLASS_CONSTRUCTOR
        );

        // If there is no class constructor, there might be a deprecated __static().
        if (! $isClassConstructorCalled) {
            $isDeprecatedClassConstructorCalled = $this->_callClassConstructor(
                $class,
                self::CLASS_CONSTRUCTOR_DEPRECATED
            );

            /**
             * A call of the deprecated __static() raises an E_USER_DEPRECATED
             * warning.
             */
            if ($isDeprecatedClassConstructorCalled) {
                $warning
                    = "The class constructor"
                    . " $class::" . self::CLASS_CONSTRUCTOR_DEPRECATED . "()"
                    . " is deprecated."
                    . " Use $class::" . self::CLASS_CONSTRUCTOR . "() instead!";

                trigger_error($warning, E_USER_DEPRECATED);

            }
        }
    }

    /**
     * Calls the class constructor
     *
     * If the class $class has the method public static $constructor, it
     * will be called.
     *
     * @param String $class       A class which might have a class constructor
     * @param String $constructor the method name of the class constructor
     *
     * @return bool true if the class constructor was called
     */
    private function _callClassConstructor($class, $constructor)
    {
        $reflectionClass = new ReflectionClass($class);
        if (! $reflectionClass->hasMethod($constructor)) {
            return false;

        }

        $static = $reflectionClass->getMethod($constructor);
        if (! $static->isStatic()) {
            return false;

        }

        if ($static->getDeclaringClass()->getName() != $reflectionClass->getName()) {
            return false;

        }

        $static->invoke(null);
        return true;
    }

}