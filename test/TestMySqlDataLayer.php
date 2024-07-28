<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Test;

use SetBased\Stratum\MySql\Exception\MySqlQueryErrorException;
use SetBased\Stratum\MySql\Exception\MySqlDataLayerException;
use SetBased\Stratum\Middle\Exception\ResultException;
use SetBased\Stratum\MySql\MySqlDataLayer;

/**
 * The data layer.
 */
class TestMySqlDataLayer extends MySqlDataLayer
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Select all IP addresses.
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function tstInet6GetAll(): array
  {
    return $this->executeRows('call tst_inet6_get_all()');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for stored routine with a inet6 argument.
   *
   * @param string|null $pIpIp The IPv6 address.
   *                           inet6
   *
   * @return int
   *
   * @throws MySqlQueryErrorException
   */
  public function tstInet6InsertInet6(?string $pIpIp): int
  {
    return $this->executeNone('call tst_inet6_insert_inet6('.$this->quoteString($pIpIp).')');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for magic constant.
   *
   * @return string
   *
   * @throws MySqlDataLayerException
   * @throws ResultException
   */
  public function tstMagicConstant01(): string
  {
    return $this->executeSingleton1('call tst_magic_constant01()');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for magic constant.
   *
   * @return int
   *
   * @throws MySqlDataLayerException
   * @throws ResultException
   */
  public function tstMagicConstant02(): int
  {
    return $this->executeSingleton1('call tst_magic_constant02()');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for magic constant.
   *
   * @return string
   *
   * @throws MySqlDataLayerException
   * @throws ResultException
   */
  public function tstMagicConstant03(): string
  {
    return $this->executeSingleton1('call tst_magic_constant03()');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for magic constant.
   *
   * @return string
   *
   * @throws MySqlDataLayerException
   * @throws ResultException
   */
  public function tstMagicConstant04(): string
  {
    return $this->executeSingleton1('call tst_magic_constant04()');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for magic constant.
   *
   * @return string
   *
   * @throws MySqlDataLayerException
   * @throws ResultException
   */
  public function tstMagicConstant05(): string
  {
    return $this->executeSingleton1('call tst_magic_constant05()');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Adds two numbers.
   *
   * @param int|null $pLeft  The left operand.
   *                         int(11)
   * @param int|null $pRight The right operand.
   *                         int(11)
   *
   * @return int
   *
   * @throws MySqlDataLayerException
   * @throws ResultException
   */
  public function tstOracleAddFunction(?int $pLeft, ?int $pRight): int
  {
    return $this->executeSingleton0('select tst_oracle_add_function('.$this->quoteInt($pLeft).','.$this->quoteInt($pRight).')');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Adds two numbers
   *
   * @param int|null $pLeft  The left operand.
   *                         int(11)
   * @param int|null $pRight The right operand.
   *                         int(11)
   *
   * @return int
   *
   * @throws MySqlDataLayerException
   * @throws ResultException
   */
  public function tstOracleAddProcedure(?int $pLeft, ?int $pRight): int
  {
    return $this->executeSingleton1('call tst_oracle_add_procedure('.$this->quoteInt($pLeft).','.$this->quoteInt($pRight).')');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects its argument.
   *
   * @param string|null $pMessage The message to be echoed.
   *                              varchar(4000) character set utf8mb4 collation utf8mb4_general_ci
   *
   * @return string
   *
   * @throws MySqlDataLayerException
   * @throws ResultException
   */
  public function tstOracleEcho(?string $pMessage): string
  {
    return $this->executeSingleton1('call tst_oracle_echo('.$this->quoteString($pMessage).')');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects Hello, World!
   *
   * @return string
   *
   * @throws MySqlDataLayerException
   * @throws ResultException
   */
  public function tstOracleHelloWorld(): string
  {
    return $this->executeSingleton1('call tst_oracle_hello_world()');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test parameters with data type from a table column.
   *
   * @param string|null           $pParam01 Data type from (other) schema, table, and column.
   *                                        varchar(64) character set utf8mb3 collation utf8mb3_general_ci
   * @param int|null              $pParam02 Data type int.
   *                                        int(11)
   * @param int|null              $pParam03 Data type smallint.
   *                                        smallint(6)
   * @param int|null              $pParam04 Data type tinyint.
   *                                        tinyint(4)
   * @param int|null              $pParam05 Data type mediumint.
   *                                        mediumint(9)
   * @param int|null              $pParam06 Data type bigint.
   *                                        bigint(20)
   * @param int|null              $pParam07 Data type int unsigned.
   *                                        int(10) unsigned
   * @param int|null              $pParam08 Data type smallint unsigned.
   *                                        smallint(5) unsigned
   * @param int|null              $pParam09 Data type tinyint unsigned.
   *                                        tinyint(3) unsigned
   * @param int|null              $pParam10 Data type mediumint unsigned.
   *                                        mediumint(8) unsigned
   * @param int|null              $pParam11 Data type bigint unsigned.
   *                                        bigint(20) unsigned
   * @param int|float|string|null $pParam12 Data type decimal(10,2).
   *                                        decimal(10,2)
   * @param int|float|string|null $pParam13 Data type decimal(65).
   *                                        decimal(65,0)
   * @param float|null            $pParam14 Data type float.
   *                                        float
   * @param float|null            $pParam15 Data type double.
   *                                        double
   * @param string|null           $pParam16 Data type bit(8).
   *                                        bit(8)
   * @param string|null           $pParam17 Data type date.
   *                                        date
   * @param string|null           $pParam18 Data type datetime.
   *                                        datetime
   * @param string|null           $pParam19 Data type timestamp.
   *                                        timestamp
   * @param string|null           $pParam20 Data type time.
   *                                        time
   * @param int|null              $pParam21 Data type year.
   *                                        year(4)
   * @param string|null           $pParam22 Data type char(10).
   *                                        char(10) character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pParam23 Data type varchar(10).
   *                                        varchar(10) character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pParam24 Data type binary(10).
   *                                        binary(10)
   * @param string|null           $pParam25 Data type varbinary(10).
   *                                        varbinary(10)
   * @param string|null           $pParam26 Data type tinyblob.
   *                                        tinyblob
   * @param string|null           $pParam27 Data type blob.
   *                                        blob(65535)
   * @param string|null           $pParam28 Data type mediumblob.
   *                                        mediumblob
   * @param string|null           $pParam29 Data type longblob.
   *                                        longblob
   * @param string|null           $pParam30 Data type tinytext.
   *                                        tinytext character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pParam31 Data type text.
   *                                        text character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pParam32 Data type mediumtext.
   *                                        mediumtext character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pParam33 Data type longtext.
   *                                        longtext character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pParam34 Data type enum('a','b').
   *                                        enum('a','b') character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pParam35 Data type set('a','b').
   *                                        set('a','b') character set utf8mb4 collation utf8mb4_general_ci
   *
   * @return int
   *
   * @throws MySqlDataLayerException
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function tstOracleProcedureWithDataTypeColumns(?string $pParam01, ?int $pParam02, ?int $pParam03, ?int $pParam04, ?int $pParam05, ?int $pParam06, ?int $pParam07, ?int $pParam08, ?int $pParam09, ?int $pParam10, ?int $pParam11, $pParam12, $pParam13, ?float $pParam14, ?float $pParam15, ?string $pParam16, ?string $pParam17, ?string $pParam18, ?string $pParam19, ?string $pParam20, ?int $pParam21, ?string $pParam22, ?string $pParam23, ?string $pParam24, ?string $pParam25, ?string $pParam26, ?string $pParam27, ?string $pParam28, ?string $pParam29, ?string $pParam30, ?string $pParam31, ?string $pParam32, ?string $pParam33, ?string $pParam34, ?string $pParam35): int
  {
    $query = 'call tst_oracle_procedure_with_data_type_columns('.$this->quoteString($pParam01).','.$this->quoteInt($pParam02).','.$this->quoteInt($pParam03).','.$this->quoteInt($pParam04).','.$this->quoteInt($pParam05).','.$this->quoteInt($pParam06).','.$this->quoteInt($pParam07).','.$this->quoteInt($pParam08).','.$this->quoteInt($pParam09).','.$this->quoteInt($pParam10).','.$this->quoteInt($pParam11).','.$this->quoteDecimal($pParam12).','.$this->quoteDecimal($pParam13).','.$this->quoteFloat($pParam14).','.$this->quoteFloat($pParam15).','.$this->quoteBit($pParam16).','.$this->quoteString($pParam17).','.$this->quoteString($pParam18).','.$this->quoteString($pParam19).','.$this->quoteString($pParam20).','.$this->quoteInt($pParam21).','.$this->quoteString($pParam22).','.$this->quoteString($pParam23).','.$this->quoteBinary($pParam24).','.$this->quoteBinary($pParam25).',?,?,?,?,?,?,?,?,'.$this->quoteString($pParam34).','.$this->quoteString($pParam35).')';
    $stmt  = @$this->mysqli->prepare($query);
    if (!$stmt) throw $this->dataLayerError('mysqli::prepare');

    $null = null;
    $success = @$stmt->bind_param('bbbbbbbb', $null, $null, $null, $null, $null, $null, $null, $null);
    if (!$success) throw $this->dataLayerError('mysqli_stmt::bind_param');

    $this->getMaxAllowedPacket();

    $this->sendLongData($stmt, 0, $pParam26);
    $this->sendLongData($stmt, 1, $pParam27);
    $this->sendLongData($stmt, 2, $pParam28);
    $this->sendLongData($stmt, 3, $pParam29);
    $this->sendLongData($stmt, 4, $pParam30);
    $this->sendLongData($stmt, 5, $pParam31);
    $this->sendLongData($stmt, 6, $pParam32);
    $this->sendLongData($stmt, 7, $pParam33);

    if ($this->logQueries)
    {
      $time0 = microtime(true);
    }

    try
    {
      $success = @$stmt->execute();
    }
    catch (\mysqli_sql_exception)
    {
      $success = false;
    }
    if (!$success)
    {
      throw $this->queryError('mysqli_stmt::execute', $query);
    }

    if ($this->logQueries)
    {
      $this->queryLog[] = ['query' => $query,
                           'time'  => microtime(true) - $time0];
    }

    $ret = $this->mysqli->affected_rows;

    $stmt->close();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * This a stored routine in SQL/PSM with "as" key word.
   *
   * @return int
   *
   * @throws MySqlQueryErrorException
   */
  public function tstOracleSqlPsm(): int
  {
    return $this->executeNone('call tst_oracle_sql_psm()');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation bulk_insert.
   *
   * @param array[] $rows The rows that must be inserted.
   *
   * @return void
   *
   * @throws MySqlQueryErrorException
   */
  public function tstTestBulkInsert01(?array $rows): void
  {
    $this->realQuery('call tst_test_bulk_insert01()');
    if (is_array($rows) && !empty($rows))
    {
      $sql = "INSERT INTO `TST_TEMPO`(`tst_int`,`tst_smallint`,`tst_mediumint`,`tst_tinyint`,`tst_bigint`,`tst_int_unsigned`,`tst_smallint_unsigned`,`tst_mediumint_unsigned`,`tst_tinyint_unsigned`,`tst_bigint_unsigned`,`tst_year`,`tst_decimal`,`tst_decimal0`,`tst_float`,`tst_double`,`tst_binary`,`tst_varbinary`,`tst_char`,`tst_varchar`,`tst_time`,`tst_timestamp`,`tst_date`,`tst_datetime`,`tst_enum`,`tst_set`,`tst_bit`)".PHP_EOL;
      $first = true;
      foreach($rows as $row)
      {
        $sql .= (($first) ? 'values' : ',     ').'('.$this->quoteInt($row['field_int']).','.$this->quoteInt($row['field_smallint']).','.$this->quoteInt($row['field_mediumint']).','.$this->quoteInt($row['field_tinyint']).','.$this->quoteInt($row['field_bigint']).','.$this->quoteInt($row['field_int_unsigned']).','.$this->quoteInt($row['field_smallint_unsigned']).','.$this->quoteInt($row['field_mediumint_unsigned']).','.$this->quoteInt($row['field_tinyint_unsigned']).','.$this->quoteInt($row['field_bigint_unsigned']).','.$this->quoteInt($row['field_year']).','.$this->quoteDecimal($row['field_decimal']).','.$this->quoteDecimal($row['field_decimal0']).','.$this->quoteFloat($row['field_float']).','.$this->quoteFloat($row['field_double']).','.$this->quoteBinary($row['field_binary']).','.$this->quoteBinary($row['field_varbinary']).','.$this->quoteString($row['field_char']).','.$this->quoteString($row['field_varchar']).','.$this->quoteString($row['field_time']).','.$this->quoteString($row['field_timestamp']).','.$this->quoteString($row['field_date']).','.$this->quoteString($row['field_datetime']).','.$this->quoteString($row['field_enum']).','.$this->quoteString($row['field_set']).','.$this->quoteBit($row['field_bit']).')'.PHP_EOL;
        $first = false;
      }
      $this->realQuery($sql);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation bulk_insert.
   *
   * @param array[] $rows The rows that must be inserted.
   *
   * @return void
   *
   * @throws MySqlQueryErrorException
   */
  public function tstTestBulkInsert02(?array $rows): void
  {
    $this->realQuery('call tst_test_bulk_insert02()');
    if (is_array($rows) && !empty($rows))
    {
      $sql = "INSERT INTO `TST_TEMPO`(`tst_col1`,`tst_col4`,`tst_col5`)".PHP_EOL;
      $first = true;
      foreach($rows as $row)
      {
        $sql .= (($first) ? 'values' : ',     ').'('.$this->quoteInt($row['field1']).','.$this->quoteInt($row['field4']).','.$this->quoteInt($row['field5']).')'.PHP_EOL;
        $first = false;
      }
      $this->realQuery($sql);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for stored function wrapper.
   *
   * @param int|null $pA Parameter A.
   *                     int(11)
   * @param int|null $pB Parameter B.
   *                     int(11)
   *
   * @return int|null
   *
   * @throws MySqlDataLayerException
   * @throws ResultException
   */
  public function tstTestFunction(?int $pA, ?int $pB): ?int
  {
    return $this->executeSingleton0('select tst_test_function('.$this->quoteInt($pA).','.$this->quoteInt($pB).')');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for stored function with return type bool wrapper.
   *
   * @param int|null $pRet The return value.
   *                       int(11)
   *
   * @return bool
   *
   * @throws MySqlDataLayerException
   * @throws ResultException
   */
  public function tstTestFunctionBool1(?int $pRet): bool
  {
    return !empty($this->executeSingleton0('select tst_test_function_bool1('.$this->quoteInt($pRet).')'));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for stored function with return type bool wrapper.
   *
   * @param string|null $pRet The return value.
   *                          varchar(8) character set utf8mb4 collation utf8mb4_general_ci
   *
   * @return bool
   *
   * @throws MySqlDataLayerException
   * @throws ResultException
   */
  public function tstTestFunctionBool2(?string $pRet): bool
  {
    return !empty($this->executeSingleton0('select tst_test_function_bool2('.$this->quoteString($pRet).')'));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for illegal query.
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function tstTestIllegalQuery(): array
  {
    return $this->executeRows('call tst_test_illegal_query()');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   *
   * @param string|int[]|null $pIds The id's in CSV format.
   *                                varchar(255) character set utf8mb4 collation utf8mb4_general_ci
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function tstTestListOfInt($pIds): array
  {
    $result = $this->query('call tst_test_list_of_int('.$this->quoteListOfInt($pIds, ',', '\"', '\\').')');
    $ret = [];
    while (($row = $result->fetch_array(MYSQLI_ASSOC))) $ret[$row['tst_id']] = $row;
    $result->free();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type log.
   *
   * @return int
   *
   * @throws MySqlDataLayerException
   */
  public function tstTestLog(): int
  {
    return $this->executeLog('call tst_test_log()');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type map.
   *
   * @param int|null $pCount Number of rows selected.
   *                         int(11)
   *
   * @return array
   *
   * @throws MySqlQueryErrorException
   */
  public function tstTestMap1(?int $pCount): array
  {
    $result = $this->query('call tst_test_map1('.$this->quoteInt($pCount).')');
    $ret = [];
    while (($row = $result->fetch_array(MYSQLI_NUM))) $ret[$row[0]] = $row[1];
    $result->free();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type rows_with_key with BLOB.
   *
   * @param int|null    $pCount Number of rows selected.
   *                            int(11)
   * @param string|null $pBlob  The BLOB.
   *                            blob
   *
   * @return array
   *
   * @throws MySqlDataLayerException
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function tstTestMap1WithLob(?int $pCount, ?string $pBlob): array
  {
    $query = 'call tst_test_map1_with_lob('.$this->quoteInt($pCount).',?)';
    $stmt  = @$this->mysqli->prepare($query);
    if (!$stmt) throw $this->dataLayerError('mysqli::prepare');

    $null = null;
    $success = @$stmt->bind_param('b', $null);
    if (!$success) throw $this->dataLayerError('mysqli_stmt::bind_param');

    $this->getMaxAllowedPacket();

    $this->sendLongData($stmt, 0, $pBlob);

    if ($this->logQueries)
    {
      $time0 = microtime(true);
    }

    try
    {
      $success = @$stmt->execute();
    }
    catch (\mysqli_sql_exception)
    {
      $success = false;
    }
    if (!$success)
    {
      throw $this->queryError('mysqli_stmt::execute', $query);
    }

    if ($this->logQueries)
    {
      $this->queryLog[] = ['query' => $query,
                           'time'  => microtime(true) - $time0];
    }

    $result = $stmt->get_result();
    $ret = [];
    while (($row = $result->fetch_array(MYSQLI_NUM))) $ret[$row[0]] = $row[1];
    $result->free();

    $stmt->close();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for sending data larger than max_allowed_packet.
   *
   * @param string|null $pTmpBlob The BLOB larger than max_allowed_packet.
   *                              longblob
   *
   * @return int
   *
   * @throws MySqlDataLayerException
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function tstTestMaxAllowedPacket(?string $pTmpBlob): int
  {
    $query = 'call tst_test_max_allowed_packet(?)';
    $stmt  = @$this->mysqli->prepare($query);
    if (!$stmt) throw $this->dataLayerError('mysqli::prepare');

    $null = null;
    $success = @$stmt->bind_param('b', $null);
    if (!$success) throw $this->dataLayerError('mysqli_stmt::bind_param');

    $this->getMaxAllowedPacket();

    $this->sendLongData($stmt, 0, $pTmpBlob);

    if ($this->logQueries)
    {
      $time0 = microtime(true);
    }

    try
    {
      $success = @$stmt->execute();
    }
    catch (\mysqli_sql_exception)
    {
      $success = false;
    }
    if (!$success)
    {
      throw $this->queryError('mysqli_stmt::execute', $query);
    }

    if ($this->logQueries)
    {
      $this->queryLog[] = ['query' => $query,
                           'time'  => microtime(true) - $time0];
    }

    $row = [];
    $this->bindAssoc($stmt, $row);

    $tmp = [];
    while (($b = $stmt->fetch()))
    {
      $new = [];
      foreach($row as $value)
      {
        $new[] = $value;
      }
      $tmp[] = $new;
    }

    $stmt->close();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    if ($b===false) throw $this->dataLayerError('mysqli_stmt::fetch');
    if (sizeof($tmp)!=1) throw new ResultException([1], sizeof($tmp), $query);

    return $tmp[0][0];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type none.
   *
   * @param int|null $pCount The number of iterations.
   *                         bigint(20)
   *
   * @return int
   *
   * @throws MySqlQueryErrorException
   */
  public function tstTestNone(?int $pCount): int
  {
    return $this->executeNone('call tst_test_none('.$this->quoteInt($pCount).')');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type none with BLOB.
   *
   * @param int|null    $pCount The number of iterations.
   *                            bigint(20)
   * @param string|null $pBlob  The BLOB.
   *                            blob
   *
   * @return int
   *
   * @throws MySqlDataLayerException
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function tstTestNoneWithLob(?int $pCount, ?string $pBlob): int
  {
    $query = 'call tst_test_none_with_lob('.$this->quoteInt($pCount).',?)';
    $stmt  = @$this->mysqli->prepare($query);
    if (!$stmt) throw $this->dataLayerError('mysqli::prepare');

    $null = null;
    $success = @$stmt->bind_param('b', $null);
    if (!$success) throw $this->dataLayerError('mysqli_stmt::bind_param');

    $this->getMaxAllowedPacket();

    $this->sendLongData($stmt, 0, $pBlob);

    if ($this->logQueries)
    {
      $time0 = microtime(true);
    }

    try
    {
      $success = @$stmt->execute();
    }
    catch (\mysqli_sql_exception)
    {
      $success = false;
    }
    if (!$success)
    {
      throw $this->queryError('mysqli_stmt::execute', $query);
    }

    if ($this->logQueries)
    {
      $this->queryLog[] = ['query' => $query,
                           'time'  => microtime(true) - $time0];
    }

    $ret = $this->mysqli->affected_rows;

    $stmt->close();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for column%sort v.s. column%type.
   *
   * @param string|null $pTstLastName   Can be a long string.
   *                                    varchar(16383) character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null $pTstFirstName  Can be a long string.
   *                                    varchar(21844) character set utf8mb3 collation utf8mb3_general_ci
   * @param string|null $pTstInstrument Can be a long string.
   *                                    varchar(65532) character set ascii collation ascii_general_ci
   *
   * @return array|null
   *
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function tstTestParameterSort(?string $pTstLastName, ?string $pTstFirstName, ?string $pTstInstrument): ?array
  {
    return $this->executeRow0('call tst_test_parameter_sort('.$this->quoteString($pTstLastName).','.$this->quoteString($pTstFirstName).','.$this->quoteString($pTstInstrument).')');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for conversion of MySQL types to PHP types.
   *
   * @param int|float|string|null $pPhpType1 Must be converted to PHP type string in the TestMySqlDataLayer.
   *                                         decimal(10,2)
   * @param int|float|string|null $pPhpType2 Must be converted to PHP type string in the TestMySqlDataLayer.
   *                                         decimal(65,0)
   *
   * @return int
   *
   * @throws MySqlQueryErrorException
   */
  public function tstTestParameterType($pPhpType1, $pPhpType2): int
  {
    return $this->executeNone('call tst_test_parameter_type('.$this->quoteDecimal($pPhpType1).','.$this->quoteDecimal($pPhpType2).')');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type row0.
   *
   * @param int|null $pCount The number of rows selected.
   *                         * 0 For a valid test.
   *                         * 1 For a valid test.
   *                         * 2 For a invalid test.
   *                         int(11)
   *
   * @return array|null
   *
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function tstTestRow0a(?int $pCount): ?array
  {
    return $this->executeRow0('call tst_test_row0a('.$this->quoteInt($pCount).')');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type row0 with BLOB.
   *
   * @param int|null    $pCount The number of rows selected.
   *                            * 0 For a valid test.
   *                            * 1 For a valid test.
   *                            * 2 For a invalid test.
   *                            int(11)
   * @param string|null $pBlob  The BLOB.
   *                            blob
   *
   * @return array|null
   *
   * @throws MySqlDataLayerException
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function tstTestRow0aWithLob(?int $pCount, ?string $pBlob): ?array
  {
    $query = 'call tst_test_row0a_with_lob('.$this->quoteInt($pCount).',?)';
    $stmt  = @$this->mysqli->prepare($query);
    if (!$stmt) throw $this->dataLayerError('mysqli::prepare');

    $null = null;
    $success = @$stmt->bind_param('b', $null);
    if (!$success) throw $this->dataLayerError('mysqli_stmt::bind_param');

    $this->getMaxAllowedPacket();

    $this->sendLongData($stmt, 0, $pBlob);

    if ($this->logQueries)
    {
      $time0 = microtime(true);
    }

    try
    {
      $success = @$stmt->execute();
    }
    catch (\mysqli_sql_exception)
    {
      $success = false;
    }
    if (!$success)
    {
      throw $this->queryError('mysqli_stmt::execute', $query);
    }

    if ($this->logQueries)
    {
      $this->queryLog[] = ['query' => $query,
                           'time'  => microtime(true) - $time0];
    }

    $row = [];
    $this->bindAssoc($stmt, $row);

    $tmp = [];
    while (($b = $stmt->fetch()))
    {
      $new = [];
      foreach($row as $key => $value)
      {
        $new[$key] = $value;
      }
      $tmp[] = $new;
    }

    $stmt->close();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    if ($b===false) throw $this->dataLayerError('mysqli_stmt::fetch');
    if (sizeof($tmp)>1) throw new ResultException([0, 1], sizeof($tmp), $query);

    return ($tmp) ? $tmp[0] : null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type row1.
   *
   * @param int|null $pCount The number of rows selected.
   *                         * 0 For a invalid test.
   *                         * 1 For a valid test.
   *                         * 2 For a invalid test.
   *                         int(11)
   *
   * @return array
   *
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function tstTestRow1a(?int $pCount): array
  {
    return $this->executeRow1('call tst_test_row1a('.$this->quoteInt($pCount).')');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type row1 with BLOB.
   *
   * @param int|null    $pCount The number of rows selected.
   *                            * 0 For a invalid test.
   *                            * 1 For a valid test.
   *                            * 2 For a invalid test.
   *                            int(11)
   * @param string|null $pBlob  The BLOB.
   *                            blob
   *
   * @return array
   *
   * @throws MySqlDataLayerException
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function tstTestRow1aWithLob(?int $pCount, ?string $pBlob): array
  {
    $query = 'call tst_test_row1a_with_lob('.$this->quoteInt($pCount).',?)';
    $stmt  = @$this->mysqli->prepare($query);
    if (!$stmt) throw $this->dataLayerError('mysqli::prepare');

    $null = null;
    $success = @$stmt->bind_param('b', $null);
    if (!$success) throw $this->dataLayerError('mysqli_stmt::bind_param');

    $this->getMaxAllowedPacket();

    $this->sendLongData($stmt, 0, $pBlob);

    if ($this->logQueries)
    {
      $time0 = microtime(true);
    }

    try
    {
      $success = @$stmt->execute();
    }
    catch (\mysqli_sql_exception)
    {
      $success = false;
    }
    if (!$success)
    {
      throw $this->queryError('mysqli_stmt::execute', $query);
    }

    if ($this->logQueries)
    {
      $this->queryLog[] = ['query' => $query,
                           'time'  => microtime(true) - $time0];
    }

    $row = [];
    $this->bindAssoc($stmt, $row);

    $tmp = [];
    while (($b = $stmt->fetch()))
    {
      $new = [];
      foreach($row as $key => $value)
      {
        $new[$key] = $value;
      }
      $tmp[] = $new;
    }

    $stmt->close();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    if ($b===false) throw $this->dataLayerError('mysqli_stmt::fetch');
    if (sizeof($tmp)!=1) throw new ResultException([1], sizeof($tmp), $query);

    return $row;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type rows.
   *
   * @param int|null $pCount The number of rows selected.
   *                         int(11)
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function tstTestRows1(?int $pCount): array
  {
    return $this->executeRows('call tst_test_rows1('.$this->quoteInt($pCount).')');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type rows.
   *
   * @param int|null    $pCount The number of rows selected.
   *                            int(11)
   * @param string|null $pBlob  The BLOB.
   *                            blob
   *
   * @return array[]
   *
   * @throws MySqlDataLayerException
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function tstTestRows1WithLob(?int $pCount, ?string $pBlob): array
  {
    $query = 'call tst_test_rows1_with_lob('.$this->quoteInt($pCount).',?)';
    $stmt  = @$this->mysqli->prepare($query);
    if (!$stmt) throw $this->dataLayerError('mysqli::prepare');

    $null = null;
    $success = @$stmt->bind_param('b', $null);
    if (!$success) throw $this->dataLayerError('mysqli_stmt::bind_param');

    $this->getMaxAllowedPacket();

    $this->sendLongData($stmt, 0, $pBlob);

    if ($this->logQueries)
    {
      $time0 = microtime(true);
    }

    try
    {
      $success = @$stmt->execute();
    }
    catch (\mysqli_sql_exception)
    {
      $success = false;
    }
    if (!$success)
    {
      throw $this->queryError('mysqli_stmt::execute', $query);
    }

    if ($this->logQueries)
    {
      $this->queryLog[] = ['query' => $query,
                           'time'  => microtime(true) - $time0];
    }

    $row = [];
    $this->bindAssoc($stmt, $row);

    $tmp = [];
    while (($b = $stmt->fetch()))
    {
      $new = [];
      foreach($row as $key => $value)
      {
        $new[$key] = $value;
      }
      $tmp[] = $new;
    }

    $stmt->close();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    if ($b===false) throw $this->dataLayerError('mysqli_stmt::fetch');

    return $tmp;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type rows_with_index.
   *
   * @param int|null $pCount The number of rows selected.
   *                         int(11)
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function tstTestRowsWithIndex1(?int $pCount): array
  {
    $result = $this->query('call tst_test_rows_with_index1('.$this->quoteInt($pCount).')');
    $ret = [];
    while (($row = $result->fetch_array(MYSQLI_ASSOC))) $ret[$row['tst_c01']][$row['tst_c02']][] = $row;
    $result->free();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type rows_with_index with BLOB..
   *
   * @param int|null    $pCount The number of rows selected.
   *                            int(11)
   * @param string|null $pBlob  The BLOB.
   *                            blob
   *
   * @return array[]
   *
   * @throws MySqlDataLayerException
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function tstTestRowsWithIndex1WithLob(?int $pCount, ?string $pBlob): array
  {
    $query = 'call tst_test_rows_with_index1_with_lob('.$this->quoteInt($pCount).',?)';
    $stmt  = @$this->mysqli->prepare($query);
    if (!$stmt) throw $this->dataLayerError('mysqli::prepare');

    $null = null;
    $success = @$stmt->bind_param('b', $null);
    if (!$success) throw $this->dataLayerError('mysqli_stmt::bind_param');

    $this->getMaxAllowedPacket();

    $this->sendLongData($stmt, 0, $pBlob);

    if ($this->logQueries)
    {
      $time0 = microtime(true);
    }

    try
    {
      $success = @$stmt->execute();
    }
    catch (\mysqli_sql_exception)
    {
      $success = false;
    }
    if (!$success)
    {
      throw $this->queryError('mysqli_stmt::execute', $query);
    }

    if ($this->logQueries)
    {
      $this->queryLog[] = ['query' => $query,
                           'time'  => microtime(true) - $time0];
    }

    $row = [];
    $this->bindAssoc($stmt, $row);

    $ret = [];
    while (($b = $stmt->fetch()))
    {
      $new = [];
      foreach($row as $key => $value)
      {
        $new[$key] = $value;
      }
      $ret[$new['tst_c01']][$new['tst_c02']][] = $new;
    }

    $stmt->close();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    if ($b===false) throw $this->dataLayerError('mysqli_stmt::fetch');

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type rows_with_key.
   *
   * @param int|null $pCount Number of rows selected.
   *                         int(11)
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function tstTestRowsWithKey1(?int $pCount): array
  {
    $result = $this->query('call tst_test_rows_with_key1('.$this->quoteInt($pCount).')');
    $ret = [];
    while (($row = $result->fetch_array(MYSQLI_ASSOC))) $ret[$row['tst_c01']][$row['tst_c02']][$row['tst_c03']] = $row;
    $result->free();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type rows_with_key with BLOB.
   *
   * @param int|null    $pCount The number of rows selected.
   *                            int(11)
   * @param string|null $pBlob  The BLOB.
   *                            blob
   *
   * @return array[]
   *
   * @throws MySqlDataLayerException
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function tstTestRowsWithKey1WithLob(?int $pCount, ?string $pBlob): array
  {
    $query = 'call tst_test_rows_with_key1_with_lob('.$this->quoteInt($pCount).',?)';
    $stmt  = @$this->mysqli->prepare($query);
    if (!$stmt) throw $this->dataLayerError('mysqli::prepare');

    $null = null;
    $success = @$stmt->bind_param('b', $null);
    if (!$success) throw $this->dataLayerError('mysqli_stmt::bind_param');

    $this->getMaxAllowedPacket();

    $this->sendLongData($stmt, 0, $pBlob);

    if ($this->logQueries)
    {
      $time0 = microtime(true);
    }

    try
    {
      $success = @$stmt->execute();
    }
    catch (\mysqli_sql_exception)
    {
      $success = false;
    }
    if (!$success)
    {
      throw $this->queryError('mysqli_stmt::execute', $query);
    }

    if ($this->logQueries)
    {
      $this->queryLog[] = ['query' => $query,
                           'time'  => microtime(true) - $time0];
    }

    $row = [];
    $this->bindAssoc($stmt, $row);

    $ret = [];
    while (($b = $stmt->fetch()))
    {
      $new = [];
      foreach($row as $key => $value)
      {
        $new[$key] = $value;
      }
      $ret[$new['tst_c01']][$new['tst_c02']][$new['tst_c03']] = $new;
    }

    $stmt->close();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    if ($b===false) throw $this->dataLayerError('mysqli_stmt::fetch');

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type singleton0.
   *
   * @param int|null $pCount The number of rows selected.
   *                         * 0 For a valid test.
   *                         * 1 For a valid test.
   *                         * 2 For a invalid test.
   *                         int(11)
   *
   * @return int|null
   *
   * @throws MySqlDataLayerException
   * @throws ResultException
   */
  public function tstTestSingleton0a(?int $pCount): ?int
  {
    return $this->executeSingleton0('call tst_test_singleton0a('.$this->quoteInt($pCount).')');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type singleton0 with BLOB..
   *
   * @param int|null    $pCount The number of rows selected.
   *                            * 0 For a valid test.
   *                            * 1 For a valid test.
   *                            * 2 For a invalid test.
   *                            int(11)
   * @param string|null $pBlob  The BLOB.
   *                            blob
   *
   * @return int|null
   *
   * @throws MySqlDataLayerException
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function tstTestSingleton0aWithLob(?int $pCount, ?string $pBlob): ?int
  {
    $query = 'call tst_test_singleton0a_with_lob('.$this->quoteInt($pCount).',?)';
    $stmt  = @$this->mysqli->prepare($query);
    if (!$stmt) throw $this->dataLayerError('mysqli::prepare');

    $null = null;
    $success = @$stmt->bind_param('b', $null);
    if (!$success) throw $this->dataLayerError('mysqli_stmt::bind_param');

    $this->getMaxAllowedPacket();

    $this->sendLongData($stmt, 0, $pBlob);

    if ($this->logQueries)
    {
      $time0 = microtime(true);
    }

    try
    {
      $success = @$stmt->execute();
    }
    catch (\mysqli_sql_exception)
    {
      $success = false;
    }
    if (!$success)
    {
      throw $this->queryError('mysqli_stmt::execute', $query);
    }

    if ($this->logQueries)
    {
      $this->queryLog[] = ['query' => $query,
                           'time'  => microtime(true) - $time0];
    }

    $row = [];
    $this->bindAssoc($stmt, $row);

    $tmp = [];
    while (($b = $stmt->fetch()))
    {
      $new = [];
      foreach($row as $value)
      {
        $new[] = $value;
      }
      $tmp[] = $new;
    }

    $stmt->close();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    if ($b===false) throw $this->dataLayerError('mysqli_stmt::fetch');
    if (sizeof($tmp)>1) throw new ResultException([0, 1], sizeof($tmp), $query);

    return $tmp[0][0] ?? null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type singleton0 with return type bool.
   *
   * @param int|null $pCount The number of rows selected.
   *                         * 0 For a valid test.
   *                         * 1 For a valid test.
   *                         * 2 For a invalid test.
   *                         int(11)
   * @param int|null $pValue The selected value.
   *                         int(11)
   *
   * @return bool
   *
   * @throws MySqlDataLayerException
   * @throws ResultException
   */
  public function tstTestSingleton0b(?int $pCount, ?int $pValue): bool
  {
    return !empty($this->executeSingleton0('call tst_test_singleton0b('.$this->quoteInt($pCount).','.$this->quoteInt($pValue).')'));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type singleton0 with BLOB..
   *
   * @param int|null    $pCount The number of rows selected.
   *                            * 0 For a valid test.
   *                            * 1 For a valid test.
   *                            * 2 For a invalid test.
   *                            int(11)
   * @param int|null    $pValue The selected value.
   *                            int(11)
   * @param string|null $pBlob  The BLOB.
   *                            blob
   *
   * @return bool
   *
   * @throws MySqlDataLayerException
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function tstTestSingleton0bWithLob(?int $pCount, ?int $pValue, ?string $pBlob): bool
  {
    $query = 'call tst_test_singleton0b_with_lob('.$this->quoteInt($pCount).','.$this->quoteInt($pValue).',?)';
    $stmt  = @$this->mysqli->prepare($query);
    if (!$stmt) throw $this->dataLayerError('mysqli::prepare');

    $null = null;
    $success = @$stmt->bind_param('b', $null);
    if (!$success) throw $this->dataLayerError('mysqli_stmt::bind_param');

    $this->getMaxAllowedPacket();

    $this->sendLongData($stmt, 0, $pBlob);

    if ($this->logQueries)
    {
      $time0 = microtime(true);
    }

    try
    {
      $success = @$stmt->execute();
    }
    catch (\mysqli_sql_exception)
    {
      $success = false;
    }
    if (!$success)
    {
      throw $this->queryError('mysqli_stmt::execute', $query);
    }

    if ($this->logQueries)
    {
      $this->queryLog[] = ['query' => $query,
                           'time'  => microtime(true) - $time0];
    }

    $row = [];
    $this->bindAssoc($stmt, $row);

    $tmp = [];
    while (($b = $stmt->fetch()))
    {
      $new = [];
      foreach($row as $value)
      {
        $new[] = $value;
      }
      $tmp[] = $new;
    }

    $stmt->close();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    if ($b===false) throw $this->dataLayerError('mysqli_stmt::fetch');
    if (sizeof($tmp)>1) throw new ResultException([0, 1], sizeof($tmp), $query);

    return !empty($tmp[0][0]);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type singleton1.
   *
   * @param int|null $pCount The number of rows selected.
   *                         * 0 For a invalid test.
   *                         * 1 For a valid test.
   *                         * 2 For a invalid test.
   *                         int(11)
   *
   * @return int
   *
   * @throws MySqlDataLayerException
   * @throws ResultException
   */
  public function tstTestSingleton1a(?int $pCount): int
  {
    return $this->executeSingleton1('call tst_test_singleton1a('.$this->quoteInt($pCount).')');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type singleton1 with BLOB.
   *
   * @param int|null    $pCount The number of rows selected.
   *                            * 0 For a invalid test.
   *                            * 1 For a valid test.
   *                            * 2 For a invalid test.
   *                            int(11)
   * @param string|null $pBlob  The BLOB.
   *                            blob
   *
   * @return int
   *
   * @throws MySqlDataLayerException
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function tstTestSingleton1aWithLob(?int $pCount, ?string $pBlob): int
  {
    $query = 'call tst_test_singleton1a_with_lob('.$this->quoteInt($pCount).',?)';
    $stmt  = @$this->mysqli->prepare($query);
    if (!$stmt) throw $this->dataLayerError('mysqli::prepare');

    $null = null;
    $success = @$stmt->bind_param('b', $null);
    if (!$success) throw $this->dataLayerError('mysqli_stmt::bind_param');

    $this->getMaxAllowedPacket();

    $this->sendLongData($stmt, 0, $pBlob);

    if ($this->logQueries)
    {
      $time0 = microtime(true);
    }

    try
    {
      $success = @$stmt->execute();
    }
    catch (\mysqli_sql_exception)
    {
      $success = false;
    }
    if (!$success)
    {
      throw $this->queryError('mysqli_stmt::execute', $query);
    }

    if ($this->logQueries)
    {
      $this->queryLog[] = ['query' => $query,
                           'time'  => microtime(true) - $time0];
    }

    $row = [];
    $this->bindAssoc($stmt, $row);

    $tmp = [];
    while (($b = $stmt->fetch()))
    {
      $new = [];
      foreach($row as $value)
      {
        $new[] = $value;
      }
      $tmp[] = $new;
    }

    $stmt->close();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    if ($b===false) throw $this->dataLayerError('mysqli_stmt::fetch');
    if (sizeof($tmp)!=1) throw new ResultException([1], sizeof($tmp), $query);

    return $tmp[0][0];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type singleton1 with return type bool.
   *
   * @param int|null $pCount The number of rows selected.
   *                         * 0 For a invalid test.
   *                         * 1 For a valid test.
   *                         * 2 For a invalid test.
   *                         int(11)
   * @param int|null $pValue The selected value.
   *                         int(11)
   *
   * @return bool
   *
   * @throws MySqlDataLayerException
   * @throws ResultException
   */
  public function tstTestSingleton1b(?int $pCount, ?int $pValue): bool
  {
    return !empty($this->executeSingleton1('call tst_test_singleton1b('.$this->quoteInt($pCount).','.$this->quoteInt($pValue).')'));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type singleton1 with BLOB.
   *
   * @param int|null    $pCount The number of rows selected.
   *                            * 0 For a invalid test.
   *                            * 1 For a valid test.
   *                            * 2 For a invalid test.
   *                            int(11)
   * @param int|null    $pValue The selected value.
   *                            int(11)
   * @param string|null $pBlob  The BLOB.
   *                            blob
   *
   * @return bool
   *
   * @throws MySqlDataLayerException
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function tstTestSingleton1bWithLob(?int $pCount, ?int $pValue, ?string $pBlob): bool
  {
    $query = 'call tst_test_singleton1b_with_lob('.$this->quoteInt($pCount).','.$this->quoteInt($pValue).',?)';
    $stmt  = @$this->mysqli->prepare($query);
    if (!$stmt) throw $this->dataLayerError('mysqli::prepare');

    $null = null;
    $success = @$stmt->bind_param('b', $null);
    if (!$success) throw $this->dataLayerError('mysqli_stmt::bind_param');

    $this->getMaxAllowedPacket();

    $this->sendLongData($stmt, 0, $pBlob);

    if ($this->logQueries)
    {
      $time0 = microtime(true);
    }

    try
    {
      $success = @$stmt->execute();
    }
    catch (\mysqli_sql_exception)
    {
      $success = false;
    }
    if (!$success)
    {
      throw $this->queryError('mysqli_stmt::execute', $query);
    }

    if ($this->logQueries)
    {
      $this->queryLog[] = ['query' => $query,
                           'time'  => microtime(true) - $time0];
    }

    $row = [];
    $this->bindAssoc($stmt, $row);

    $tmp = [];
    while (($b = $stmt->fetch()))
    {
      $new = [];
      foreach($row as $value)
      {
        $new[] = $value;
      }
      $tmp[] = $new;
    }

    $stmt->close();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    if ($b===false) throw $this->dataLayerError('mysqli_stmt::fetch');
    if (sizeof($tmp)!=1) throw new ResultException([1], sizeof($tmp), $query);

    return !empty($tmp[0][0]);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for designation type table.
   *
   * @return int
   *
   * @throws MySqlDataLayerException
   */
  public function tstTestTable(): int
  {
    return $this->executeTable('call tst_test_table()');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for all possible types of parameters excluding LOB's.
   *
   * @param int|null              $pTstInt               Parameter of type int.
   *                                                     int(11)
   * @param int|null              $pTstSmallint          Parameter of type smallint.
   *                                                     smallint(6)
   * @param int|null              $pTstTinyint           Parameter of type tinyint.
   *                                                     tinyint(4)
   * @param int|null              $pTstMediumint         Parameter of type mediumint.
   *                                                     mediumint(9)
   * @param int|null              $pTstBigint            Parameter of type bigint.
   *                                                     bigint(20)
   * @param int|null              $pTstIntUnsigned       Parameter of type int unsigned.
   *                                                     int(10) unsigned
   * @param int|null              $pTstSmallintUnsigned  Parameter of type smallint unsigned.
   *                                                     smallint(5) unsigned
   * @param int|null              $pTstTinyintUnsigned   Parameter of type tinyint unsigned.
   *                                                     tinyint(3) unsigned
   * @param int|null              $pTstMediumintUnsigned Parameter of type mediumint unsigned.
   *                                                     mediumint(8) unsigned
   * @param int|null              $pTstBigintUnsigned    Parameter of type bigint unsigned.
   *                                                     bigint(20) unsigned
   * @param int|float|string|null $pTstDecimal           Parameter of type decimal.
   *                                                     decimal(10,2)
   * @param int|float|string|null $pTstDecimal0          Parameter of type decimal with 0 scale.
   *                                                     decimal(65,0)
   * @param float|null            $pTstFloat             Parameter of type float.
   *                                                     float
   * @param float|null            $pTstDouble            Parameter of type double.
   *                                                     double
   * @param string|null           $pTstBit               Parameter of type bit.
   *                                                     bit(8)
   * @param string|null           $pTstDate              Parameter of type date.
   *                                                     date
   * @param string|null           $pTstDatetime          Parameter of type datetime.
   *                                                     datetime
   * @param string|null           $pTstTimestamp         Parameter of type timestamp.
   *                                                     timestamp
   * @param string|null           $pTstTime              Parameter of type time.
   *                                                     time
   * @param int|null              $pTstYear              Parameter of type year.
   *                                                     year(4)
   * @param string|null           $pTstChar              Parameter of type char.
   *                                                     char(10) character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pTstVarchar           Parameter of type varchar.
   *                                                     varchar(10) character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pTstBinary            Parameter of type binary.
   *                                                     binary(10)
   * @param string|null           $pTstVarbinary         Parameter of type varbinary.
   *                                                     varbinary(10)
   * @param string|null           $pTstEnum              Parameter of type enum.
   *                                                     enum('a','b') character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pTstSet               Parameter of type set.
   *                                                     set('a','b') character set utf8mb4 collation utf8mb4_general_ci
   *
   * @return int
   *
   * @throws MySqlQueryErrorException
   */
  public function tstTestTypeHint(?int $pTstInt, ?int $pTstSmallint, ?int $pTstTinyint, ?int $pTstMediumint, ?int $pTstBigint, ?int $pTstIntUnsigned, ?int $pTstSmallintUnsigned, ?int $pTstTinyintUnsigned, ?int $pTstMediumintUnsigned, ?int $pTstBigintUnsigned, $pTstDecimal, $pTstDecimal0, ?float $pTstFloat, ?float $pTstDouble, ?string $pTstBit, ?string $pTstDate, ?string $pTstDatetime, ?string $pTstTimestamp, ?string $pTstTime, ?int $pTstYear, ?string $pTstChar, ?string $pTstVarchar, ?string $pTstBinary, ?string $pTstVarbinary, ?string $pTstEnum, ?string $pTstSet): int
  {
    return $this->executeNone('call tst_test_type_hint('.$this->quoteInt($pTstInt).','.$this->quoteInt($pTstSmallint).','.$this->quoteInt($pTstTinyint).','.$this->quoteInt($pTstMediumint).','.$this->quoteInt($pTstBigint).','.$this->quoteInt($pTstIntUnsigned).','.$this->quoteInt($pTstSmallintUnsigned).','.$this->quoteInt($pTstTinyintUnsigned).','.$this->quoteInt($pTstMediumintUnsigned).','.$this->quoteInt($pTstBigintUnsigned).','.$this->quoteDecimal($pTstDecimal).','.$this->quoteDecimal($pTstDecimal0).','.$this->quoteFloat($pTstFloat).','.$this->quoteFloat($pTstDouble).','.$this->quoteBit($pTstBit).','.$this->quoteString($pTstDate).','.$this->quoteString($pTstDatetime).','.$this->quoteString($pTstTimestamp).','.$this->quoteString($pTstTime).','.$this->quoteInt($pTstYear).','.$this->quoteString($pTstChar).','.$this->quoteString($pTstVarchar).','.$this->quoteBinary($pTstBinary).','.$this->quoteBinary($pTstVarbinary).','.$this->quoteString($pTstEnum).','.$this->quoteString($pTstSet).')');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for all possible types of parameters including LOB's.
   *
   * @param int|null              $pTstInt        Parameter of type int.
   *                                              int(11)
   * @param int|null              $pTstSmallint   Parameter of type smallint.
   *                                              smallint(6)
   * @param int|null              $pTstTinyint    Parameter of type tinyint.
   *                                              tinyint(4)
   * @param int|null              $pTstMediumint  Parameter of type mediumint.
   *                                              mediumint(9)
   * @param int|null              $pTstBigint     Parameter of type bigint.
   *                                              bigint(20)
   * @param int|float|string|null $pTstDecimal    Parameter of type decimal.
   *                                              decimal(10,2)
   * @param int|float|string|null $pTstDecimal0   Parameter of type decimal with 0 scale.
   *                                              decimal(65,0)
   * @param float|null            $pTstFloat      Parameter of type float.
   *                                              float
   * @param float|null            $pTstDouble     Parameter of type double.
   *                                              double
   * @param string|null           $pTstBit        Parameter of type bit.
   *                                              bit(8)
   * @param string|null           $pTstDate       Parameter of type date.
   *                                              date
   * @param string|null           $pTstDatetime   Parameter of type datetime.
   *                                              datetime
   * @param string|null           $pTstTimestamp  Parameter of type timestamp.
   *                                              timestamp
   * @param string|null           $pTstTime       Parameter of type time.
   *                                              time
   * @param int|null              $pTstYear       Parameter of type year.
   *                                              year(4)
   * @param string|null           $pTstChar       Parameter of type char.
   *                                              char(10) character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pTstVarchar    Parameter of type varchar.
   *                                              varchar(10) character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pTstBinary     Parameter of type binary.
   *                                              binary(10)
   * @param string|null           $pTstVarbinary  Parameter of type varbinary.
   *                                              varbinary(10)
   * @param string|null           $pTstTinyblob   Parameter of type tinyblob.
   *                                              tinyblob
   * @param string|null           $pTstBlob       Parameter of type blob.
   *                                              blob
   * @param string|null           $pTstMediumblob Parameter of type mediumblob.
   *                                              mediumblob
   * @param string|null           $pTstLongblob   Parameter of type longblob.
   *                                              longblob
   * @param string|null           $pTstTinytext   Parameter of type tinytext.
   *                                              tinytext character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pTstText       Parameter of type text.
   *                                              text character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pTstMediumtext Parameter of type mediumtext.
   *                                              mediumtext character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pTstLongtext   Parameter of type longtext.
   *                                              longtext character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pTstEnum       Parameter of type enum.
   *                                              enum('a','b') character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pTstSet        Parameter of type set.
   *                                              set('a','b') character set utf8mb4 collation utf8mb4_general_ci
   *
   * @return int
   *
   * @throws MySqlDataLayerException
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function tstTestTypeHintWithBlobs(?int $pTstInt, ?int $pTstSmallint, ?int $pTstTinyint, ?int $pTstMediumint, ?int $pTstBigint, $pTstDecimal, $pTstDecimal0, ?float $pTstFloat, ?float $pTstDouble, ?string $pTstBit, ?string $pTstDate, ?string $pTstDatetime, ?string $pTstTimestamp, ?string $pTstTime, ?int $pTstYear, ?string $pTstChar, ?string $pTstVarchar, ?string $pTstBinary, ?string $pTstVarbinary, ?string $pTstTinyblob, ?string $pTstBlob, ?string $pTstMediumblob, ?string $pTstLongblob, ?string $pTstTinytext, ?string $pTstText, ?string $pTstMediumtext, ?string $pTstLongtext, ?string $pTstEnum, ?string $pTstSet): int
  {
    $query = 'call tst_test_type_hint_with_blobs('.$this->quoteInt($pTstInt).','.$this->quoteInt($pTstSmallint).','.$this->quoteInt($pTstTinyint).','.$this->quoteInt($pTstMediumint).','.$this->quoteInt($pTstBigint).','.$this->quoteDecimal($pTstDecimal).','.$this->quoteDecimal($pTstDecimal0).','.$this->quoteFloat($pTstFloat).','.$this->quoteFloat($pTstDouble).','.$this->quoteBit($pTstBit).','.$this->quoteString($pTstDate).','.$this->quoteString($pTstDatetime).','.$this->quoteString($pTstTimestamp).','.$this->quoteString($pTstTime).','.$this->quoteInt($pTstYear).','.$this->quoteString($pTstChar).','.$this->quoteString($pTstVarchar).','.$this->quoteBinary($pTstBinary).','.$this->quoteBinary($pTstVarbinary).',?,?,?,?,?,?,?,?,'.$this->quoteString($pTstEnum).','.$this->quoteString($pTstSet).')';
    $stmt  = @$this->mysqli->prepare($query);
    if (!$stmt) throw $this->dataLayerError('mysqli::prepare');

    $null = null;
    $success = @$stmt->bind_param('bbbbbbbb', $null, $null, $null, $null, $null, $null, $null, $null);
    if (!$success) throw $this->dataLayerError('mysqli_stmt::bind_param');

    $this->getMaxAllowedPacket();

    $this->sendLongData($stmt, 0, $pTstTinyblob);
    $this->sendLongData($stmt, 1, $pTstBlob);
    $this->sendLongData($stmt, 2, $pTstMediumblob);
    $this->sendLongData($stmt, 3, $pTstLongblob);
    $this->sendLongData($stmt, 4, $pTstTinytext);
    $this->sendLongData($stmt, 5, $pTstText);
    $this->sendLongData($stmt, 6, $pTstMediumtext);
    $this->sendLongData($stmt, 7, $pTstLongtext);

    if ($this->logQueries)
    {
      $time0 = microtime(true);
    }

    try
    {
      $success = @$stmt->execute();
    }
    catch (\mysqli_sql_exception)
    {
      $success = false;
    }
    if (!$success)
    {
      throw $this->queryError('mysqli_stmt::execute', $query);
    }

    if ($this->logQueries)
    {
      $this->queryLog[] = ['query' => $query,
                           'time'  => microtime(true) - $time0];
    }

    $ret = $this->mysqli->affected_rows;

    $stmt->close();
    if ($this->mysqli->more_results()) $this->mysqli->next_result();

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test type hint with schema.
   *
   * @param int|null              $pTstInt               Parameter of type int.
   *                                                     int(11)
   * @param int|null              $pTstSmallint          Parameter of type smallint.
   *                                                     smallint(6)
   * @param int|null              $pTstTinyint           Parameter of type tinyint.
   *                                                     tinyint(4)
   * @param int|null              $pTstMediumint         Parameter of type mediumint.
   *                                                     mediumint(9)
   * @param int|null              $pTstBigint            Parameter of type bigint.
   *                                                     bigint(20)
   * @param int|null              $pTstIntUnsigned       Parameter of type int unsigned.
   *                                                     int(10) unsigned
   * @param int|null              $pTstSmallintUnsigned  Parameter of type smallint unsigned.
   *                                                     smallint(5) unsigned
   * @param int|null              $pTstTinyintUnsigned   Parameter of type tinyint unsigned.
   *                                                     tinyint(3) unsigned
   * @param int|null              $pTstMediumintUnsigned Parameter of type mediumint unsigned.
   *                                                     mediumint(8) unsigned
   * @param int|null              $pTstBigintUnsigned    Parameter of type bigint unsigned.
   *                                                     bigint(20) unsigned
   * @param int|float|string|null $pTstDecimal           Parameter of type decimal.
   *                                                     decimal(10,2)
   * @param int|float|string|null $pTstDecimal0          Parameter of type decimal with 0 scale.
   *                                                     decimal(65,0)
   * @param float|null            $pTstFloat             Parameter of type float.
   *                                                     float
   * @param float|null            $pTstDouble            Parameter of type double.
   *                                                     double
   * @param string|null           $pTstBit               Parameter of type bit.
   *                                                     bit(8)
   * @param string|null           $pTstDate              Parameter of type date.
   *                                                     date
   * @param string|null           $pTstDatetime          Parameter of type datetime.
   *                                                     datetime
   * @param string|null           $pTstTimestamp         Parameter of type timestamp.
   *                                                     timestamp
   * @param string|null           $pTstTime              Parameter of type time.
   *                                                     time
   * @param int|null              $pTstYear              Parameter of type year.
   *                                                     year(4)
   * @param string|null           $pTstChar              Parameter of type char.
   *                                                     char(10) character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pTstVarchar           Parameter of type varchar.
   *                                                     varchar(10) character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pTstBinary            Parameter of type binary.
   *                                                     binary(10)
   * @param string|null           $pTstVarbinary         Parameter of type varbinary.
   *                                                     varbinary(10)
   * @param string|null           $pTstEnum              Parameter of type enum.
   *                                                     enum('a','b') character set utf8mb4 collation utf8mb4_general_ci
   * @param string|null           $pTstSet               Parameter of type set.
   *                                                     set('a','b') character set utf8mb4 collation utf8mb4_general_ci
   *
   * @return int
   *
   * @throws MySqlQueryErrorException
   */
  public function tstTestTypeHintWithSchema(?int $pTstInt, ?int $pTstSmallint, ?int $pTstTinyint, ?int $pTstMediumint, ?int $pTstBigint, ?int $pTstIntUnsigned, ?int $pTstSmallintUnsigned, ?int $pTstTinyintUnsigned, ?int $pTstMediumintUnsigned, ?int $pTstBigintUnsigned, $pTstDecimal, $pTstDecimal0, ?float $pTstFloat, ?float $pTstDouble, ?string $pTstBit, ?string $pTstDate, ?string $pTstDatetime, ?string $pTstTimestamp, ?string $pTstTime, ?int $pTstYear, ?string $pTstChar, ?string $pTstVarchar, ?string $pTstBinary, ?string $pTstVarbinary, ?string $pTstEnum, ?string $pTstSet): int
  {
    return $this->executeNone('call tst_test_type_hint_with_schema('.$this->quoteInt($pTstInt).','.$this->quoteInt($pTstSmallint).','.$this->quoteInt($pTstTinyint).','.$this->quoteInt($pTstMediumint).','.$this->quoteInt($pTstBigint).','.$this->quoteInt($pTstIntUnsigned).','.$this->quoteInt($pTstSmallintUnsigned).','.$this->quoteInt($pTstTinyintUnsigned).','.$this->quoteInt($pTstMediumintUnsigned).','.$this->quoteInt($pTstBigintUnsigned).','.$this->quoteDecimal($pTstDecimal).','.$this->quoteDecimal($pTstDecimal0).','.$this->quoteFloat($pTstFloat).','.$this->quoteFloat($pTstDouble).','.$this->quoteBit($pTstBit).','.$this->quoteString($pTstDate).','.$this->quoteString($pTstDatetime).','.$this->quoteString($pTstTimestamp).','.$this->quoteString($pTstTime).','.$this->quoteInt($pTstYear).','.$this->quoteString($pTstChar).','.$this->quoteString($pTstVarchar).','.$this->quoteBinary($pTstBinary).','.$this->quoteBinary($pTstVarbinary).','.$this->quoteString($pTstEnum).','.$this->quoteString($pTstSet).')');
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
