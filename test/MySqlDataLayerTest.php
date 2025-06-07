<?php
declare(strict_types=0);

namespace SetBased\Stratum\MySql\Test;

use SetBased\Stratum\MySql\MySqlDefaultConnector;

/**
 * Test cases for class MySqlDataLayer.
 */
class MySqlDataLayerTest extends DataLayerTestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests for quoteFloat.
   */
  public function testQuoteFloat1()
  {
    $value    = 123.123;
    $expected = '123.123';
    self::assertSame($expected, $this->dataLayer->quoteFloat($value), var_export($value, true));

    $value    = '123.123';
    $expected = '123.123';
    self::assertSame($expected, $this->dataLayer->quoteFloat($value), var_export($value, true));

    $value    = 0;
    $expected = '0';
    self::assertSame($expected, $this->dataLayer->quoteFloat($value), var_export($value, true));

    $value    = '0';
    $expected = '0';
    self::assertSame($expected, $this->dataLayer->quoteFloat($value), var_export($value, true));

    $value    = null;
    $expected = 'null';
    self::assertSame($expected, $this->dataLayer->quoteFloat($value), var_export($value, true));

    $value    = false;
    $expected = '0';
    self::assertSame($expected, $this->dataLayer->quoteFloat($value), var_export($value, true));

    $value    = true;
    $expected = '1';
    self::assertSame($expected, $this->dataLayer->quoteFloat($value), var_export($value, true));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests for quoteInt.
   */
  public function testQuoteInt1()
  {
    $value    = 123;
    $expected = '123';
    self::assertSame($expected, $this->dataLayer->quoteInt($value), var_export($value, true));

    $value    = '123';
    $expected = '123';
    self::assertSame($expected, $this->dataLayer->quoteInt($value), var_export($value, true));

    $value    = 0;
    $expected = '0';
    self::assertSame($expected, $this->dataLayer->quoteInt($value), var_export($value, true));

    $value    = '0';
    $expected = '0';
    self::assertSame($expected, $this->dataLayer->quoteInt($value), var_export($value, true));

    $value    = null;
    $expected = 'null';
    self::assertSame($expected, $this->dataLayer->quoteInt($value), var_export($value, true));

    $value    = false;
    $expected = '0';
    self::assertSame($expected, $this->dataLayer->quoteInt($value), var_export($value, true));

    $value    = true;
    $expected = '1';
    self::assertSame($expected, $this->dataLayer->quoteInt($value), var_export($value, true));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests for quoteString.
   */
  public function testQuoteString1()
  {
    $value    = 123;
    $expected = "'123'";
    self::assertSame($expected, $this->dataLayer->quoteString($value), var_export($value, true));

    $value    = '123';
    $expected = "'123'";
    self::assertSame($expected, $this->dataLayer->quoteString($value), var_export($value, true));

    $value    = 0;
    $expected = "'0'";
    self::assertSame($expected, $this->dataLayer->quoteString($value), var_export($value, true));

    $value    = '0';
    $expected = "'0'";
    self::assertSame($expected, $this->dataLayer->quoteString($value), var_export($value, true));

    $value    = '';
    $expected = 'null';
    self::assertSame($expected, $this->dataLayer->quoteString($value), var_export($value, true));

    $value    = null;
    $expected = 'null';
    self::assertSame($expected, $this->dataLayer->quoteString($value), var_export($value, true));

    $value    = false;
    $expected = 'null';
    self::assertSame($expected, $this->dataLayer->quoteString($value), var_export($value, true));

    $value    = true;
    $expected = "'1'";
    self::assertSame($expected, $this->dataLayer->quoteString($value), var_export($value, true));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests for quoteString.
   */
  public function testQuoteString4()
  {
    $this->expectException(\TypeError::class);
    $this->dataLayer->quoteString($this);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test connectIfNotAlive.
   *
   * @throws \Exception
   */
  public function testReconnect()
  {
    if (array_key_exists('GITHUB_WORKFLOW', $_SERVER))
    {
      $this->markTestSkipped('Can not start stop MySQL or MariaDB server at GitHub');
    }

    $connector = new MySqlDefaultConnector('127.0.0.1', 'test', 'test', 'test');
    $dl        = new TestMySqlDataLayer($connector);

    // Reconnect when not connected.
    $dl->connectIfNotAlive();
    self::assertTrue(true);

    // Reconnect when alive.
    $dl->connectIfNotAlive();
    self::assertTrue(true);

    // Reconnect when server has been gone.
    exec('sudo systemctl restart mariadb');
    $dl->connectIfNotAlive();
    self::assertTrue(true);

    // Reconnect when disconnected.
    $dl->disconnect();
    $dl->connectIfNotAlive();
    self::assertTrue(true);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test connectIfNotAlive.
   *
   * @throws \Exception
   */
  public function testSetConnector()
  {
    $connector1 = new MySqlDefaultConnector('x-localhost', 'x-test', 'x-test', 'x-test');
    $dl         = new TestMySqlDataLayer($connector1);

    $connector2 = new MySqlDefaultConnector('127.0.0.1', 'test', 'test', 'test');
    $dl->setConnector($connector2);
    self::assertTrue(true);

    $this->expectException(\LogicException::class);
    $dl->connect();
    $dl->setConnector($connector1);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------

