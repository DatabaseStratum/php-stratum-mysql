<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Test;

use SetBased\Stratum\MySql\Exception\MySqlConnectFailedException;
use SetBased\Stratum\MySql\Exception\MySqlDataLayerException;

/**
 * Test cases for stored routines inet4 data types.
 */
class Inet4Test extends DataLayerTestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritDoc
   *
   * @throws MySqlDataLayerException
   * @throws MySqlConnectFailedException
   */
  public function setUp(): void
  {
    parent::setUp();

    if (!file_exists('test/psql/inet4'))
    {
      $this->markTestSkipped('Server does not have inet4 column type.');
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test invoking a stored function.
   *
   * @throws MySqlDataLayerException
   */
  public function testInet4()
  {
    $this->dataLayer->tstInet4InsertInet4('0.0.0.0');
    self::assertTrue(true);

    $this->dataLayer->tstInet4InsertInet4('127.0.0.1');
    self::assertTrue(true);

    $this->dataLayer->tstInet4InsertInet4('1.1.1.1');
    self::assertTrue(true);

    $this->dataLayer->tstInet4InsertInet4('');
    self::assertTrue(true);

    $this->dataLayer->tstInet4InsertInet4(null);
    self::assertTrue(true);

    $addresses = $this->dataLayer->tstInet4GetAll();
    self::assertCount(5, $addresses);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------

