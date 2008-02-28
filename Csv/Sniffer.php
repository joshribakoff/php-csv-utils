<?php
/**
 * CSV Utils - Sniffer
 * 
 * This class accepts a sample of csv and attempts to deduce its format. It then
 * can return a Csv_Dialect tailored to that particular csv file
 * Please read the LICENSE file
 * @copyright MC2 Design Group, Inc. <luke@mc2design.com>
 * @author Luke Visinoni <luke@mc2design.com>
 * @package Csv
 * @license GNU Lesser General Public License
 * @version 0.1
 */

/**
 * Attempts to deduce the format of a csv file
 * @package Csv
 */
class Csv_Sniffer
{
    public function __construct() {
    
        // (?:^|\n)(?P<quote>["\']).*?(?P=quote)(?:$|\n)')
    
    }
    
    public function sniff($data) {
        
        list($quote, $delim) = $this->guessQuoteAndDelim($data);
        if (is_null($delim)) {
            $delim = $this->guessDelim($data);
        }
        $dialect = new Csv_Dialect();
        $dialect->delimiter = $delim;
        if (!$quote) {
            $dialect->quotechar = "";
            //$dialect->quoting =
        }
        return $dialect;
    
    }
    
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
    
    protected function guessDelim($data) {

        $data = explode("\n", $data);
        $frequency = array();
        foreach ($data as $row) {
            if (empty($row)) continue;
            $frequency[] = count_chars($row, 1);
        }
        
        $modes = array();
        foreach ($frequency as $line) {
            foreach ($line as $char => $count) {
                //$ord = ord($char);
                if (!isset($modes[$char]) || $count > $modes[$char])
                    $modes[$char] = $count;
            }
        }
        
        $temp = array();
        foreach ($modes as $key => $mode) {
            foreach ($frequency as $line) {
                if (!isset($temp[chr($key)])) $temp[chr($key)] = 0;
                if (isset($line[$key]) && $line[$key] == $mode) $temp[chr($key)]++; 
            }
        }
        
        arsort($temp);
        return key($temp);
    
    }
}

/*
    
        $ascii = array();
        foreach (range(0, 127) as $i) $ascii[] = chr($i);
        $data = explode("\n", $data);
        foreach ($data as $key => $row) {
            if (empty($row)) unset($data[$key]);
        }
        
        $chunkLength = min(array(10, strlen($data)));
        $iteration = 0;
        $charFrequency = array();
        $modes = array();
        $delims = array();
        list($start, $end) = array(0, min(array($chunkLength, count($data))));
        
        while ($start < count($data)) {
            
        }
*/