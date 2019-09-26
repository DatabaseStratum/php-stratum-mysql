<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Test;

use SetBased\Stratum\Middle\Exception\ResultException;

/**
 * Test cases for stored routines with designation type singleton1 with LOBs.
 */
class Singleton1WithLobTest extends DataLayerTestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Stored routine with designation type singleton1 must return 1 value and 1 value only.
   */
  public function test01()
  {
    $value = $this->dataLayer->tstTestSingleton1aWithLob(1, 'blob');
    self::assertEquals('1', $value);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * An exception must be thrown when a stored routine with designation type singleton1 returns 0 rows.
   */
  public function test02()
  {
    $this->expectException(ResultException::class);
    $this->dataLayer->tstTestSingleton1aWithLob(0, 'blob');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * An exception must be thrown when a stored routine with designation type singleton1 returns more than 1 row.
   */
  public function test03()
  {
    $this->expectException(ResultException::class);
    $this->dataLayer->tstTestSingleton1aWithLob(2, 'blob');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * An exception must be thrown when a stored routine with designation type singleton1 and return type bool returns
   * 0 rows.
   */
  public function test11()
  {
    $this->expectException(ResultException::class);
    $this->dataLayer->tstTestSingleton1bWithLob(0, 1, 'blob');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Stored routine with designation type singleton0 and return type bool must return false when selecting 1 row
   * with null value.
   */
  public function test12()
  {
    $value = $this->dataLayer->tstTestSingleton1bWithLob(1, null, 'blob');
    self::assertFalse($value);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Stored routine with designation type singleton0 and return type bool must return false when selecting 1 row
   * with empty value.
   */
  public function test13()
  {
    $value = $this->dataLayer->tstTestSingleton1bWithLob(1, 0, 'blob');
    self::assertFalse($value);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Stored routine with designation type singleton0 and return type bool must return true when selecting 1 row
   * with non-empty value.
   */
  public function test14()
  {
    $value = $this->dataLayer->tstTestSingleton1bWithLob(1, 123, 'blob');
    self::assertTrue($value);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * An exception must be thrown when a stored routine with designation type singleton0 and return type bool
   * returns more than 1 row.
   */
  public function test15()
  {
    $this->expectException(ResultException::class);
    $this->dataLayer->tstTestSingleton1bWithLob(2, 1, 'blob');
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------

