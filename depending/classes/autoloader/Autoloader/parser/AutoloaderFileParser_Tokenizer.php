<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the AutoloaderFileParser_Tokenizer
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
 * @version    SVN: $Id: AutoloaderFileParser_Tokenizer.php,v 1.4 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * These class must be loaded.
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Parser',
    dirname(__FILE__) . '/exception/AutoloaderException_Parser.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileParser',
    dirname(__FILE__) . '/AutoloaderFileParser.php'
);

/**
 * A reliable AutoloaderFileParser implementation which uses PHP's tokenizer
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Parser
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        Autoloader::searchPath()
 * @see        token_get_all()
 */
class AutoloaderFileParser_Tokenizer extends AutoloaderFileParser
{

    const NEXT_CLASS        = 'class';
    const NEXT_NAMESPACE    = 'namespace';
    const NEXT_NOTHING      = 'nothing';


    /**
     * AutoloaderFileParser_Tokenizer is supported if the function token_get_all()
     * exists.
     *
     * @see token_get_all()
     * @return bool
     */
    static public function isSupported()
    {
        return function_exists("token_get_all");
    }


    /**
     * Returns the classes in the code $source
     *
     * getClassesInSource() uses the tokenizer to return
     * a list of class definitions.
     *
     * @param String $source A source which might contain class definitions
     *
     * @throws AutoloaderException_Parser
     * @return Array found classes in the source
     */
    public function getClassesInSource($source)
    {
        $classes        = array();
        $nextStringType = self::NEXT_NOTHING;
        $namespace      = '';
        $tokens         = @token_get_all($source);

        if (! is_array($tokens)) {
            $error = error_get_last();
            throw new AutoloaderException_Parser(
                "Could not tokenize: $error[message]\n$source");

        }

        foreach ($tokens as $token) {
            if (! is_array($token)) {
                continue;


            }
            $tokenID    = $token[0];
            $tokenValue = $token[1];

            switch ($tokenID) {
            case T_NAMESPACE:
                $namespace      = '';
            case T_NS_SEPARATOR:
                $nextStringType = self::NEXT_NAMESPACE;
                break;

            case T_INTERFACE:
            case T_CLASS:
                $nextStringType = self::NEXT_CLASS;
                break;

            case T_STRING:
                $type           = $nextStringType;
                $nextStringType = self::NEXT_NOTHING;

                switch ($type) {
                case self::NEXT_CLASS:
                    $classes[] = $namespace.$tokenValue;
                    break;

                case self::NEXT_NAMESPACE:
                    $namespace .= "$tokenValue\\";
                    break;

                }
                break;

            }

        }
        return $classes;
    }

}
