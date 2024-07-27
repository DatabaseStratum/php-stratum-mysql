<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Test;

use PHPUnit\Framework\TestCase;
use SetBased\Stratum\MySql\Exception\MySqlConnectFailedException;
use SetBased\Stratum\MySql\Exception\MySqlDataLayerException;
use SetBased\Stratum\MySql\MySqlDefaultConnector;

/**
 * Parent class for all test cases.
 */
class DataLayerTestCase extends TestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The data layer.
   *
   * @var TestMySqlDataLayer
   */
  protected TestMySqlDataLayer $dataLayer;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns true if the server is a has issues with affected rows.
   *
   * @return bool
   *
   * @throws MySqlDataLayerException
   */
  protected function isMariaDBHasIssuesWithAffectedRows(): bool
  {
    $row = $this->dataLayer->executeRow1("show variables like 'version'");

    if (preg_match('/^10\.[3456].*MariaDB$/', $row['Value'])===1)
    {
      return true;
    }

    if (preg_match('/^10\.11.*MariaDB$/', $row['Value'])===1)
    {
      return true;
    }

    if (preg_match('/^11\.4.*MariaDB$/', $row['Value'])===1)
    {
      return true;
    }

    return false;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Connects to the MySQL server.
   *
   * @throws MySqlConnectFailedException
   * @throws MySqlDataLayerException
   */
  protected function setUp(): void
  {
    $connector       = new MySqlDefaultConnector('127.0.0.1', 'test', 'test', 'test');
    $this->dataLayer = new TestMySqlDataLayer($connector);
    $this->dataLayer->connect();
  }
  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
