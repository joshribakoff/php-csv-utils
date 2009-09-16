<?php
/**
 * CSV Utils
 * 
 * This is a csv reader - basically it reads a csv file into an array
 * Please read the LICENSE file
 * @copyright Luke Visinoni <luke.visinoni@gmail.com>
 * @author Luke Visinoni <luke.visinoni@gmail.com>
 * @package Csv
 * @license GNU Lesser General Public License
 * @version 0.1
 */
require_once 'Csv/Exception/FileNotFound.php';
require_once 'Csv/AutoDetect.php';
require_once 'Csv/Dialect.php';
require_once 'Csv/Reader/String.php';
/**
 * Provides an easy-to-use interface for reading csv-formatted text files. It
 * makes use of the function fgetcsv. It provides quite a bit of flexibility.
 * You can specify just about everything about how it should read a csv file
 * @package Csv
 * @subpackage Csv_Reader
 */
class Csv_Reader implements Iterator, Countable
{
    /**
     * Maximum row size
     * @todo Should this be editable? maybe change it to a public variable
     */
    const MAX_ROW_SIZE = 4096;
    /**
     * Path to csv file
     * @var string
     * @access protected
     */
    protected $path;
    /**
     * Tells reader how to read the file
     * @var Csv_Dialect
     * @access protected
     */
    protected $dialect;
    /**
     * A handle that points to the file we are reading
     * @var resource
     * @access protected
     */
    protected $handle;
    /**
     * The currently loaded row
     * @var array
     * @access public
     * @todo: Should this be public? I think it might have been required for ArrayIterator to work properly
     */
    public $current;
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
    protected $skippedlines = 0;
    /**
     * An array of values to use as the header row - allows to reference by key
     */
    protected $header = array();
    /**
     * Class constructor
     *
     * @param string Path to csv file we want to open
     * @param Csv_Dialect If a dialect is not provided, Csv_Reader will attempt to guess the file's dialect
     * @throws Csv_Exception_FileNotFound
     */
    public function __construct($path, Csv_Dialect $dialect = null) {
    
        // open the file
        $this->setPath($path);
        $this->handle = fopen($this->path, 'rb');
        if ($this->handle === false) throw new Csv_Exception_FileNotFound('File does not exist or is not readable: "' . $path . '".');
        if (is_null($dialect)) {
            // if dialect isn't specified in the constructor, the reader will attempt to figure out the format
			$data = file_get_contents($this->path);
			$autodetect = new Csv_AutoDetect();
			$dialect = $autodetect->detect($data);
        }
        $this->dialect = $dialect;
        $this->rewind();
    
    }
    /**
     * Deduce the probable line feed for this file
     * 
     * @author Special thanks to Edward on this method
     */
    protected function guessLinefeed($data) {
    
        // get total number of all characters
    	$charcount = count_chars($data);
        // foreach ($charcount as $chr => $amt) print chr($chr) . ": $amt<BR>";
    	$cr = "\r";
    	$lf = "\n";
    	// get total number of each type of line feed
    	$count_cr = $charcount[ord($cr)];
    	$count_lf = $charcount[ord($lf)];
        // if both appear the same amount of times, it's likely that the line feed is \r\n
        // this would be a lot more accurate if it didn't count newlines that were inside quotes
    	if ($count_cr == $count_lf) {
    		return "$cr$lf";
    	}
        // if carriage return is non-existant and line feed appears more than once, its probably a line feed
    	if ($count_cr == 0 && $count_lf > 0) {
    		return "$lf";
    	}
        // if line feed is non-existant and carriage return appears more than once, its probably a carriage return
    	if ($count_lf == 0 && $count_cr > 0) {
    		return "$cr";
    	}
    	// sane default: cr+lf
    	return "$cr$lf";
    
    }
    /**
     * Deduce the probable quoting style for this file
     * 
     * @author Special thanks to Edward on this method
     */
    protected function guessQuotingStyle($data, $quote, $delim, $linefeed) {
    
        // build temporary dialect to traverse through the data
    	$dialect = new Csv_Dialect();
    	$dialect->delimiter = $delim;
    	$dialect->quotechar = $quote;
    	$dialect->lineterminator = $linefeed;
    	$lines = explode($linefeed, $data);
    	$lines_processed = 0;
        // Csv_Reader_String accepts raw csv data as a string
        // there is an issue here because this extends the class we're inside right now
        $reader = new Csv_Reader_String($data, $dialect);
        $quotingstyle_count = array();
        foreach ($reader as $parsedline) {
            do {
                // fetch next line until a non-empty line is found
                $line = array_shift($lines);
            } while (strlen($line) == 0);
            // how many quotes are present in this line?
            $quote_count = substr_count($line, $quote);
            // how many quotes are in the data itself?
            $quotecount_in_data = substr_count(implode("", $parsedline), $quote);
            // how many columns are in this line?
            $column_count = count($parsedline);
            // default quoting style for this line: QUOTE_NONE
            $quotingstyle = Csv_Dialect::QUOTE_NONE;
            // determine this line's quoting style
            if ($quote_count == 0 || $quote_count <= $quotecount_in_data) {
                // there are no quotes, or there are less quotes than the number of quotes in the data
                $quotingstyle = Csv_Dialect::QUOTE_NONE;
            } elseif ($quote_count >= ($column_count * 2)) {
                // the number of quotes is larger than, or equal to, the number of quotes 
                // necessary to quote each column
                $quotingstyle = Csv_Dialect::QUOTE_ALL;
            } elseif ($quote_count >= $quotecount_in_data) {
                // there are more quotes than the number of quotes in the data
                $quotingstyle = Csv_Dialect::QUOTE_MINIMAL;
            }
            if (!array_key_exists($quotingstyle, $quotingstyle_count)) {
                $quotingstyle_count[$quotingstyle] = 0;
            }
            $quotingstyle_count[$quotingstyle]++;
            $lines_processed++;
            if ($lines_processed > 15) {
                // don't process the whole file - stop processing after a while
                break;
            }
        }
        
        // return the quoting style that was used most often
        asort($quotingstyle_count);
        $guess = end(array_keys($quotingstyle_count));
        
    	return $guess;
    
    }
    /**
     * I copied this functionality from python's csv module. Basically, it looks
     * for text enclosed by identical quote characters which are in turn surrounded
     * by identical characters (the probable delimiter). If there is no quotes, the
     * delimiter cannot be determined this way.
     *
     * @param string A piece of sample data used to deduce the format of the csv file
     * @return array An array with the first value being the quote char and the second the delim
     * @access protected
     */
    protected function guessQuoteAndDelim($data) {
    
        $patterns = array();
        $patterns[] = '/([^\w\n"\']) ?(["\']).*?(\2)(\1)/'; 
        $patterns[] = '/(?:^|\n)(["\']).*?(\1)([^\w\n"\']) ?/'; // dont know if any of the regexes starting here work properly
        $patterns[] = '/([^\w\n"\']) ?(["\']).*?(\2)(?:^|\n)/';
        $patterns[] = '/(?:^|\n)(["\']).*?(\2)(?:$|\n)/';
        
        foreach ($patterns as $pattern) {
            if ($nummatches = preg_match_all($pattern, $data, $matches)) {
                if ($matches) break;
            }
        }
        
        if (!$matches) return array("", null); // couldn't guess quote or delim
        
        $quotes = array_count_values($matches[2]);
        arsort($quotes);
        if ($quote = array_shift(array_flip($quotes))) {
            $delims = array_count_values($matches[1]);
            arsort($delims);
            $delim = array_shift(array_flip($delims));
        } else {
            $quote = ""; $delim = null;
        }
        return array($quote, $delim);
    
    }
    /**
     * Get the current Csv_Dialect object
     *
     * @return The current Csv_Dialect object
     * @access public
     */
    public function getDialect() {
    
        return $this->dialect;
    
    }
    /**
     * Change the dialect this csv reader is using
     *
     * @param Csv_Dialect the current Csv_Dialect object
     * @access public
     */
    public function setDialect(Csv_Dialect $dialect) {
    
        $this->dialect = $dialect;
    
    }
    /**
     * 
     * 
     * @param boolean 
     * @access public
     */
    public function hasHeader($flag = null) {
    
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
    public function setHeader($header) {
    
        $row = $this->current();
        if (count($row) != count($header)) throw new Csv_Exception_InvalidHeaderRow('A header row should contain the same amount of columns as the data');
        $this->header = $header;
    
    }
    /**
     * Set the path to the csv file
     *
     * @param string The full path to the file we want to read
     * @access protected
     */
    protected function setPath($path) {
    
        if (file_exists($path)) $this->path = $path;
    
    }
    /**
     * Get the path to the csv file we're reading
     *
     * @return string The path to the file we are reading
     * @access public
     */
    public function getPath() {
    
        return $this->path;
    
    }
    /**
     * Removes the escape character in front of our quote character
     *
     * @param string The input we are unescaping
     * @param string The key of the item
     * @todo Is the second param necssary? I think it is because array_walk
     */
    protected function unescape(&$item, $key) {
    
        $item = str_replace($this->dialect->escapechar.$this->dialect->quotechar, $this->dialect->quotechar, $item);
    
    }
    /**
     * Returns the current row and calls advances internal pointer
     * 
     * @access public
     */
    public function getRow() {
    
        $return = $this->current();
        $this->next();
        return $return;
    
    }
    /**
     * Loads the current row into memory
     * 
     * @access protected
     * @todo Don't use fgetcsv - parse the file manually. I think this would allow much more control
     */
    protected function loadRow() {
    
        if (!$this->current = fgetcsv($this->handle, self::MAX_ROW_SIZE, $this->dialect->delimiter, $this->dialect->quotechar)) {
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
     * Get number of lines that were skipped
     * @todo probably should return an array with actual data instead of just the amount
     */
    public function getSkippedLines() {
    
        return $this->skippedlines;
    
    }
    /**
     * Returns csv data as an array
     * @todo if reader has been given a header row it is used as keys
     */
    public function toArray() {
    
        $return = array();
        $this->rewind();
        while ($row = $this->getRow()) {
            $return[] = $row;
        }
        
        // be kinds, please rewind
        $this->rewind();
        return $return;
    
    }
    /**
     * Get total rows
     *
     * @return integer The number of rows in the file (not includeing line-breaks in the data)
     * @todo Make sure that this is aware of line-breaks in data as opposed to end of row
     * @access public
     */
    public function close() {
    
        if (is_resource($this->handle)) fclose($this->handle);
    
    }
    /**
     * Destructor method - Closes the file handle
     * 
     * @access public
     */
    public function __destruct() {

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
    public function next() {
    
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
    public function valid() {
    
        if (is_resource($this->handle))
            return (boolean) !feof($this->handle);
        
        return false;
    
    }
    /**
     * Returns the current row 
     * 
     * @access public
     * @return array An array of the current row's data
     */
    public function current() {
    
        if (empty($this->header) || !$this->current) return $this->current;
        else return array_combine($this->header, $this->current);
    
    }
    /**
     * Moves the internal pointer to the beginning
     * 
     * @access public
     */
    public function rewind() {
    
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
    public function key() {
    
        return (integer) $this->position;
    
    }
    /**
     * Returns the number of rows in the csv file
     * 
     * @access public
     * @return integer
     * @todo Should this remember the position the file was in or something?
     */
    public function count() {
    
        $lines = 0;
        foreach ($this as $row) $lines++;
        $this->rewind();
        return (integer) $lines;
    
    }
}