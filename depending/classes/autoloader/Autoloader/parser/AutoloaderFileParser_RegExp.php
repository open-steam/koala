<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the AutoloaderFileParser_RegExp
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
 * @subpackage Parser
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    SVN: $Id: AutoloaderFileParser_RegExp.php,v 1.4 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The parent class must be loaded.
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileParser',
    dirname(__FILE__) . '/AutoloaderFileParser.php'
);

/**
 * An AutoloaderFileParser implementation which uses a regulare expression for
 * parsing
 *
 * This is not as reliable as the AutoloaderFileParser_Tokenizer.
 * But if there's no tokenizer support this is a well working
 * fallback. This class is as well as these regular expressions:
 * <samp>
 * ~\s*((abstract\s+)?class|interface)\s+([a-z].*)[$\s#/{]~imU
 * </samp>
 * <samp>
 * ~namespace\s+([^\s;{]+)~im
 * </samp>
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Parser
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        Autoloader::searchPath()
 */
class AutoloaderFileParser_RegExp extends AutoloaderFileParser
{

    /**
     * AutoloaderFileParser_RegExp is supported in every environment.
     *
     * @return bool
     */
    static public function isSupported()
    {
        return true;
    }

    /**
     * Returns classes in the code $source
     *
     * getClassesInSource() uses a regular expression to find class definitions.
     *
     * @param String $source The content which is searched for class definitions
     *
     * @return Array found classes in the source
     */
    public function getClassesInSource($source)
    {
        // Namespaces are searched.
        $namespaces       = array();
        $namespacePattern = '~namespace\s+([^\s;{]+)~im';
        preg_match_all(
            $namespacePattern,
            $source,
            $namespaceMatches,
            PREG_OFFSET_CAPTURE
        );
        foreach ($namespaceMatches[1] as $namespaceMatch) {
            $namespace  = $namespaceMatch[0];
            $offset     = $namespaceMatch[1];

            $namespaces[$offset] = $namespace;

        }

        // Classes and interfaces are searched.
        $classes = array();
        $classPattern
            = '~\s*((abstract\s+)?class|interface)\s+([a-z].*)[$\s#/{]~imU';
        preg_match_all(
            $classPattern,
            $source,
            $classMatches,
            PREG_OFFSET_CAPTURE
        );
        foreach ($classMatches[3] as $classMatch) {
            $class  = $classMatch[0];
            $offset = $classMatch[1];

            // The appropriate will be prepended.
            $classNamespace = '';
            foreach ($namespaces as $namespaceOffset => $namespace) {
                if ($namespaceOffset > $offset) {
                    break;

                }
                $classNamespace = $namespace . "\\";

            }

            $classes[] = $classNamespace . $class;

        }
        return $classes;
    }

}