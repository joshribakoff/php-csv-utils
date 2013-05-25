<?php
class DialectTest extends PHPUnit_Framework_TestCase
{
    public function test_Csv_Dialect_Can_Accept_Options_Param()
    {
        $dialect = new Csv_Dialect(array('delimiter' => "?", 'quotechar' => '`', 'lineterminator' => "\r\n"));
        $this->assertEquals($dialect->delimiter, "?");
        $this->assertEquals($dialect->quotechar, "`");
        $this->assertEquals($dialect->lineterminator, "\r\n");
    }
}