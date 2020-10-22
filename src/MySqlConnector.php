<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql;

use SetBased\Stratum\MySql\Exception\MySqlConnectFailedException;

/**
 * Interface for classes for connecting to a MySQL or MariaDB instance.
 */
interface MySqlConnector
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Connects to a MySQL or MariaDB instance.
   *
   * @return \mysqli
   *
   * @throws MySqlConnectFailedException
   *
   * @since 5.0.0
   * @api
   */
  public function connect(): \mysqli;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * If connected to a MySQL or MariaDB disconnects from the MySQL or MariaDB instance.
   *
   * This method will never throw an exception.
   *
   * @since 5.0.0
   * @api
   */
  public function disconnect(): void;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns true if PHP is (still) connected to a MySQL or MariaDB instance. Otherwise returns false.
   *
   * This method will never throw an exception.
   *
   * @return bool
   *
   * @since 5.0.0
   * @api
   */
  public function isAlive(): bool;

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
