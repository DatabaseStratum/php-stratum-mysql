<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Test;

/**
 * Test case for insert multiple.
 */
class InsertMultipleTest extends DataLayerTestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test number of rows inserted is corrected. All columns type supported.
   */
  public function test1()
  {
    $data = [];

    $data[] = ['field_int'                => 1,
               'field_smallint'           => 1,
               'field_mediumint'          => 1,
               'field_tinyint'            => 1,
               'field_bigint'             => 1,
               'field_int_unsigned'       => 1,
               'field_smallint_unsigned'  => 1,
               'field_mediumint_unsigned' => 1,
               'field_tinyint_unsigned'   => 1,
               'field_bigint_unsigned'    => 1,
               'field_year'               => 2000,
               'field_decimal'            => '0.1',
               'field_decimal0'           => '1234567890123456789012345678901234567890',
               'field_float'              => 0.1,
               'field_double'             => 0.1,
               'field_binary'             => '1010',
               'field_varbinary'          => '1010',
               'field_char'               => 'abc',
               'field_varchar'            => 'abc',
               'field_time'               => date('H:i:s'),
               'field_timestamp'          => date('Y-m-d H:i:s'),
               'field_date'               => date('Y-m-d'),
               'field_datetime'           => date('Y-m-d H:i:s'),
               'field_enum'               => 'a',
               'field_set'                => 'a',
               'field_bit'                => '1010'];

    $data[] = ['field_int'                => 2,
               'field_smallint'           => 2,
               'field_mediumint'          => 2,
               'field_tinyint'            => 2,
               'field_bigint'             => 2,
               'field_int_unsigned'       => 2,
               'field_smallint_unsigned'  => 2,
               'field_mediumint_unsigned' => 2,
               'field_tinyint_unsigned'   => 2,
               'field_bigint_unsigned'    => 2,
               'field_year'               => 2001,
               'field_decimal'            => '0.2',
               'field_decimal0'           => 123456,
               'field_float'              => 0.2,
               'field_double'             => 0.2,
               'field_binary'             => '1010',
               'field_varbinary'          => '1010',
               'field_char'               => 'abc',
               'field_varchar'            => 'abc',
               'field_time'               => date('H:i:s'),
               'field_timestamp'          => date('Y-m-d H:i:s'),
               'field_date'               => date('Y-m-d'),
               'field_datetime'           => date('Y-m-d H:i:s'),
               'field_enum'               => 'b',
               'field_set'                => 'b',
               'field_bit'                => '1010'];

    $data[] = ['field_int'                => 3,
               'field_smallint'           => 3,
               'field_mediumint'          => 3,
               'field_tinyint'            => 3,
               'field_bigint'             => 3,
               'field_int_unsigned'       => 3,
               'field_smallint_unsigned'  => 3,
               'field_mediumint_unsigned' => 3,
               'field_tinyint_unsigned'   => 3,
               'field_bigint_unsigned'    => 3,
               'field_year'               => 2002,
               'field_decimal'            => '0.3',
               'field_decimal0'           => '3',
               'field_float'              => 0.3,
               'field_double'             => 0.3,
               'field_binary'             => '1010',
               'field_varbinary'          => '1010',
               'field_char'               => 'abc',
               'field_varchar'            => 'abc',
               'field_time'               => date('H:i:s'),
               'field_timestamp'          => date('Y-m-d H:i:s'),
               'field_date'               => date('Y-m-d'),
               'field_datetime'           => date('Y-m-d H:i:s'),
               'field_enum'               => 'c',
               'field_set'                => 'c',
               'field_bit'                => '1010'];

    $data[] = ['field_int'                => 4,
               'field_smallint'           => 1,
               'field_mediumint'          => 1,
               'field_tinyint'            => 1,
               'field_bigint'             => 1,
               'field_int_unsigned'       => 4,
               'field_smallint_unsigned'  => 4,
               'field_mediumint_unsigned' => 4,
               'field_tinyint_unsigned'   => 4,
               'field_bigint_unsigned'    => 4,
               'field_year'               => 2000,
               'field_decimal'            => '0.1',
               'field_decimal0'           => 1.0,
               'field_float'              => 0.1,
               'field_double'             => 0.1,
               'field_binary'             => '1010',
               'field_varbinary'          => '1010',
               'field_char'               => 'abc',
               'field_varchar'            => 'abc',
               'field_time'               => date('H:i:s'),
               'field_timestamp'          => date('Y-m-d H:i:s'),
               'field_date'               => date('Y-m-d'),
               'field_datetime'           => date('Y-m-d H:i:s'),
               'field_enum'               => 'c',
               'field_set'                => 'a,b',
               'field_bit'                => '1010'];

    $this->dataLayer->tstTestInsertMultiple01($data);

    $query = 'SELECT count(*) FROM `TST_TEMPO`';
    $ret   = $this->dataLayer->executeSingleton1($query);

    self::assertEquals(4, $ret);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for empty fields.
   */
  public function test2()
  {
    $data   = [];
    $data[] = ['field1' => 1,
               'field4' => 1,
               'field5' => 1];

    $this->dataLayer->tstTestInsertMultiple02($data);

    $query = 'SELECT count(*) FROM `TST_TEMPO`';
    $ret   = $this->dataLayer->executeSingleton1($query);

    self::assertEquals(1, $ret);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
