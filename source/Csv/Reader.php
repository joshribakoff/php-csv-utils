<?php
/**
 * CSV Reader
 *
 * Provides an easy-to-use interface for reading csv-formatted text files. It
 * makes use of the function fgetcsv. It provides quite a bit of flexibility.
 * You can specify just about everything about how it should read a csv file
 *
 * Please read the LICENSE file
 *
 * @copyright    (c) 2010, Luke Visinoni <luke.visinoni@gmail.com>
 * @author        Luke Visinoni <luke.visinoni@gmail.com>
 * @package    PHP CSV Utilities
 * @subpackage    Readers
 * @license    GNU Lesser General Public License
 * @version    $Id$
 */
class Csv_Reader implements Iterator, Countable
{
    /**
     * Tells reader how to read the file
     * @var Csv_Dialect
     * @access protected
     */
    protected $dialect;

    /**
     * Maximum row size
     */
    protected $maxRowSize = 4096;

    /**
     * The currently loaded row
     * @var array
     * @access public
     * @todo: Should this be public? I think it might have been required for ArrayIterator to work properly
     */
    protected $currentRow = array();

    /**
     * This is the current line position in the file we're reading
     * @var integer
     */
    protected $position = 0;

    /**
     * Number of lines skipped due to malformed data
     * @var integer
     * @todo This may be flawed - be sure to test it thoroughly
     */
    protected $skippedLines = 0;

    /**
     * An array of values to use as the header row - allows to reference by key
     */
    protected $header = array();

    /**
     * Path to csv file
     * @var string
     * @access protected
     */
    protected $path;

    /**
     * A handle that points to the file we are reading
     * @var resource
     * @access protected
     */
    protected $handle;

    /**
     * Class constructor
     *
     * @param string Path to csv file we want to open
     * @param Csv_Dialect If a dialect is not provided, Csv_Reader will attempt to guess the file's dialect
     * @throws Csv_Exception_FileNotFound
     */
    public function __construct($path, Csv_Dialect $dialect = null)
    {

        // open the file
        $this->setPath($path);
        $this->initStream();
        if (is_null($dialect)) {
            // if dialect isn't specified in the constructor, the reader will attempt to figure out the format
            $dialect = $this->detectDialect(file_get_contents($this->path));
        }
        $this->dialect = $dialect;
        $this->rewind();

    }

    /**
     * Set the path to the csv file
     *
     * @param string The full path to the file we want to read
     * @access protected
     */
    protected function setPath($path)
    {

        $this->path = $path;
        if (!file_exists($this->path)) {
            throw new Csv_Exception_FileNotFound('File does not exist or is not readable: "' . $path . '".');
        }
        return $this;

    }

    /**
     * Get the path to the csv file we're reading
     *
     * @return string The path to the file we are reading
     * @access public
     */
    public function getPath()
    {

        return $this->path;

    }

    protected function initStream()
    {

        $this->handle = fopen($this->path, 'rb');
        if ($this->handle === false) {
            throw new Csv_Exception_FileNotFound('File does not exist or is not readable: "' . $path . '".');
        }

    }

    /**
     * Loads the current row into memory
     *
     * @access protected
     * @todo Don't use fgetcsv - parse the file manually. I think this would allow much more control
     */
    protected function loadRow()
    {

        if (!$this->current = fgetcsv($this->handle, $this->maxRowSize, $this->dialect->delimiter, $this->dialect->quotechar)) {
            // we actually don't want to throw an exception... that's a little dramatic. maybe log it?
            // throw new Csv_Exception('Invalid format for row ' . $this->position);
        }
        if (
            $this->dialect->escapechar !== ''
            && $this->dialect->escapechar !== $this->dialect->quotechar
            && is_array($this->current)
        ) array_walk($this->current, array($this, 'unescape'));
        // if this row is blank and dialect says to skip blank lines, load in the next one and pretend this never happened
        if ($this->dialect->skipblanklines && is_array($this->current) && count($this->current) == 1 && $this->current[0] == '') {
            $this->skippedlines++;
            $this->next();
        }

    }

    /**
     * Get total rows
     *
     * @return integer The number of rows in the file (not includeing line-breaks in the data)
     * @todo Make sure that this is aware of line-breaks in data as opposed to end of row
     * @access public
     */
    public function close()
    {

        if (is_resource($this->handle)) fclose($this->handle);

    }

    /**
     * Destructor method - Closes the file handle
     *
     * @access public
     */
    public function __destruct()
    {

        $this->close();

    }

    /**
     * The following are the methods required by php's Standard PHP Library - Iterator, Countable Interfaces
     */

    /**
     * Advances the internal pointer to the next row and returns it if valid, otherwise it returns false
     *
     * @access public
     * @return boolean|array An array of data if valid, or false if not
     */
    public function next()
    {

        $this->position++;
        $this->loadRow(); // loads the current row into memory
        return ($this->valid()) ? $this->current() : false;

    }

    /**
     * Tells whether or not the current row is valid - called after next and rewind
     *
     * @access public
     * @return boolean True if the current row is valid
     */
    public function valid()
    {

        if (is_resource($this->handle))
            return (boolean)!feof($this->handle);

        return false;

    }

    /**
     * Returns the current row
     *
     * @access public
     * @return array An array of the current row's data
     */
    public function current()
    {

        if (empty($this->header) || !$this->current) return $this->current;
        else return array_combine($this->header, $this->current);

    }

    /**
     * Moves the internal pointer to the beginning
     *
     * @access public
     */
    public function rewind()
    {

        rewind($this->handle);
        $this->position = 0;
        $this->loadRow(); // loads the current (first) row into memory 

    }

    /**
     * Returns the key of the current row (position of pointer)
     *
     * @access public
     * @return integer
     */
    public function key()
    {

        return (integer)$this->position;

    }

    /**
     * Returns the number of rows in the csv file
     *
     * @access public
     * @return integer
     * @todo Should this remember the position the file was in or something?
     */
    public function count()
    {

        $lines = 0;
        foreach ($this as $row) $lines++;
        $this->rewind();
        return (integer)$lines;

    }

    public function setPosition($position)
    {
        $this->rewind();
        while ($this->position < $position) {
            $this->next();
        }
    }

    function getHeader()
    {
        $original_position = $this->position;
        $this->rewind();
        $current = $this->current();
        $this->setPosition($original_position);
        return $current;
    }


    /**
     * Get the current Csv_Dialect object
     *
     * @return The current Csv_Dialect object
     * @access public
     */
    public function getDialect()
    {

        return $this->dialect;

    }

    /**
     * Change the dialect this csv reader is using
     *
     * @param Csv_Dialect the current Csv_Dialect object
     * @access public
     */
    public function setDialect(Csv_Dialect $dialect)
    {

        $this->dialect = $dialect;

    }

    /**
     *
     *
     * @param boolean
     * @access public
     */
    public function hasHeader($flag = null)
    {

        return $this->dialect->hasHeader();

    }

    /**
     * Use this method if your csv file doesn't have a header row and you want the reader to pretend that it does,
     * pass an array of column names in the order they appear in the csv file and it will return associative arrays
     * with this array as keys
     *
     * @param array An array of column names you would like to use as the "header row"
     * @access public
     */
    public function setHeader($header)
    {

        $row = $this->current();
        if (count($row) != count($header)) throw new Csv_Exception_InvalidHeaderRow('A header row should contain the same amount of columns as the data');
        $this->header = $header;

    }

    function detectDialect($data)
    {

        // if dialect isn't specified in the constructor, the reader will attempt to figure out the format
        $autodetect = new Csv_AutoDetect();
        $dialect = $autodetect->detect($data);
        // @todo Header detection isn't quite right (need to remove the first row from iteration)
        /*if ($autodetect->hasHeader($data)) {
            $this->rewind();
            pre($this->loadRow());
            $this->setHeader($this->loadRow());
        }*/
        return $dialect;

    }

    /**
     * Removes the escape character in front of our quote character
     *
     * @param string The input we are unescaping
     * @param string The key of the item
     * @todo Is the second param necssary? I think it is because array_walk
     */
    function unescape(&$item, $key)
    {

        $item = str_replace($this->dialect->escapechar . $this->dialect->quotechar, $this->dialect->quotechar, $item);

    }

    /**
     * Returns the current row and calls advances internal pointer
     *
     * @access public
     */
    public function getRow()
    {

        $return = $this->current();
        $this->next();
        return $return;

    }

    /** Get a row as an associative array using field names in first row as array key */
    public function getAssociativeRow()
    {
        $row = $this->getRow();
        $header = $this->getHeader();
        return array_combine($header,$row);
    }

    /**
     * Get number of lines that were skipped
     * @todo probably should return an array with actual data instead of just the amount
     */
    public function getSkippedLines()
    {

        return $this->skippedlines;

    }

    /**
     * Returns csv data as an array
     * @todo if reader has been given a header row it is used as keys
     */
    public function toArray()
    {

        $return = array();
        $this->rewind();
        while ($row = $this->getRow()) {
            $return[] = $row;
        }

        // be kinds, please rewind
        $this->rewind();
        return $return;

    }

}