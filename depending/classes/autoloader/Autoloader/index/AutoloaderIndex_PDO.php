<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Implements the class AutoloaderIndex_PDO
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
 * @subpackage Index
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    SVN: $Id: AutoloaderIndex_PDO.php,v 1.5 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The parent class is needed.
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndex',
    dirname(__FILE__) . '/AutoloaderIndex.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Index_NotFound',
    dirname(__FILE__) . '/exception/AutoloaderException_Index_NotFound.php'
);

/**
 * Implements AutoloaderIndex with PDO
 *
 * This index uses a PDO object to store its data in any
 * database wich understands SQL. There is no need to
 * create any table. The index creates its structure by itself.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Index
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        Autoloader::setIndex()
 * @see        Autoloader::getIndex()
 * @see        PDO
 */
class AutoloaderIndex_PDO extends AutoloaderIndex
{

    /**
     * The name of the default SQLite database.
     *
     * @see getSQLiteInstance()
     */
    const DEFAULT_SQLITE = "AutoloaderIndex_PDO.sqlite.db";

    private
    /**
     * @var Array A map of prepared statements
     * @see _getStatement()
     */
    $_statements = array(),
    /**
     * @var PDO
     */
    $_pdo;

    /**
     * Returns an index using a SQLite database
     *
     * If no filename is given a default database in the
     * temporary directory will be used.
     *
     * @param String $file The path of the sqlite file
     *
     * @return AutoloaderIndex_PDO
     * @see AutoloaderIndex_PDO::DEFAULT_SQLITE
     */
    static public function getSQLiteInstance($file = null)
    {
        if (is_null($file)) {
            $file
                = sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::DEFAULT_SQLITE;

        }
        $pdo = new PDO("sqlite://$file");
        return new self($pdo);
    }

    /**
     * Initializes the index
     *
     * If the structure doesn't exist in the database it will be
     * created. The relation for the index is autoloadindex.
     *
     * The PDO object will be configured to throw exceptions.
     *
     * @param PDO $pdo A PDO instance
     */
    public function __construct(PDO $pdo)
    {
        $this->_pdo = $pdo;

        $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $pdo->query("SELECT 1 FROM autoloadindex");

        } catch (PDOException $e) {
            $pdo->exec(
                "
                CREATE TABLE autoloadindex (
                    context   CHAR(32),
                    class     VARCHAR(255),
                    path      VARCHAR(255),

                    PRIMARY KEY (context, class)
                );
                "
            );

        }
    }

    /**
     * Returns the PDO object of this index
     *
     * @return PDO
     */
    public function getPDO()
    {
        return $this->_pdo;
    }

    /**
     * Deletes the relation autoloadindex
     *
     * @throws AutoloaderException_Index
     * @return void
     */
    public function delete()
    {
        try {
            $this->_pdo->exec("DROP TABLE autoloadindex");

        } catch (PDOException $e) {
            throw new AutoloaderException_Index($e->getMessage());

        }
    }

    /**
     * Prepares a PDOStatement and would reuse it if it is requested again
     *
     * @param String $sql A SQL statement
     *
     * @see $_statements
     * @return PDOStatement
     */
    private function _getStatement($sql)
    {
        $key = md5($sql);
        if (! array_key_exists($key, $this->_statements)) {
            $this->_statements[$key] = $this->_pdo->prepare($sql);

        }
        return $this->_statements[$key];
    }

    /**
     * Returns the unfiltered path of a class definition
     *
     * @param String $class The class name
     *
     * @throws AutoloaderException_Index
     * @throws AutoloaderException_Index_NotFound
     * @return String
     */
    protected function getRawPath($class)
    {
        try {
            $stmt = $this->_getStatement(
                "SELECT path FROM autoloadindex
                 WHERE context = ? AND class = ?"
            );
            $stmt->execute(
                array(
                    $this->getContext(),
                    $class
                )
            );
            $path = $stmt->fetchColumn();
            $stmt->closeCursor();
            if (! $path) {
                throw new AutoloaderException_Index_NotFound($class);

            }
            return $path;

        } catch (PDOException $e) {
            throw new AutoloaderException_Index($e->getMessage());

        }
    }

    /**
     * Returns all paths in the index
     *
     * @throws AutoloaderException_Index
     * @return Array
     */
    public function getPaths()
    {
        try {
            $stmt =  $this->_getStatement(
                "SELECT class, path FROM autoloadindex
                 WHERE context = ?"
            );
            $stmt->execute(array($this->getContext()));
            $paths = array();
            foreach ($stmt->fetchAll() as $data) {
                $paths[$data['class']] = $data['path'];

            }
            return $paths;

        } catch (PDOException $e) {
            throw new AutoloaderException_Index($e->getMessage());

        }
    }

    /**
     * Returns the size of the index
     *
     * @throws AutoloaderException_Index
     * @see Countable
     * @return int
     */
    public function count()
    {
        try {
            $stmt = $this->_getStatement(
                "SELECT count(*) FROM autoloadindex WHERE context = ?"
            );
            $stmt->execute(array($this->getContext()));
            $count = $stmt->fetchColumn();
            $stmt->closeCursor();

            if ($count === false) {
                throw new AutoloaderException_Index("Couldn't SELECT count(*).");

            }
            return $count;

        } catch (PDOException $e) {
            throw new AutoloaderException_Index($e->getMessage());

        }
    }

    /**
     * Stores the path immediately persistent
     *
     * There is no sense in making the class paths persistent
     * during {@link save()}. It is stored immediately.
     *
     * @param String $class The class name
     * @param String $path  The filtered path
     *
     * @throws AutoloaderException_Index
     * @return void
     */
    protected function setRawPath($class, $path)
    {
        try {
            $this->unsetRawPath($class);
            $this->_getStatement(
                "INSERT INTO autoloadindex (path, class, context)
                 VALUES (:path, :class, :context)"
            )->execute(
                array(
                    "class"   => $class,
                    "path"    => $path,
                    "context" => $this->getContext()
                )
            );

        } catch (PDOException $e) {
            throw new AutoloaderException_Index($e->getMessage());

        }
    }

    /**
     * Deletes the path immediately persistent
     *
     * There is no sense in making the class paths persistent
     * during {@link save()}. It is deleted immediately.
     *
     * @param String $class The class name
     *
     * @throws AutoloaderException_Index
     * @return void
     */
    protected function unsetRawPath($class)
    {
        try {
            $stmt = $this->_getStatement(
                "DELETE FROM autoloadindex
                 WHERE context = ? AND class = ?"
            );
            $stmt->execute(array($this->getContext(), $class));

        } catch (PDOException $e) {
            throw new AutoloaderException_Index($e->getMessage());

        }
    }

    /**
     * Returns true if the class is contained in the index
     *
     * @param String $class The class name
     *
     * @throws AutoloaderException_Index
     * @return bool
     */
    public function hasPath($class)
    {
        try {
            $stmt = $this->_getStatement(
                "SELECT 1 FROM autoloadindex WHERE context = ? AND class = ?"
            );
            $stmt->execute(array($this->getContext(), $class));
            $hasPath = $stmt->fetchColumn();
            $stmt->closeCursor();

            return (bool) $hasPath;

        } catch (PDOException $e) {
            throw new AutoloaderException_Index($e->getMessage());

        }
    }

    /**
     * Does nothing as {@link setRawPath()} and {@link unsetRawPath()}
     * store immediately
     *
     * @see setRawPath()
     * @see unsetRawPath()
     * @return void
     */
    protected function saveRaw()
    {

    }

}