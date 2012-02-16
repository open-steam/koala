<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the AutoloaderFileIterator
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
 * @subpackage FileIterator
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    SVN: $Id: AutoloaderFileIterator.php,v 1.5 2011/01/11 19:42:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * This class must be loaded.
 */
InternalAutoloader::getInstance()->registerClass(
    'Autoloader',
    dirname(__FILE__) . '/../Autoloader.php'
);

/**
 * Finds potential files with class definitions
 *
 * As AutoloaderFileIterator implements the Iterator interface iterating through
 * a class path recursively is as simple as:
 *
 * <code>
 * <?php
 *
 * foreach ($fileIterator as $path) {
 *
 * }
 * </code>
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage FileIterator
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        Autoloader::searchPath()
 */
abstract class AutoloaderFileIterator implements Iterator
{

    protected
    /**
     * @var int Skip files greater than 1MB as default
     */
    $skipFilesize = 1048576,
    /**
     * @var Array ignore SVN and CVS folders
     */
    $skipDirPatterns = array(
        '~/\.svn~',
        '~/\.CVS~',
        '~/CVS~'
    ),
    /**
     * @var Array ignore *.dist and multimedia files
     */
    $skipFilePatterns = array(
        '~\.(html|css|htm|xml)$~i',
        '~\.(doc|docx|ppt|pptx|xls|xlsx)$~i',
        '~\.(dist|jpe?g|gif|png|tiff|tif|pdf|svg|og[gm]|mp3|wav|mpe?g)$~i'
    ),
    $onlyDirPattern,
    $onlyFilePattern,
    /**
     * @var Autoloader
     */
    $autoloader;

    /**
     * Resets the state of the AutoloaderFileIterator
     * 
     * This happens implicit if the configuration of the object has changed and
     * would have a different result set.
     *
     * The default implementaion does nothing.
     *
     * @see setAutoloader()
     * @see addSkipPattern()
     * @see setSkipFilesize()
     * @return void
     */
    protected function reset()
    {

    }

    /**
     * Sets the Autoloader for this object
     * 
     * The Autoloader object has the class path where this object will search for
     * files. This changes the configuration of this object, so reset() is called.
     *
     * @param Autoloader $autoloader The Autoloader object
     *
     * @see $autoloader
     * @see reset()
     * @return void
     */
    public function setAutoloader(Autoloader $autoloader)
    {
        $this->autoloader = $autoloader;
        $this->reset();
    }

    /**
     * Adds a regular expression for ignoring directories in the class paths
     *
     * Files which paths match one of these patterns won't be
     * searched for class definitions.
     *
     * This is useful for version control paths where files
     * with class definitions exists.
     * Subversion (.svn) and CVS (.CVS) are excluded by default.
     *
     * This changes the configuration of this object, so reset() is called.
     *
     * @param String $pattern a regular expression including delimiters
     *
     * @see $skipPatterns
     * @see reset()
     * @return void
     */
    public function addSkipDirPattern($pattern)
    {
        $this->skipDirPatterns[] = $pattern;
        $this->reset();
    }
    
    /**
     * Adds a regular expression for ignoring files in the class paths
     *
     * Files which paths match one of these patterns won't be
     * searched for class definitions.
     *
     * This is useful for version control paths where files
     * with class definitions exists.
     * Subversion (.svn) and CVS (.CVS) are excluded by default.
     *
     * This changes the configuration of this object, so reset() is called.
     *
     * @param String $pattern a regular expression including delimiters
     *
     * @see $skipPatterns
     * @see reset()
     * @return void
     */
    public function addSkipFilePattern($pattern)
    {
        $this->skipFilePatterns[] = $pattern;
        $this->reset();
    }
    
    public function setOnlyDirPattern($pattern) {
    	$this->onlyDirPattern = $pattern;
    	$this->reset();
    }
    
    public function setOnlyFilePattern($pattern) {
    	$this->onlyFilePattern = $pattern;
    	$this->reset();
    }

    /**
     * Sets a file size to ignore files bigger than $size
     *
     * The autoloader has to look into every file. Large files
     * like images may result in exceeding the max_execution_time.
     *
     * Default is set to 1MB. A size of 0 would disable this limitation.
     *
     * This changes the configuration of this object, so reset() is called.
     *
     * @param int $size Size in bytes
     *
     * @see $skipFilesize
     * @see reset()
     * @return void
     */
    public function setSkipFilesize($size)
    {
        $this->skipFilesize = $size;
        $this->reset();
    }

}