<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql;

use SetBased\Stratum\MySql\Exception\MySqlConnectFailedException;

/**
 * Connects to a MySQL or MariaDB instance using user name and password.
 */
class MySqlDefaultConnector implements MySqlConnector
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The connection between PHP and the MySQL instance.
   *
   * @var \mysqli|null
   */
  protected ?\mysqli $mysqli;

  /**
   * @var string
   */
  private string $database;

  /**
   * The hostname.
   *
   * @var string
   */
  private string $host;

  /**
   * The password.
   *
   * @var string
   */
  private string $password;

  /**
   * The port number.
   *
   * @var int
   */
  private int $port;

  /**
   * The MySQL user name.
   *
   * @var string
   */
  private string $user;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param string $host     The hostname.
   * @param string $user     The username.
   * @param string $password The password.
   * @param string $database The default database.
   * @param int    $port     The port number.
   *
   * @since 5.0.0
   * @api
   */
  public function __construct(string $host, string $user, string $password, string $database, int $port = 3306)
  {
    $this->host     = $host;
    $this->user     = $user;
    $this->password = $password;
    $this->database = $database;
    $this->port     = $port;
    $this->mysqli   = null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object destructor.
   *
   * @since 5.0.0
   * @api
   */
  public function __destruct()
  {
    $this->disconnect();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Connects to the MySQL or MariaDB instance.
   *
   * @return \mysqli
   *
   * @throws MySqlConnectFailedException
   *
   * @since 5.0.0
   * @api
   */
  public function connect(): \mysqli
  {
    $this->disconnect();

    $this->mysqli = @new \mysqli($this->host, $this->user, $this->password, $this->database, $this->port);
    if ($this->mysqli->connect_errno)
    {
      $exception    = new MySqlConnectFailedException($this->mysqli->connect_errno,
                                                      $this->mysqli->connect_error,
                                                      'mysqli::__construct');
      $this->mysqli = null;

      throw $exception;
    }

    return $this->mysqli;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * If connected to a MySQL or MariaDB disconnects from the MySQL or MariaDB instance.
   *
   * @since 5.0.0
   * @api
   */
  public function disconnect(): void
  {
    if ($this->mysqli!==null)
    {
      @$this->mysqli->close();
      $this->mysqli = null;
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns true if PHP is (still) connected to a MySQL or MariaDB instance.
   *
   * @return bool
   *
   * @since 5.0.0
   * @api
   */
  public function isAlive(): bool
  {
    if ($this->mysqli===null)
    {
      return false;
    }

    $result = @$this->mysqli->query('select 1');
    if (is_bool($result))
    {
      return false;
    }
    $result->free();

    return true;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
