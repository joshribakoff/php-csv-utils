<?php
set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../../'));
require_once 'Csv/Reader.php';
require_once 'Csv/Dialect/Excel.php';

try {

    $dialect = new Csv_Dialect_Excel;
    $dialect->delimiter = "\t";
    $reader = new Csv_Reader("../data/tab-200.csv", $dialect);
    $headers = $reader->current();
    echo '<table border="1">';
    foreach ($headers as $column) printf("<th>%s</th>", !empty($column) ? $column : "&nbsp;");
    while ($row = $reader->current()) {
    
        echo "<tr>\r\n";
        foreach ($row as $column) printf("<td style='padding: .5em;'>%s</td>", !empty($column) ? $column : "&nbsp;");
        echo "</tr>\r\n";
    
    }
    echo "</table>";
    
} catch (Exception $e) {

    printf("Error: %s", $e->getMessage());

}