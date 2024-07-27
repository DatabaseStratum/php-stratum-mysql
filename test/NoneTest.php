<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Test;

/**
 * Test cases for stored routines with designation type none.
 */
class NoneTest extends DataLayerTestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Stored routine with designation type none must return the number of rows affected.
   */
  public function test1()
  {
    $ret = $this->dataLayer->tstTestNone(0);
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

    $ret = $this->dataLayer->tstTestNone(1);
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

    $ret = $this->dataLayer->tstTestNone(20);
    self::assertEquals($expected, $ret);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
