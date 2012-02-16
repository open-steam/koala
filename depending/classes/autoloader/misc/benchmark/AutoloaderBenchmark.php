#! /usr/bin/php
<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Starts an index benchmark
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
 * @subpackage Benchmark
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    SVN: $Id: AutoloaderBenchmark.php,v 1.4 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The Autoloader is used for class loading.
 */
require dirname(__FILE__) . "/../../Autoloader.php";

/**
 * The class constructor serves as entry point of this script.
 */
AutoloaderBenchmark::classConstructor();

/**
 * AutoloaderBenchmark class
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Benchmark
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        AutoloaderIndex
 * @see        AutoloaderIndex_CSV
 * @see        AutoloaderIndex_IniFile
 * @see        AutoloaderIndex_PDO
 * @see        AutoloaderIndex_PHPArrayCode
 * @see        AutoloaderIndex_SerializedHashtable
 * @see        AutoloaderIndex_SerializedHashtable_GZ
 */
class AutoloaderBenchmark
{

    private static
    /**
     * @var Array
     */
    $_pdoPool = array();

    private
    /**
     * @var int
     */
    $_getPathCount = 0,
    /**
     * @var array
     */
    $_durations = array(),
    /**
     * @var int
     */
    $_iterations = 0,
    /**
     * @var int
     */
    $_indexSize = 0,
    /**
     * @var String
     */
    $_hashtable,
    /**
     * @var String
     */
    $_hashtableGZ,
    /**
     * @var String
     */
    $_hashtableCSV,
    /**
     * @var String
     */
    $_hashtableIni,
    /**
     * @var String
     */
    $_hashtablePHP,
    /**
     * @var String
     */
    $_sqliteFile;

    /**
     * Serves as entry point for this script.
     *
     * It runs several benchmark iteratios.
     *
     * @return void
     */
    static public function classConstructor()
    {
        $cases = array(
            array(10,    1),
            array(100,   1),
            array(1000,  1),
            array(10000, 1),

            array(10,    10),
            array(100,   10),
            array(1000,  10),
            array(10000, 10),

            array(100,   100),
            array(1000,  100),
            array(10000, 100),

            array(1000,  1000),
            array(10000, 1000),

            array(10000, 10000),
        );

        foreach ($cases as $case) {
            $benchmark = new self($case[0], $case[1]);
            $benchmark->run();
            echo $benchmark;

        }
    }

    /**
     * Creates a new benchmark
     *
     * @param int $indexSize    The size of the index
     * @param int $getPathCount The amount of AutoloaderIndex::getPath() calls
     * @param int $iterations   10000 iterations per default
     */
    public function __construct($indexSize, $getPathCount, $iterations = 10000)
    {
        $this->_indexSize      = $indexSize;
        $this->_iterations     = $iterations;
        $this->_getPathCount   = $getPathCount;
        $this->_sqliteFile     = tempnam(
            "/var/tmp/", "AutoloaderBenchmarkSQLite_"
        );
        $this->_hashtable = tempnam(
            "/var/tmp/", "AutoloaderBenchmarkHT_Serialized_"
        );
        $this->_hashtableGZ = tempnam(
            "/var/tmp/", "AutoloaderBenchmarkHT_Serialized_GZ_"
        );
        $this->_hashtableCSV = tempnam(
            "/var/tmp/", "AutoloaderBenchmarkHT_CSV_"
        );
        $this->_hashtableIni = tempnam(
            "/var/tmp/", "AutoloaderBenchmarkHT_Ini_"
        );
        $this->_hashtablePHP = tempnam(
            "/var/tmp/", "AutoloaderBenchmarkHT_PHP_"
        );

        unlink($this->_sqliteFile);
        unlink($this->_hashtable);
        unlink($this->_hashtableGZ);
        unlink($this->_hashtableCSV);
        unlink($this->_hashtableIni);
        unlink($this->_hashtablePHP);
    }

    /**
     * Starts the benchmark
     *
     * @return void
     */
    public function run()
    {
        $indexes = $this->_createIndexes();
        foreach ($indexes as $name => $index) {
            $this->_fillIndex($index, $this->_indexSize);

        }

        for ($i = 0; $i < $this->_iterations; $i++) {
            foreach ($this->_createIndexes() as $name => $index) {
                if (! isset($this->_durations[$name])) {
                    $this->_durations[$name] = 0;

                }
                $classSet = $this->_getClassSet();
                clearstatcache();

                $startTime = microtime(true);
                $this->runBenchmark($index, $classSet);
                $stopTime = microtime(true);

                $this->_durations[$name] += $stopTime - $startTime;

            }

        }

        foreach ($indexes as $index) {
            $index->delete();

        }
        unlink($this->_sqliteFile);
    }

    /**
     * Runs one iteration of the benchmark
     *
     * @param AutoloaderIndex $index    The AutoloaderIndex instance
     * @param array           $classSet The list of classes to fetch
     *
     * @return void
     */
    protected function runBenchmark(AutoloaderIndex $index, Array $classSet)
    {
        foreach ($classSet as $classNumber) {
            $index->getPath($this->_getIndexedClassName($classNumber));

        }
    }


    /**
     * Returns a random set of class numbers, which the index contains
     *
     * The count of this is $_getPathCount. To get the class name of a class
     * number you can use _getIndexedClassName().
     *
     * @see $_getPathCount
     * @see _getIndexedClassName()
     * @return Array
     */
    private function _getClassSet()
    {
        $allClasses = array();
        $classes    = array();
        for ($i = 0; $i < $this->_indexSize; $i++) {
            $allClasses[] = $i;

        }
        for ($i = 0; $i < $this->_getPathCount; $i++) {
            $index = (int) mt_rand(0, count($allClasses) - 1);
            $classes[] = $allClasses[$index];
            unset($allClasses[$index]);
            $allClasses = array_values($allClasses);

        }
        return $classes;
    }

    /**
     * Returns the human readable output of the benchmark
     *
     * @return String
     */
    public function __toString()
    {
        $durations = array();
        $sortIndex = array();
        foreach ($this->_durations as $name => $duration) {
            $paddedName  = str_pad($name . ":", 13);
            $avgDuration = $this->getAverageDuration($name);

            $durations[$name] = "$paddedName {$avgDuration}";
            $sortIndex[$name] = $duration;

        }

        // Sort the result by duration
        asort($sortIndex);
        $sortedDurations = array();
        foreach ($sortIndex as $name => $duration) {
            $sortedDurations[] = $durations[$name];

        }

        return "==================================\n"
             . "Index size:      $this->_indexSize\n"
             . "getPath() count: $this->_getPathCount\n"
             . "Iterations:      $this->_iterations\n"
             . "----------------------------------\n"
             . implode("\n", $sortedDurations) . "\n"
             . "==================================\n";
    }

    /**
     * Returns the durations array
     *
     * @return Array
     */
    public function getDurations()
    {
        return $this->_durations;
    }

    /**
     * Returns the avarage duration Array
     *
     * @return Array
     */
    public function getAverageDurations()
    {
        $durations = array();
        foreach ($this->_durations as $name => $duration) {
            $durations[$name] = $duration / $this->_iterations;

        }
        return $durations;
    }

    /**
     * Returns the avarage duration for one index
     *
     * @param String $name The index name
     *
     * @return float
     */
    public function getAverageDuration($name)
    {
        $durations = $this->getAverageDurations();
        return $durations[$name];
    }

    /**
     * Creates new indexes which are tested in this benchmark
     *
     * @see _fillIndex()
     * @return Array
     */
    private function _createIndexes()
    {
        try {
            self::$_pdoPool['mysql'] = new PDO("mysql:dbname=test");

        } catch (PDOException $e) {
            /*
             * This happens when too many connections are open.
             * We will reuse the last connection.
             */

        }


        $indexes = array(
            "sqlite" => AutoloaderIndex_PDO::getSQLiteInstance($this->_sqliteFile),
            "mysql"  => new AutoloaderIndex_PDO(self::$_pdoPool['mysql']),
            "hashtable"    => new AutoloaderIndex_SerializedHashtable(),
            "hashtableGZ"  => new AutoloaderIndex_SerializedHashtable_GZ(),
            "hashtableCSV" => new AutoloaderIndex_CSV(),
            "hashtableIni" => new AutoloaderIndex_IniFile(),
            "hashtablePHP" => new AutoloaderIndex_PHPArrayCode()
        );
        $indexes["hashtable"]->setIndexPath($this->_hashtable);
        $indexes["hashtableGZ"]->setIndexPath($this->_hashtableGZ);
        $indexes["hashtableCSV"]->setIndexPath($this->_hashtableCSV);
        $indexes["hashtableIni"]->setIndexPath($this->_hashtableIni);
        $indexes["hashtablePHP"]->setIndexPath($this->_hashtablePHP);

        foreach ($indexes as $index) {
            Autoloader::getRegisteredAutoloader()->setIndex($index);

        }

        return $indexes;
    }

    /**
     * Inserts an amount of classes into an empty index
     *
     * @param AutoloaderIndex $index An instance of AutoloaderIndex
     * @param int             $count the amount of classes for the index
     *
     * @see _createIndexes()
     * @return void
     */
    private function _fillIndex(AutoloaderIndex $index, $count)
    {
        for ($i = 0; $i < $count; $i++) {
            $index->setPath($this->_getIndexedClassName($i), uniqid());

        }
        $index->save();
    }

    /**
     * Returns a class name for a class number
     *
     * @param int $number The class number
     *
     * @see _getClassSet()
     * @return String
     */
    private function _getIndexedClassName($number)
    {
        return "class$number";
    }

}
