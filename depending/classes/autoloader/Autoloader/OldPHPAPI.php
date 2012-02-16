<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the class OldPHPAPI
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
 * @version   SVN: $Id: OldPHPAPI.php,v 1.4 2011/01/11 14:25:32 nicke Exp $
 * @link      http://php-autoloader.malkusch.de/en/
 */

/**
 * Implements missing PHP5 functions and constants
 *
 * A missing function is implemented by a static method of this class with
 * an @implement annotation. The @implement annotation gives the name of the
 * function.
 *
 * @category PHP
 * @package  Autoloader
 * @author   Markus Malkusch <markus@malkusch.de>
 * @license  http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version  Release: 1.11
 * @link     http://php-autoloader.malkusch.de/en/
 */
class OldPHPAPI
{

    /**
     * Defines all required functions and constants
     *
     * @see _define()
     * @return void
     */
    public function checkAPI()
    {
        // The constants are defined.
        $this->_define('T_NAMESPACE');
        $this->_define('T_NS_SEPARATOR');
        $this->_define('E_USER_DEPRECATED', E_USER_WARNING);

        /*
         * Every static public method with an @implement annotation defines
         * a function.
         */
        $reflectionObject = new ReflectionObject($this);
        $methods = $reflectionObject->getMethods(
            ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC
        );
        foreach ($methods as $method) {
            // The method comment is parsed for the @implement annotation
            $isAnnotated = preg_match(
                '/\s*\*\s*@implement\s+(\S+)/',
                $method->getDocComment(),
                $matches
            );
            if (! $isAnnotated) {
                continue;

            }

            $function = $matches[1];

            // A function might already exist.
            if (function_exists($function)) {
                continue;

            }

            // The parameters are build.
            $parametersArray = array();
            for ($i = 0; $i < $method->getNumberOfParameters(); $i++) {
                $parametersArray[] = '$parameter' . $i;

            }
            $parameters = implode(', ', $parametersArray);

            // The function is defined.
            $apiClass   = get_class($this);
            $definition = "function $function($parameters)
                {
                    \$parameters = func_get_args();
                    return call_user_func_array(
                        array('$apiClass', '{$method->getName()}'),
                        \$parameters
                    );
                }
            ";
            eval($definition);

        }
    }

    /**
     * Defines a global constant if it is not defined yet
     *
     * $value is optional and would be name of the constant itself if omitted.
     *
     * @param String $const The constant name
     * @param String $value The optional constant value
     *
     * @see checkAPI()
     * @return void
     */
    private function _define($const, $value = null)
    {
        if (defined($const)) {
            return;

        }
        define($const, is_null($value) ? $const : $value);
    }

    /**
     * Implements error_get_last()
     *
     * @implement error_get_last
     * @see error_get_last()
     * @return array
     */
    public static function errorGetLast()
    {
        $message = 'Getting the last error message is not supported'
            . 'by your old PHP version.';
        return array(
            'type'      => 0,
            'message'   => $message,
            'file'      => '/dev/null',
            'line'      => 0
        );
    }

    /**
     * Implements sys_get_temp_dir()
     *
     * @implement sys_get_temp_dir
     * @see sys_get_temp_dir()
     * @throws LogicException It's not expected to fail
     * @return String
     */
    public static function sysGetTempDir()
    {
        $envVars = array('TMP', 'TEMP', 'TMPDIR');
        foreach ($envVars as $envVar) {
            $temp = getenv($envVar);
            if (! empty($temp)) {
                return $temp;

            }

        }

        $temp = tempnam(__FILE__, '');
        if (file_exists($temp)) {
            unlink($temp);
            return dirname($temp);

        }
        throw new LogicException("sys_get_temp_dir() failed.");
    }

    /**
     * Implements parse_ini_string()
     *
     * @param String $data The parsable ini string
     *
     * @implement parse_ini_string
     * @see parse_ini_string()
     * @see AutoloaderIndex_IniFile
     * @return Array
     */
    public static function parseIniString($data)
    {
        $file = tempnam(sys_get_temp_dir(), 'parse_ini_string');
        file_put_contents($file, $data);
        $iniData = parse_ini_file($file);
        unlink($file);
        return $iniData;
    }

    /**
     * Implements str_getcsv()
     *
     * @param String $data The parsable csv string
     *
     * @implement str_getcsv
     * @see str_getcsv()
     * @see AutoloaderIndex_CSV
     * @return Array
     */
    public static function strGetCSV($data)
    {
        $fp = tmpfile();
        fwrite($fp, $data);
        fseek($fp, 0);
        $csv = fgetcsv($fp);
        fclose($fp);
        return $csv;
    }

}