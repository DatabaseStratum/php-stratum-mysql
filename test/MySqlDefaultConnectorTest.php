<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Test;

use PHPUnit\Framework\TestCase;
use SetBased\Stratum\MySql\Exception\MySqlConnectFailedException;
use SetBased\Stratum\MySql\MySqlDefaultConnector;

/**
 * Test cases class MySqlDefaultConnector.
 */
class MySqlDefaultConnectorTest extends TestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test connection to a non-existing server.
   */
  public function testConnectFailed(): void
  {
    $connector = new MySqlDefaultConnector('127.0.0.1', 'no-such-user', 'test', 'test');

    $this->expectException(MySqlConnectFailedException::class);
    $connector->connect();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test disconnect when connect to a server failed.
   */
  public function testDisconnectConnectFailed(): void
  {
    $connector = new MySqlDefaultConnector('127.0.0.1', 'no-such-user', 'test', 'test');
    try
    {
      $connector->connect();
    }
    catch (MySqlConnectFailedException)
    {
      // Ignore error.
    }

    $connector->disconnect();
    self::assertTrue(true);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test disconnect when connected.
   */
  public function testDisconnectConnected(): void
  {
    $connector = new MySqlDefaultConnector('127.0.0.1', 'test', 'test', 'test');
    $connector->connect();
    $connector->disconnect();
    self::assertTrue(true);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test disconnect when server has gone.
   */
  public function testDisconnectNoServer(): void
  {
    if (array_key_exists('GITHUB_WORKFLOW', $_SERVER))
    {
      $this->markTestSkipped('Can not start stop MySQL or MariaDB server at GitHub');
    }

    $connector = new MySqlDefaultConnector('127.0.0.1', 'test', 'test', 'test');
    $connector->connect();

    exec('sudo systemctl stop mariadb');

    $connector->disconnect();
    self::assertTrue(true);

    exec('sudo systemctl start mariadb');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test disconnect when never connected to a server.
   */
  public function testDisconnectNotConnected(): void
  {
    $connector = new MySqlDefaultConnector('127.0.0.1', 'test', 'test', 'test');
    $connector->disconnect();
    self::assertTrue(true);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test isAlive when never disconnected.
   */
  public function testIsAliveDisconnected(): void
  {
    $connector = new MySqlDefaultConnector('127.0.0.1', 'test', 'test', 'test');
    $connector->connect();
    $connector->disconnect();

    $isAlive = $connector->isAlive();
    self::assertFalse($isAlive);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test isAlive with alive connection.
   */
  public function testIsAliveIsAlive(): void
  {
    $connector = new MySqlDefaultConnector('127.0.0.1', 'test', 'test', 'test');

    $connector->connect();
    $isAlive = $connector->isAlive();
    self::assertTrue($isAlive);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test isAlive when server has gone.
   */
  public function testIsAliveNoServer(): void
  {
    if (array_key_exists('GITHUB_WORKFLOW', $_SERVER))
    {
      $this->markTestSkipped('Can not start stop MySQL or MariaDB server at GitHub');
    }

    $connector = new MySqlDefaultConnector('127.0.0.1', 'test', 'test', 'test');
    $connector->connect();

    exec('sudo systemctl stop mariadb');


    $isAlive = $connector->isAlive();
    self::assertFalse($isAlive);

    exec('sudo systemctl start mariadb');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test isAlive when never connected.
   */
  public function testIsAliveNotConnected(): void
  {
    $connector = new MySqlDefaultConnector('127.0.0.1', 'test', 'test', 'test');

    $isAlive = $connector->isAlive();
    self::assertFalse($isAlive);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
