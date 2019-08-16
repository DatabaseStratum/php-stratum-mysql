<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Test;

/**
 * Test cases for stored routines with designation type table.
 */
class TableTest extends DataLayerTestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Stored routine with designation type table must show table.
   */
  public function test1()
  {
    $template_table = '
+---------+---------+---------+---------+---------------------+------+------+
| tst_c00 | tst_c01 | tst_c02 | tst_c03 |       tst_c04       |  t   |  s   |
+---------+---------+---------+---------+---------------------+------+------+
| Hello   |       1 |   0.543 | 1.23450 | 2014-03-27 00:00:00 | 4444 |    1 |
| World   |       3 |  3.0E-5 | 0.00000 | 2014-03-28 00:00:00 |      |    1 |
+---------+---------+---------+---------+---------------------+------+------+
';

    ob_start();
    $this->dataLayer->tstTestTable();
    $table = ob_get_contents();
    ob_end_clean();

    self::assertEquals($template_table, $table);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
