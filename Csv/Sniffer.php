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
        
    }
    
    public function sniff() {
        return new Csv_Dialect;
    }
}