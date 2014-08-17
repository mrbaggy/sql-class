<?php

namespace duncan3dc\SqlClass;

class ResultTest extends \PHPUnit_Framework_TestCase
{
    private $sql;


    public function __construct()
    {
        $database = "/tmp/phpunit_" . microtime(true) . ".sqlite";
        if (file_exists($database)) {
            unlink($database);
        }

        $this->sql = new \duncan3dc\SqlClass\Sql([
            "mode"      =>  "sqlite",
            "database"  =>  "/tmp/phpunit.sqlite",
        ]);

        $this->sql->attachDatabase($database, "test1");

        $this->sql->definitions([
            "table1"    =>  "test1",
            "table2"    =>  "test1",
        ]);

        $this->sql->connect();

        $this->sql->query("CREATE TABLE test1.table1 (field1 VARCHAR(10), field2 INT)");
    }


    public function __destruct()
    {

        unset($this->sql);
    }


    public function testCount()
    {
        $this->sql->insert("table1", ["field1" => "row1"]);
        $this->sql->insert("table1", ["field1" => "row2"]);

        $result = $this->sql->selectAll("table1", Sql::NO_WHERE_CLAUSE);
        $this->assertSame(2, $result->count());
    }

    public function testColumnCount()
    {
        $result = $this->sql->selectAll("table1", Sql::NO_WHERE_CLAUSE);
        $this->assertSame(2, $result->columnCount());
    }

    public function testFetchAssoc1()
    {
        $this->sql->insert("table1", ["field1" => "row1"]);
        $result = $this->sql->selectAll("table1", Sql::NO_WHERE_CLAUSE);
        $this->assertSame("row1", $result->fetch()["field1"]);
    }

    public function testFetchAssoc2()
    {
        $this->sql->insert("table1", ["field1" => "row1"]);
        $result = $this->sql->selectAll("table1", Sql::NO_WHERE_CLAUSE);
        $this->assertSame("row1", $result->fetch(Sql::FETCH_ASSOC)["field1"]);
    }

    public function testFetchAssoc3()
    {
        $this->sql->insert("table1", ["field1" => "row1"]);
        $result = $this->sql->selectAll("table1", Sql::NO_WHERE_CLAUSE);
        $result->fetchStyle(Sql::FETCH_ASSOC);
        $this->assertSame("row1", $result->fetch()["field1"]);
    }

    public function testFetchRow1()
    {
        $this->sql->insert("table1", ["field1" => "row1"]);
        $result = $this->sql->selectAll("table1", Sql::NO_WHERE_CLAUSE);
        $this->assertSame("row1", $result->fetch(true)[0]);
    }

    public function testFetchRow2()
    {
        $this->sql->insert("table1", ["field1" => "row1", "field2" => "ok"]);
        $result = $this->sql->selectAll("table1", Sql::NO_WHERE_CLAUSE);
        $this->assertSame("ok", $result->fetch(1)[1]);
    }

    public function testFetchRow3()
    {
        $this->sql->insert("table1", ["field1" => "row1", "field2" => "ok"]);
        $result = $this->sql->selectAll("table1", Sql::NO_WHERE_CLAUSE);
        $this->assertSame("ok", $result->fetch(Sql::FETCH_ROW)[1]);
    }

    public function testFetchRow4()
    {
        $this->sql->insert("table1", ["field1" => "row1", "field2" => "ok"]);
        $result = $this->sql->selectAll("table1", Sql::NO_WHERE_CLAUSE);
        $result->fetchStyle(Sql::FETCH_ROW);
        $this->assertSame("ok", $result->fetch()[1]);
    }
}