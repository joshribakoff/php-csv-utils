<?php
/**
 * CSV Utils Unit Tests
 * 
 * In order to run these tests, you need to have simpletest installed in 
 * your include path somewhere.
 *
 * Special thanks to www.generatedata.com for our test data
 */

// set_include_path('/path/to/simpletest' . PATH_SEPARATOR . get_include_path());
set_include_path(realpath('../') . PATH_SEPARATOR . get_include_path());
error_reporting(E_ALL);
ini_set('display_errors', 1);

// this is here to help me while I test this library
function pr($data) {

    echo "<pre>";
    var_dump($data);
    echo "</pre>";

}

function make_table($headers, $rows) {
    echo "<table border=\"1\">\n";
    echo " <tr><th>#</th>\n";
    foreach ($headers as $header) {
        printf("  <th>%s</th>", $header);
    }
    echo " </tr>";
    foreach ($rows as $line => $row) {
        echo " <tr>\n";
        printf("  <td>%s</td>", $line);
        foreach ($row as $column) {
            printf("  <td>%s</td>", $column);
        }
        echo " </tr>\n";
    }
    echo "</table>\n";
}

// include simpletest classes
require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

// include all tests
require_once 'TestCases/Reader.php';
// require_once 'TestCases/Writer.php';
// require_once 'TestCases/AutoDetect.php';
// require_once 'TestCases/Dialect.php';
// require_once 'TestCases/Docs.php';

// run tests in html reporter
$test = new GroupTest('Core CSV Utilities Tests');
$test->addTestCase(new Test_Of_Csv_Reader);
// $test->addTestCase(new Test_Of_Csv_Writer);
// $test->addTestCase(new Test_Of_Csv_AutoDetect);
// $test->addTestCase(new Test_Of_Csv_Dialect);
// $test->addTestCase(new Test_Of_Csv_Docs);
if (TextReporter::inCli()) {
    exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());