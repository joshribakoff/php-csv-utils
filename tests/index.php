<?php

/**
 * CSV Utils Unit Tests
 * 
 * In order to run these tests, you need to have simpletest installed in 
 * your include path somewhere.
 *
 * Special thanks to www.generatedata.com for our csv data
 */

// set_include_path('/path/to/simpletest' . get_include_path());

require_once('../vendor/autoload.php');
set_include_path(
    '../vendor/lastcraft/simpletest/' . PATH_SEPARATOR .
    realpath('../source/') . PATH_SEPARATOR .
    get_include_path()
);
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);

// this is here to help me while I test this library
function pr($data) {

    echo "<pre>";
    var_dump($data);
    echo "</pre>";

}

function pre($data) {

	pr($data);
	exit;

}

function __autoload($className) {

	$paths = explode(PATH_SEPARATOR, get_include_path());
	foreach ($paths as $path) {
		$file = str_replace('_', DIRECTORY_SEPARATOR, $className);
		$filename = $path . DIRECTORY_SEPARATOR . $file . '.php';
		if (file_exists($filename)) {
			require_once $filename;
		}
	}

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
require_once 'unit_tester.php';
require_once 'reporter.php';
require_once 'mock_objects.php';

// include all tests
require_once 'TestCases/Reader.php';
require_once 'TestCases/Writer.php';
require_once 'TestCases/AutoDetect.php';
require_once 'TestCases/Dialect.php';
require_once 'TestCases/Docs.php';

// run tests in html reporter
$test = new TestSuite('Core CSV Utilities Tests');
$test->add(new Test_Of_Csv_Reader);
$test->add(new Test_Of_Csv_Writer);
$test->add(new Test_Of_Csv_AutoDetect);
$test->add(new Test_Of_Csv_Dialect);
$test->add(new Test_Of_Csv_Docs);
if (TextReporter::inCli()) {
    exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());
