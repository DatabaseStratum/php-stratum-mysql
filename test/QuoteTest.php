<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Test;

use SetBased\Stratum\MySql\Exception\MySqlDataLayerException;
use SetBased\Stratum\MySql\Exception\MySqlQueryErrorException;

/**
 * Test cases for quoting variables.
 */
class QuoteTest extends DataLayerTestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test invoking a stored routine with an invalid value for a parameter/column type.
   *
   * @param string $column The column type.
   * @param mixed  $value The invalid value.
   */
  public function genericInvalid(string $column, mixed $value): void
  {
    try
    {
      $this->dataLayer->tstTestTypeHint(($column=='int') ? $value : null,
                                        ($column=='smallint') ? $value : null,
                                        ($column=='tinyint') ? $value : null,
                                        ($column=='mediumint') ? $value : null,
                                        ($column=='bigint') ? $value : null,
                                        ($column=='int_unsigned') ? $value : null,
                                        ($column=='smallint_unsigned') ? $value : null,
                                        ($column=='tinyint_unsigned') ? $value : null,
                                        ($column=='mediumint_unsigned') ? $value : null,
                                        ($column=='bigint_unsigned') ? $value : null,
                                        ($column=='decimal') ? $value : null,
                                        ($column=='decimal0') ? $value : null,
                                        ($column=='float') ? $value : null,
                                        ($column=='double') ? $value : null,
                                        ($column=='bit') ? $value : null,
                                        ($column=='date') ? $value : null,
                                        ($column=='datetime') ? $value : null,
                                        ($column=='timestamp') ? $value : null,
                                        ($column=='time') ? $value : null,
                                        ($column=='year') ? $value : null,
                                        ($column=='char') ? $value : null,
                                        ($column=='varchar') ? $value : null,
                                        ($column=='binary') ? $value : null,
                                        ($column=='varbinary') ? $value : null,
                                        ($column=='enum') ? $value : null,
                                        ($column=='set') ? $value : null);
      self::fail("column: $column, value: $value");
    }
    catch (\TypeError|\RuntimeException $e)
    {
      self::assertTrue(true);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test invoking a stored routine with a valid value for a parameter/column type.
   *
   * @param string $column The column type.
   * @param mixed  $value  The invalid value.
   *
   * @throws MySqlDataLayerException
   * @throws MySqlQueryErrorException
   */
  public function genericValid(string $column, mixed $value): void
  {
    $n = $this->dataLayer->tstTestTypeHint(($column=='int') ? $value : null,
                                           ($column=='smallint') ? $value : null,
                                           ($column=='tinyint') ? $value : null,
                                           ($column=='mediumint') ? $value : null,
                                           ($column=='bigint') ? $value : null,
                                           ($column=='int_unsigned') ? $value : null,
                                           ($column=='smallint_unsigned') ? $value : null,
                                           ($column=='tinyint_unsigned') ? $value : null,
                                           ($column=='mediumint_unsigned') ? $value : null,
                                           ($column=='bigint_unsigned') ? $value : null,
                                           ($column=='decimal') ? $value : null,
                                           ($column=='decimal0') ? $value : null,
                                           ($column=='float') ? $value : null,
                                           ($column=='double') ? $value : null,
                                           ($column=='bit') ? $value : null,
                                           ($column=='date') ? $value : null,
                                           ($column=='datetime') ? $value : null,
                                           ($column=='timestamp') ? $value : null,
                                           ($column=='time') ? $value : null,
                                           ($column=='year') ? $value : null,
                                           ($column=='char') ? $value : null,
                                           ($column=='varchar') ? $value : null,
                                           ($column=='binary') ? $value : null,
                                           ($column=='varbinary') ? $value : null,
                                           ($column=='enum') ? $value : null,
                                           ($column=='set') ? $value : null);
    self::assertEquals(1, $n);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test illegal values will raise an exception.
   */
  public function testInvalid(): void
  {
    $tests = [];

    $tests[] = ['column' => 'int', 'value' => 'abc'];
    $tests[] = ['column' => 'smallint', 'value' => 'abc'];
    $tests[] = ['column' => 'tinyint', 'value' => 'abc'];
    $tests[] = ['column' => 'mediumint', 'value' => 'abc'];
    $tests[] = ['column' => 'bigint', 'value' => 'abc'];

    $tests[] = ['column' => 'int_unsigned', 'value' => 'abc'];
    $tests[] = ['column' => 'smallint_unsigned', 'value' => 'abc'];
    $tests[] = ['column' => 'tinyint_unsigned', 'value' => 'abc'];
    $tests[] = ['column' => 'mediumint_unsigned', 'value' => 'abc'];
    $tests[] = ['column' => 'bigint_unsigned', 'value' => 'abc'];

    $tests[] = ['column' => 'decimal', 'value' => 'abc'];
    $tests[] = ['column' => 'float', 'value' => 'abc'];
    $tests[] = ['column' => 'double', 'value' => 'abc'];

    $tests[] = ['column' => 'bit', 'value' => 'abc'];

    $tests[] = ['column' => 'date', 'value' => 'qwerty'];
    $tests[] = ['column' => 'datetime', 'value' => 'qwerty'];
    $tests[] = ['column' => 'timestamp', 'value' => 'qwerty'];
    $tests[] = ['column' => 'time', 'value' => 'qwerty'];

    $tests[] = ['column' => 'year', 'value' => 'abc'];

    $tests[] = ['column' => 'enum', 'value' => 'c'];
    $tests[] = ['column' => 'set', 'value' => 'c'];

    foreach ($tests as $test)
    {
      $this->genericInvalid($test['column'], $test['value']);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test all column types are quoted properly.
   *
   * @throws MySqlDataLayerException
   * @throws MySqlQueryErrorException
   */
  public function testValid(): void
  {
    $tests = [];

    $tests[] = ['column' => 'int', 'value' => 1];
    $tests[] = ['column' => 'smallint', 'value' => 1];
    $tests[] = ['column' => 'tinyint', 'value' => 1];
    $tests[] = ['column' => 'mediumint', 'value' => 1];
    $tests[] = ['column' => 'bigint', 'value' => 1];

    $tests[] = ['column' => 'int_unsigned', 'value' => 1];
    $tests[] = ['column' => 'smallint_unsigned', 'value' => 1];
    $tests[] = ['column' => 'tinyint_unsigned', 'value' => 1];
    $tests[] = ['column' => 'mediumint_unsigned', 'value' => 1];
    $tests[] = ['column' => 'bigint_unsigned', 'value' => 1];

    $tests[] = ['column' => 'decimal', 'value' => '0.1'];
    $tests[] = ['column' => 'decimal0', 'value' => 1];
    $tests[] = ['column' => 'float', 'value' => 0.1];
    $tests[] = ['column' => 'double', 'value' => 0.1];

    $tests[] = ['column' => 'bit', 'value' => '1010'];

    $tests[] = ['column' => 'date', 'value' => date('Y-m-d')];
    $tests[] = ['column' => 'datetime', 'value' => date('Y-m-d H:i:s')];
    $tests[] = ['column' => 'timestamp', 'value' => date('Y-m-d H:i:s')];
    $tests[] = ['column' => 'time', 'value' => date('H:i:s')];
    $tests[] = ['column' => 'year', 'value' => 2000];

    $tests[] = ['column' => 'char', 'value' => '1234'];
    $tests[] = ['column' => 'char', 'value' => 'abc'];
    $tests[] = ['column' => 'char', 'value' => "0xC8 ' --"];

    $tests[] = ['column' => 'varchar', 'value' => 'abc'];
    $tests[] = ['column' => 'varchar', 'value' => "0xC8 ' --"];

    $tests[] = ['column' => 'binary', 'value' => '1010'];
    $tests[] = ['column' => 'binary', 'value' => "\xFF\x7F\x80\x5c\x00\x10"];
    $tests[] = ['column' => 'varbinary', 'value' => '1010'];
    $tests[] = ['column' => 'varbinary', 'value' => "\xFF\x7F\x80\x5c\x00\x10"];

    $tests[] = ['column' => 'enum', 'value' => 'a'];
    $tests[] = ['column' => 'enum', 'value' => 'b'];

    $tests[] = ['column' => 'set', 'value' => 'a'];
    $tests[] = ['column' => 'set', 'value' => 'b'];

    foreach ($tests as $test)
    {
      $this->genericValid($test['column'], $test['value']);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
