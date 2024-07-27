<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Test;

/**
 * Test cases for stored routines with designation type none with LOBs.
 */
class NoneWithLobTest extends DataLayerTestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Stored routine with designation type none must return the number of rows affected.
   */
  public function test1()
  {
    $ret = $this->dataLayer->tstTestNoneWithLob(0, 'blob');
    self::assertEquals(0, $ret);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Stored routine with designation type none must return the number of rows affected.
   */
  public function test2()
  {
    // MariaDB 10.3 and above returns the total number of rows affected in the stored routine (not the affected rows
    // of the last statement).
    $expected = ($this->isMariaDBHasIssuesWithAffectedRows()) ? 2 : 1;

    $ret = $this->dataLayer->tstTestNoneWithLob(1, 'blob');
    self::assertEquals($expected, $ret);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Stored routine with designation type none must return the number of rows affected.
   */
  public function test3()
  {
    // MariaDB 10.3 and above returns the total number of rows affected in the stored routine (not the affected rows
    // of the last statement).
    $expected = ($this->isMariaDBHasIssuesWithAffectedRows()) ? 40 : 20;

    $ret = $this->dataLayer->tstTestNoneWithLob(20, 'blob');
    self::assertEquals($expected, $ret);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
