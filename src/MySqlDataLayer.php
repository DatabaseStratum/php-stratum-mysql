<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql;

use SetBased\Exception\FallenException;
use SetBased\Exception\LogicException;
use SetBased\Stratum\Middle\BulkHandler;
use SetBased\Stratum\Middle\Exception\ResultException;
use SetBased\Stratum\MySql\Exception\MySqlConnectFailedException;
use SetBased\Stratum\MySql\Exception\MySqlDataLayerException;
use SetBased\Stratum\MySql\Exception\MySqlQueryErrorException;

/**
 * Supper class for routine wrapper classes.
 */
class MySqlDataLayer
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The default character set to be used when sending data from and to the MySQL instance.
   *
   * @var string
   *
   * @since 1.0.0
   * @api
   */
  public string $charSet = 'utf8mb4';

  /**
   * Whether queries must be logged.
   *
   * @var bool
   *
   * @since 1.0.0
   * @api
   */
  public bool $logQueries = false;

  /**
   * The options to be set.
   *
   * @var array
   */
  public array $options = [MYSQLI_OPT_INT_AND_FLOAT_NATIVE => true];

  /**
   * The SQL mode of the MySQL instance.
   *
   * @var string
   *
   * @since 1.0.0
   * @api
   */
  public string $sqlMode = 'STRICT_ALL_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_VALUE_ON_ZERO,NO_ENGINE_SUBSTITUTION,NO_ZERO_DATE,NO_ZERO_IN_DATE,ONLY_FULL_GROUP_BY';

  /**
   * The transaction isolation level. Possible values are:
   * <ul>
   * <li> REPEATABLE READ
   * <li> READ COMMITTED
   * <li> READ UNCOMMITTED
   * <li> SERIALIZABLE
   * </ul>
   *
   * @var string
   *
   * @since 1.0.0
   * @api
   */
  public string $transactionIsolationLevel = 'READ COMMITTED';

  /**
   * Chunk size when transmitting LOB to the MySQL instance. Must be less than max_allowed_packet.
   *
   * @var int
   */
  protected int $chunkSize;

  /**
   * Value of variable max_allowed_packet
   *
   * @var int|null
   */
  protected ?int $maxAllowedPacket = null;

  /**
   * The connection between PHP and the MySQL or MariaDB instance.
   *
   * @var \mysqli|null
   */
  protected ?\mysqli $mysqli = null;

  /**
   * The query log.
   *
   * @var array[]
   */
  protected array $queryLog = [];

  /**
   * The object for connecting to a MySQL or MariaDB instance.
   *
   * @var MySqlConnector
   */
  private MySqlConnector $connector;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * MySqlDataLayer constructor.
   *
   * @param MySqlConnector $connector The object for connecting to a MySQL or MariaDB instance.
   */
  public function __construct(MySqlConnector $connector)
  {
    $this->connector = $connector;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Starts a transaction.
   *
   * MysqlWrapper around [mysqli::autocommit](http://php.net/manual/mysqli.autocommit.php), however on failure an
   * exception is thrown.
   *
   * @throws MySqlDataLayerException
   *
   * @api
   * @since 1.0.0
   */
  public function begin(): void
  {
    $success = @$this->mysqli->autocommit(false);
    if (!$success)
    {
      throw $this->dataLayerError('mysqli::autocommit');
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @param \mysqli_stmt $stmt
   * @param array        $out
   *
   * @throws MySqlDataLayerException
   */
  public function bindAssoc(\mysqli_stmt $stmt, array &$out): void
  {
    $data = $stmt->result_metadata();
    if (!$data)
    {
      throw $this->dataLayerError('mysqli_stmt::result_metadata');
    }

    $fields = [];
    $out    = [];

    while (($field = $data->fetch_field()))
    {
      $fields[] = &$out[$field->name];
    }

    $b = call_user_func_array([$stmt, 'bind_result'], $fields);
    if ($b===false)
    {
      throw $this->dataLayerError('mysqli_stmt::bind_result');
    }

    $data->free();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Commits the current transaction (and starts a new transaction).
   *
   * MysqlWrapper around [mysqli::commit](http://php.net/manual/mysqli.commit.php), however on failure an exception is
   * thrown.
   *
   * @throws MySqlDataLayerException
   *
   * @api
   * @since 1.0.0
   */
  public function commit(): void
  {
    $success = @$this->mysqli->commit();
    if (!$success)
    {
      throw $this->dataLayerError('mysqli::commit');
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Connects PHP to the MySQL instance.
   *
   * @throws MySqlConnectFailedException
   * @throws MySqlDataLayerException
   *
   * @since 1.0.0
   * @api
   */
  public function connect(): void
  {
    $this->mysqli = $this->connector->connect();

    // Set the options.
    foreach ($this->options as $option => $value)
    {
      $success = @$this->mysqli->options($option, $value);
      if (!$success)
      {
        throw $this->dataLayerError('mysqli::options');
      }
    }

    // Set the default character set.
    $success = @$this->mysqli->set_charset($this->charSet);
    if (!$success)
    {
      throw $this->dataLayerError('mysqli::set_charset');
    }

    // Set the SQL mode.
    $this->executeNone("set sql_mode = '".$this->sqlMode."'");

    // Set transaction isolation level.
    $this->executeNone('set session transaction isolation level '.$this->transactionIsolationLevel);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Connects or reconnects to the MySQL instance when PHP is not (longer) connected to a MySQL or MariaDB instance.
   *
   * @throws MySqlConnectFailedException
   * @throws MySqlDataLayerException
   *
   * @since 5.0.0
   * @api
   */
  public function connectIfNotAlive(): void
  {
    if (!$this->connector->isAlive())
    {
      $this->mysqli = $this->connector->connect();
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Closes the connection to the MySQL instance, if connected.
   *
   * @since 1.0.0
   * @api
   */
  public function disconnect(): void
  {
    $this->mysqli = null;
    $this->connector->disconnect();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes a query using a bulk handler.
   *
   * @param BulkHandler $bulkHandler The bulk handler.
   * @param string      $query       The SQL statement.
   *
   * @throws MySqlQueryErrorException
   *
   * @api
   * @since 1.0.0
   */
  public function executeBulk(BulkHandler $bulkHandler, string $query): void
  {
    $this->realQuery($query);

    $bulkHandler->start();

    $result = $this->mysqli->use_result();
    while (($row = $result->fetch_assoc()))
    {
      $bulkHandler->row($row);
    }
    $result->free();

    $bulkHandler->stop();

    if ($this->mysqli->more_results())
    {
      $this->mysqli->next_result();
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes a query and logs the result set.
   *
   * @param string $queries The query or multi query.
   *
   * @return int The total number of rows selected/logged.
   *
   * @throws MySqlDataLayerException
   *
   * @api
   * @since 1.0.0
   */
  public function executeLog(string $queries): int
  {
    // Counter for the number of rows written/logged.
    $n = 0;

    $this->multiQuery($queries);
    do
    {
      $result = @$this->mysqli->store_result();
      if ($this->mysqli->errno)
      {
        throw $this->dataLayerError('mysqli::store_result');
      }
      if ($result)
      {
        $fields = $result->fetch_fields();
        while (($row = $result->fetch_row()))
        {
          $line = '';
          foreach ($row as $i => $field)
          {
            if ($i>0)
            {
              $line .= ' ';
            }
            $line .= str_pad((string)$field, $fields[$i]->max_length);
          }
          echo date('Y-m-d H:i:s'), ' ', $line, "\n";
          $n++;
        }
        $result->free();
      }

      $continue = $this->mysqli->more_results();
      if ($continue)
      {
        $success = @$this->mysqli->next_result();
        if (!$success)
        {
          throw $this->dataLayerError('mysqli::next_result');
        }
      }
    } while ($continue);

    return $n;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes multiple queries and returns an array with the "result" of each query, i.e. the length of the returned
   * array equals the number of queries. For SELECT, SHOW, DESCRIBE or EXPLAIN queries the "result" is the selected
   * rows (i.e. an array of arrays), for other queries the "result" is the number of effected rows.
   *
   * @param string $queries The SQL statements.
   *
   * @return array
   *
   * @throws MySqlDataLayerException
   *
   * @api
   * @since 1.0.0
   */
  public function executeMulti(string $queries): array
  {
    $ret = [];

    $this->multiQuery($queries);
    do
    {
      $result = $this->mysqli->store_result();
      if ($this->mysqli->errno)
      {
        throw $this->dataLayerError('mysqli::store_result');
      }
      if ($result)
      {
        $ret[] = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
      }
      else
      {
        $ret[] = $this->mysqli->affected_rows;
      }

      $continue = $this->mysqli->more_results();
      if ($continue)
      {
        $success = @$this->mysqli->next_result();
        if (!$success)
        {
          throw $this->dataLayerError('mysqli::next_result');
        }
      }
    } while ($continue);

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes a query that does not select any rows.
   *
   * @param string $query The SQL statement.
   *
   * @return int The number of affected rows (if any).
   *
   * @throws MySqlQueryErrorException
   *
   * @api
   * @since 1.0.0
   */
  public function executeNone(string $query): int
  {
    $this->realQuery($query);

    $n = $this->mysqli->affected_rows;

    if ($this->mysqli->more_results())
    {
      $this->mysqli->next_result();
    }

    return $n;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes a query that returns 0 or 1 row.
   * Throws an exception if the query selects 2 or more rows.
   *
   * @param string $query The SQL statement.
   *
   * @return array|null The selected row.
   *
   * @throws MySqlQueryErrorException
   * @throws ResultException
   *
   * @api
   * @since 1.0.0
   */
  public function executeRow0(string $query): ?array
  {
    $result = $this->query($query);
    $row    = $result->fetch_assoc();
    $n      = $result->num_rows;
    $result->free();

    if ($this->mysqli->more_results())
    {
      $this->mysqli->next_result();
    }

    if (!($n==0 || $n==1))
    {
      throw new ResultException([0, 1], $n, $query);
    }

    return $row;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes a query that returns 1 and only 1 row.
   * Throws an exception if the query selects none, 2 or more rows.
   *
   * @param string $query The SQL statement.
   *
   * @return array The selected row.
   *
   * @throws MySqlQueryErrorException
   * @throws ResultException
   *
   * @api
   * @since 1.0.0
   */
  public function executeRow1(string $query): array
  {
    $result = $this->query($query);
    $row    = $result->fetch_assoc();
    $n      = $result->num_rows;
    $result->free();

    if ($this->mysqli->more_results())
    {
      $this->mysqli->next_result();
    }

    if ($n!=1)
    {
      throw new ResultException([1], $n, $query);
    }

    return $row;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes a query that returns 0 or more rows.
   *
   * @param string $query The SQL statement.
   *
   * @return array[] The selected rows.
   *
   * @throws MySqlQueryErrorException
   *
   * @api
   * @since 1.0.0
   */
  public function executeRows(string $query): array
  {
    $result = $this->query($query);
    $rows   = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();

    if ($this->mysqli->more_results())
    {
      $this->mysqli->next_result();
    }

    return $rows;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes a query that returns 0 or 1 row with one column.
   * Throws an exception if the query selects 2 or more rows.
   *
   * @param string $query The SQL statement.
   *
   * @return mixed The selected value.
   *
   * @throws MySqlQueryErrorException
   * @throws ResultException
   *
   * @api
   * @since 1.0.0
   */
  public function executeSingleton0(string $query): mixed
  {
    $result = $this->query($query);
    $row    = $result->fetch_array(MYSQLI_NUM);
    $n      = $result->num_rows;
    $result->free();

    if ($this->mysqli->more_results())
    {
      $this->mysqli->next_result();
    }

    if ($n==0)
    {
      return null;
    }

    if ($n==1)
    {
      return $row[0];
    }

    throw new ResultException([0, 1], $n, $query);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes a query that returns 1 and only 1 row with 1 column.
   * Throws an exception if the query selects none, 2 or more rows.
   *
   * @param string $query The SQL statement.
   *
   * @return mixed The selected value.
   *
   * @throws MySqlQueryErrorException
   * @throws ResultException
   *
   * @api
   * @since 1.0.0
   */
  public function executeSingleton1(string $query): mixed
  {
    $result = $this->query($query);
    $row    = $result->fetch_array(MYSQLI_NUM);
    $n      = $result->num_rows;
    $result->free();

    if ($this->mysqli->more_results())
    {
      $this->mysqli->next_result();
    }

    if ($n!=1)
    {
      throw new ResultException([1], $n, $query);
    }

    return $row[0];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes a query and shows the data in formatted in a table (like mysql's default pager) of in multiple tables
   * (in case of a multi query).
   *
   * @param string $query The query.
   *
   * @return int The total number of rows in the tables.
   *
   * @throws MySqlDataLayerException
   *
   * @api
   * @since 1.0.0
   */
  public function executeTable(string $query): int
  {
    $rowCount = 0;

    $this->multiQuery($query);
    do
    {
      $result = @$this->mysqli->store_result();
      if ($this->mysqli->errno)
      {
        throw $this->dataLayerError('mysqli::store_result');
      }
      if ($result)
      {
        $columns = [];

        // Get metadata to array.
        foreach ($result->fetch_fields() as $key => $column)
        {
          $columns[$key]['header'] = $column->name;
          $columns[$key]['type']   = $column->type;
          switch ($column->type)
          {
            case 12:
              $length = 19;
              break;

            default:
              $length = $column->max_length;
          }
          $columns[$key]['length'] = max(4, $length, mb_strlen($column->name));
        }

        // Show the table header.
        $this->executeTableShowHeader($columns);

        // Show for all rows all columns.
        while (($row = $result->fetch_row()))
        {
          $rowCount++;

          // First row separator.
          echo '|';

          foreach ($row as $i => $value)
          {
            $this->executeTableShowTableColumn($columns[$i], $value);
            echo '|';
          }

          echo "\n";
        }

        // Show the table footer.
        $this->executeTableShowFooter($columns);
      }

      $continue = $this->mysqli->more_results();
      if ($continue)
      {
        $result = @$this->mysqli->next_result();
        if (!$result)
        {
          throw $this->dataLayerError('mysqli::next_result');
        }
      }
    } while ($continue);

    return $rowCount;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the value of the MySQL variable max_allowed_packet.
   *
   * @return int
   *
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function getMaxAllowedPacket(): int
  {
    if ($this->maxAllowedPacket===null)
    {
      $query            = "show variables like 'max_allowed_packet'";
      $maxAllowedPacket = $this->executeRow1($query);

      $this->maxAllowedPacket = (int)$maxAllowedPacket['Value'];

      // Note: When setting $chunkSize equal to $maxAllowedPacket it is not possible to transmit a LOB
      // with size $maxAllowedPacket bytes (but only $maxAllowedPacket - 8 bytes). But when setting the size of
      // $chunkSize less than $maxAllowedPacket than it is possible to transmit a LOB with size
      // $maxAllowedPacket bytes.
      $this->chunkSize = (int)min($this->maxAllowedPacket - 8, 1024 * 1024);
    }

    return $this->maxAllowedPacket;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the query log.
   *
   * To enable the query log set {@link $queryLog} to true.
   *
   * @return array[]
   *
   * @since 1.0.0
   * @api
   */
  public function getQueryLog(): array
  {
    return $this->queryLog;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether PHP is (still) connected to a MySQL or MariaDB instance.
   *
   * This method will never throw an exception.
   *
   * @return bool
   *
   * @since 5.0.0
   * @api
   */
  public function isAlive(): bool
  {
    return $this->connector->isAlive();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a hexadecimal literal for a binary value that can be safely used in SQL statements.
   *
   * @param string|null $value The binary value.
   *
   * @return string
   */
  public function quoteBinary(?string $value): string
  {
    if ($value===null || $value==='')
    {
      return 'null';
    }

    return '0x'.bin2hex($value);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a literal for a bit value that can be safely used in SQL statements.
   *
   * @param string|null $bits The bit value.
   *
   * @return string
   */
  public function quoteBit(?string $bits): string
  {
    if ($bits===null || $bits==='')
    {
      return 'null';
    }

    return "b'".$this->mysqli->real_escape_string($bits)."'";
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a literal for a decimal value that can be safely used in SQL statements.
   *
   * @param int|float|string|null $value The value.
   *
   * @return string
   */
  public function quoteDecimal(int|float|string|null $value): string
  {
    if ($value===null || $value==='')
    {
      return 'null';
    }

    if (is_int($value) || is_float($value))
    {
      return (string)$value;
    }

    return "'".$this->mysqli->real_escape_string($value)."'";
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a literal for a float value that can be safely used in SQL statements.
   *
   * @param float|null $value The float value.
   *
   * @return string
   */
  public function quoteFloat(?float $value): string
  {
    if ($value===null)
    {
      return 'null';
    }

    return (string)$value;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a literal for an integer value that can be safely used in SQL statements.
   *
   * @param int|null $value The integer value.
   *
   * @return string
   */
  public function quoteInt(?int $value): string
  {
    if ($value===null)
    {
      return 'null';
    }

    return (string)$value;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a literal for an expression with a separated list of integers that can be safely used in SQL
   * statements. Throws an exception if the value is a list of integers.
   *
   * @param array|string|null $list      The list of integers.
   * @param string            $delimiter The field delimiter (one character only).
   * @param string            $enclosure The field enclosure character (one character only).
   * @param string            $escape    The escape character (one character only)
   *
   * @return string
   *
   * @throws LogicException
   */
  public function quoteListOfInt(array|string|null $list, string $delimiter, string $enclosure, string $escape): string
  {
    if ($list===null || $list==='' || $list===[])
    {
      return 'null';
    }

    if (is_string($list))
    {
      $list = str_getcsv($list, $delimiter, $enclosure, $escape);
    }

    foreach ($list as $number)
    {
      if (!is_numeric($number))
      {
        throw new LogicException("Value '%s' is not a number.", (is_scalar($number)) ? $number : gettype($number));
      }
    }

    return $this->quoteString(implode(',', $list));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a literal for a string value that can be safely used in SQL statements.
   *
   * @param string|null $value The value.
   *
   * @return string
   */
  public function quoteString(?string $value): string
  {
    if ($value===null || $value==='')
    {
      return 'null';
    }

    return "'".$this->mysqli->real_escape_string($value)."'";
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Escapes special characters in a string such that it can be safely used in SQL statements.
   *
   * MysqlWrapper around [mysqli::real_escape_string](http://php.net/manual/mysqli.real-escape-string.php).
   *
   * @param string $string The string.
   *
   * @return string
   */
  public function realEscapeString(string $string): string
  {
    return $this->mysqli->real_escape_string($string);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Rollbacks the current transaction (and starts a new transaction).
   *
   * MysqlWrapper around [mysqli::rollback](http://php.net/manual/en/mysqli.rollback.php), however on failure an
   * exception is thrown.
   *
   * @throws MySqlDataLayerException
   *
   * @api
   * @since 1.0.0
   */
  public function rollback(): void
  {
    $success = @$this->mysqli->rollback();
    if (!$success)
    {
      throw $this->dataLayerError('mysqli::rollback');
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Sets the connector of this data layer. The data layer must be disconnected from the MySQL instance.
   *
   * @param MySqlConnector $connector The new connector.
   */
  public function setConnector(MySqlConnector $connector): void
  {
    if ($this->mysqli!==null)
    {
      throw new \LogicException('Can not set connector of a connected data layer. Disconnect first.');
    }

    $this->connector = $connector;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Logs the warnings of the last executed SQL statement.
   *
   * MysqlWrapper around the SQL statement [show warnings](https://dev.mysql.com/doc/refman/5.6/en/show-warnings.html).
   *
   * @throws MySqlDataLayerException
   *
   * @api
   * @since 1.0.0
   */
  public function showWarnings(): void
  {
    $this->executeLog('show warnings');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Return an exception with error information provided by MySQL/[mysqli](http://php.net/manual/en/class.mysqli.php).
   *
   * @param string $method The name of the method that has failed.
   *
   * @return MySqlDataLayerException
   */
  protected function dataLayerError(string $method): MySqlDataLayerException
  {
    return new MySqlDataLayerException($this->mysqli->errno, $this->mysqli->error, $method);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes multiple SQL statements.
   *
   * MysqlWrapper around [multi_mysqli::query](http://php.net/manual/mysqli.multi-query.php), however on failure an
   * exception is thrown.
   *
   * @param string $queries The SQL statements.
   *
   * @throws MySqlQueryErrorException
   */
  protected function multiQuery(string $queries): void
  {
    if ($this->logQueries)
    {
      $time0 = microtime(true);
    }

    try
    {
      $ret = @$this->mysqli->multi_query($queries);
    }
    catch (\mysqli_sql_exception)
    {
      $ret = false;
    }
    if ($ret===false)
    {
      throw $this->queryError('mysqli::multi_query', $queries);
    }

    if ($this->logQueries)
    {
      $this->queryLog[] = ['query' => $queries, 'time' => microtime(true) - $time0];
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes a query (i.e. SELECT, SHOW, DESCRIBE or EXPLAIN) with a result set.
   *
   * MysqlWrapper around [mysqli::query](http://php.net/manual/mysqli.query.php), however on failure an exception is
   * thrown.
   *
   * For other SQL statements, see @realQuery.
   *
   * @param string $query The SQL statement.
   *
   * @return \mysqli_result
   *
   * @throws MySqlQueryErrorException
   */
  protected function query(string $query): \mysqli_result
  {
    if ($this->logQueries)
    {
      $time0 = microtime(true);
    }

    try
    {
      $ret = @$this->mysqli->query($query);
    }
    catch (\mysqli_sql_exception)
    {
      $ret = false;
    }
    if ($ret===false)
    {
      throw $this->queryError('mysqli::query', $query);
    }

    if ($this->logQueries)
    {
      $this->queryLog[] = ['query' => $query, 'time' => microtime(true) - $time0];
    }

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Throws an exception with error information provided by MySQL/[mysqli](http://php.net/manual/en/class.mysqli.php).
   *
   * @param string $method The name of the method that has failed.
   * @param string $query  The failed query.
   *
   * @return MySqlQueryErrorException
   */
  protected function queryError(string $method, string $query): MySqlQueryErrorException
  {
    return new MySqlQueryErrorException($this->mysqli->errno, $this->mysqli->error, $method, $query);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Execute a query without a result set.
   *
   * MysqlWrapper around [mysqli::real_query](http://php.net/manual/en/mysqli.real-query.php), however on failure an
   * exception is thrown.
   *
   * For SELECT, SHOW, DESCRIBE or EXPLAIN queries, see @query.
   *
   * @param string $query The SQL statement.
   *
   * @throws MySqlQueryErrorException
   */
  protected function realQuery(string $query): void
  {
    if ($this->logQueries)
    {
      $time0 = microtime(true);
    }

    try
    {
      $success = @$this->mysqli->real_query($query);
    }
    catch (\mysqli_sql_exception)
    {
      $success = false;
    }
    if (!$success)
    {
      throw $this->queryError('mysqli::real_query', $query);
    }

    if ($this->logQueries)
    {
      $this->queryLog[] = ['query' => $query,
                           'time'  => microtime(true) - $time0];
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Send data in blocks to the MySQL server.
   *
   * MysqlWrapper around [mysqli_stmt::send_long_data](http://php.net/manual/mysqli-stmt.send-long-data.php).
   *
   * @param \mysqli_stmt $statement The prepared statement.
   * @param int          $paramNr   The 0-indexed parameter number.
   * @param string|null  $data      The data.
   *
   * @throws MySqlDataLayerException
   */
  protected function sendLongData(\mysqli_stmt $statement, int $paramNr, ?string $data): void
  {
    if ($data!==null)
    {
      $n = strlen($data);
      $p = 0;
      while ($p<$n)
      {
        $success = @$statement->send_long_data($paramNr, substr($data, $p, $this->chunkSize));
        if (!$success)
        {
          throw $this->dataLayerError('mysqli_stmt::send_long_data');
        }
        $p += $this->chunkSize;
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Helper method for method executeTable. Shows table footer.
   *
   * @param array $columns
   */
  private function executeTableShowFooter(array $columns): void
  {
    $separator = '+';

    foreach ($columns as $column)
    {
      $separator .= str_repeat('-', $column['length'] + 2).'+';
    }
    echo $separator, "\n";
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Helper method for method executeTable. Shows table header.
   *
   * @param array $columns
   */
  private function executeTableShowHeader(array $columns): void
  {
    $separator = '+';
    $header    = '|';

    foreach ($columns as $column)
    {
      $separator .= str_repeat('-', $column['length'] + 2).'+';
      $spaces    = ($column['length'] + 2) - mb_strlen((string)$column['header']);

      $spacesLeft  = (int)floor($spaces / 2);
      $spacesRight = (int)ceil($spaces / 2);

      $fillerLeft  = ($spacesLeft>0) ? str_repeat(' ', $spacesLeft) : '';
      $fillerRight = ($spacesRight>0) ? str_repeat(' ', $spacesRight) : '';

      $header .= $fillerLeft.$column['header'].$fillerRight.'|';
    }

    echo "\n", $separator, "\n";
    echo $header, "\n";
    echo $separator, "\n";
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Helper method for method executeTable. Shows table cell with data.
   *
   * @param array $column The metadata of the column.
   * @param mixed $value  The value of the table cell.
   */
  private function executeTableShowTableColumn(array $column, mixed $value): void
  {
    $spaces = str_repeat(' ', max($column['length'] - mb_strlen((string)$value), 0));

    switch ($column['type'])
    {
      case 1: // tinyint
      case 2: // smallint
      case 3: // int
      case 4: // float
      case 5: // double
      case 8: // bigint
      case 9: // mediumint
      case 246: // decimal
        echo ' ', $spaces.$value, ' ';
        break;

      case 7: // timestamp
      case 10: // date
      case 11: // time
      case 12: // datetime
      case 13: // year
      case 16: // bit
      case 252: // is currently mapped to all text and blob types (MySQL 5.0.51a)
      case 253: // varchar
      case 254: // char
        echo ' ', $value.$spaces, ' ';
        break;

      default:
        throw new FallenException('data type id', $column['type']);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
