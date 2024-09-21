<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql;

use SetBased\Stratum\Backend\StratumStyle;
use SetBased\Stratum\Middle\Exception\ResultException;
use SetBased\Stratum\MySql\Exception\MySqlQueryErrorException;

/**
 * Data layer for retrieving metadata and loading stored routines.
 */
class MySqlMetadataLayer
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The connection to the MySQL instance.
   *
   * @var MySqlDataLayer|null
   */
  private ?MySqlDataLayer $dl;

  /**
   * The Output decorator.
   *
   * @var StratumStyle
   */
  private StratumStyle $io;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * MySqlMetadataLayer constructor.
   *
   * @param MySqlDataLayer $dl The connection to the MySQL instance.
   * @param StratumStyle   $io The Output decorator.
   */
  public function __construct(MySqlDataLayer $dl, StratumStyle $io)
  {
    $this->dl = $dl;
    $this->io = $io;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects the details of all character sets.
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function allCharacterSets(): array
  {
    $sql = "
select CHARACTER_SET_NAME  as  character_set_name
,      MAXLEN              as  maxlen
from   information_schema.CHARACTER_SETS
order by CHARACTER_SET_NAME";

    return $this->executeRows($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects metadata of tables with a label column.
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function allLabelTables(): array
  {
    $sql = "
select t1.TABLE_NAME   as  table_name
,      t1.COLUMN_NAME  as  id
,      t2.COLUMN_NAME  as  label
from       information_schema.COLUMNS t1
inner join information_schema.COLUMNS t2 on t1.table_name = t2.table_name
where t1.table_schema = database()
and   t1.extra        = 'auto_increment'
and   t2.table_schema = database()
and   t2.column_name like '%%\\_label'";

    return $this->executeRows($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects all routines in the current schema.
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function allRoutines(): array
  {
    $sql = "
select ROUTINE_NAME          as  routine_name                            
,      ROUTINE_TYPE          as  routine_type           
,      SQL_MODE              as  sql_mode       
,      CHARACTER_SET_CLIENT  as  character_set_client                   
,      COLLATION_CONNECTION  as  collation_connection                   
from  information_schema.ROUTINES
where ROUTINE_SCHEMA = database()
and   ROUTINE_TYPE in ('PROCEDURE', 'FUNCTION')
order by routine_name";

    return $this->executeRows($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects metadata of all columns of all tables.
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function allTableColumns(): array
  {
    $sql = "
(
  select TABLE_NAME                as  table_name                                                       
  ,      COLUMN_NAME               as  column_name                                 
  ,      COLUMN_TYPE               as  column_type                                 
  ,      DATA_TYPE                 as  data_type                                 
  ,      CHARACTER_MAXIMUM_LENGTH  as  character_maximum_length                                 
  ,      CHARACTER_SET_NAME        as  character_set_name                                 
  ,      COLLATION_NAME            as  collation_name                                 
  ,      NUMERIC_PRECISION         as  numeric_precision                                 
  ,      NUMERIC_SCALE             as  numeric_scale                                 
  from   information_schema.COLUMNS
  where  TABLE_SCHEMA = database()
  and    TABLE_NAME  rlike '^[a-zA-Z0-9_]*$'
  and    COLUMN_NAME rlike '^[a-zA-Z0-9_]*$'
  order by TABLE_NAME
  ,        ORDINAL_POSITION
)

union all

(
  select concat(TABLE_SCHEMA,'.',TABLE_NAME)  as  table_name                                               
  ,      COLUMN_NAME                          as  column_name                     
  ,      COLUMN_TYPE                          as  column_type                     
  ,      DATA_TYPE                            as  data_type                     
  ,      CHARACTER_MAXIMUM_LENGTH             as  character_maximum_length                     
  ,      CHARACTER_SET_NAME                   as  character_set_name                     
  ,      COLLATION_NAME                       as  collation_name                     
  ,      NUMERIC_PRECISION                    as  numeric_precision                     
  ,      NUMERIC_SCALE                        as  numeric_scale                     
  from   information_schema.COLUMNS
  where  TABLE_NAME  rlike '^[a-zA-Z0-9_]*$'
  and    COLUMN_NAME rlike '^[a-zA-Z0-9_]*$'
  order by TABLE_SCHEMA
  ,        TABLE_NAME
  ,        ORDINAL_POSITION
)
";

    return $this->executeRows($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects all table names in a schema.
   *
   * @param string $schemaName The name of the schema.
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function allTablesNames(string $schemaName): array
  {
    $sql = sprintf("
select TABLE_NAME  as  table_name
from   information_schema.TABLES
where  TABLE_SCHEMA = %s
and    TABLE_TYPE   = 'BASE TABLE'
order by TABLE_NAME",
                   $this->dl->quoteString($schemaName));

    return $this->executeRows($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Class a stored procedure without arguments.
   *
   * @param string $procedureName The name of the procedure.
   *
   * @throws MySqlQueryErrorException
   */
  public function callProcedure(string $procedureName): void
  {
    $sql = sprintf('call %s()', $procedureName);

    $this->executeNone($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Checks if a table exists in the current schema.
   *
   * @param string $tableName The name of the table.
   *
   * @return bool
   *
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function checkTableExists(string $tableName): bool
  {
    $sql = sprintf('
select 1
from   information_schema.TABLES
where table_schema = database()
and   table_name   = %s',
                   $this->dl->quoteString($tableName));

    return !empty($this->executeSingleton0($sql));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Describes a table.
   *
   * @param string $tableName The table name.
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function describeTable(string $tableName): array
  {
    $sql = sprintf('describe `%s`', $tableName);

    return $this->executeRows($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Closes the connection to the MySQL instance, if connected.
   */
  public function disconnect(): void
  {
    if ($this->dl!==null)
    {
      $this->dl->disconnect();
      $this->dl = null;
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Drops a routine if it exists.
   *
   * @param string $routineType The type of the routine (function of procedure).
   * @param string $routineName The name of the routine.
   *
   * @throws MySqlQueryErrorException
   */
  public function dropRoutine(string $routineType, string $routineName): void
  {
    $sql = sprintf('drop %s if exists `%s`', $routineType, $routineName);

    $this->executeNone($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Drops a temporary table.
   *
   * @param string $tableName the name of the temporary table.
   *
   * @throws MySqlQueryErrorException
   */
  public function dropTemporaryTable(string $tableName): void
  {
    $sql = sprintf('drop temporary table `%s`', $tableName);

    $this->executeNone($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @param string $sql The SQL statement.
   *
   * @return int The number of affected rows (if any).
   *
   * @throws MySqlQueryErrorException
   */
  public function executeNone(string $sql): int
  {
    $this->logQuery($sql);

    return $this->dl->executeNone($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes a query that returns 0 or 1 row.
   * Throws an exception if the query selects 2 or more rows.
   *
   * @param string $sql The SQL statement.
   *
   * @return array|null The selected row.
   *
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function executeRow0(string $sql): ?array
  {
    $this->logQuery($sql);

    return $this->dl->executeRow0($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes a query that returns 1 and only 1 row.
   * Throws an exception if the query selects none, 2 or more rows.
   *
   * @param string $sql The SQL statement.
   *
   * @return array The selected row.
   *
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function executeRow1(string $sql): array
  {
    $this->logQuery($sql);

    return $this->dl->executeRow1($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes a query that returns 0 or more rows.
   *
   * @param string $sql The SQL statement.
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function executeRows(string $sql): array
  {
    $this->logQuery($sql);

    return $this->dl->executeRows($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes a query that returns 0 or 1 row.
   * Throws an exception if the query selects 2 or more rows.
   *
   * @param string $sql The SQL statement.
   *
   * @return mixed The selected row.
   *
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function executeSingleton0(string $sql): mixed
  {
    $this->logQuery($sql);

    return $this->dl->executeSingleton0($sql);
  }
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes a query that returns 1 and only 1 row with 1 column.
   * Throws an exception if the query selects none, 2 or more rows.
   *
   * @param string $sql The SQL statement.
   *
   * @return mixed The selected row.
   *
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function executeSingleton1(string $sql): mixed
  {
    $this->logQuery($sql);

    return $this->dl->executeSingleton1($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects the SQL mode in the order as preferred by MySQL.
   *
   * @return string
   *
   * @throws MySqlQueryErrorException
   * @throws ResultException
   */
  public function getCanonicalSqlMode(): string
  {
    $sql = 'select @@sql_mode';

    return (string)$this->executeSingleton1($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects all labels from a table with labels.
   *
   * @param string $tableName       The table name.
   * @param string $idColumnName    The name of the auto increment column.
   * @param string $labelColumnName The name of the column with labels.
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function labelsFromTable(string $tableName, string $idColumnName, string $labelColumnName): array
  {
    $sql = "
select `%s`  as id
,      `%s`  as label
from   `%s`
where   nullif(`%s`,'') is not null";

    $sql = sprintf($sql, $idColumnName, $labelColumnName, $tableName, $labelColumnName);

    return $this->executeRows($sql);
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
    return $this->dl->realEscapeString($string);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects the parameters of a stored routine.
   *
   * @param string $routineName The name of the routine.
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function routineParameters(string $routineName): array
  {
    $sql = sprintf("
select t2.PARAMETER_NAME      as  parameter_name                            
,      t2.DATA_TYPE           as  data_type             
,      t2.NUMERIC_PRECISION   as  numeric_precision                     
,      t2.NUMERIC_SCALE       as  numeric_scale                 
,      t2.CHARACTER_SET_NAME  as  character_set_name                      
,      t2.COLLATION_NAME      as  collation_name                  
,      t2.DTD_IDENTIFIER      as  dtd_identifier                  
from information_schema.ROUTINES   t1
join information_schema.PARAMETERS t2  on  t2.SPECIFIC_SCHEMA = t1.ROUTINE_SCHEMA and
                                           t2.SPECIFIC_NAME   = t1.ROUTINE_NAME and
                                           t2.PARAMETER_MODE  is not null
where t1.ROUTINE_SCHEMA = database()
and   t1.ROUTINE_NAME   = '%s'",
                   $routineName);

    return $this->executeRows($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Sets the default character set and collation.
   *
   * @param string $characterSet The character set.
   * @param string $collate      The collation.
   *
   * @throws MySqlQueryErrorException
   */
  public function setCharacterSet(string $characterSet, string $collate): void
  {
    $sql = sprintf('set names %s collate %s', $this->dl->quoteString($characterSet), $this->dl->quoteString($collate));

    $this->executeNone($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Sets the SQL mode.
   *
   * @param string $sqlMode The SQL mode.
   *
   * @throws MySqlQueryErrorException
   */
  public function setSqlMode(string $sqlMode): void
  {
    $sql = sprintf('set sql_mode = %s', $this->dl->quoteString($sqlMode));

    $this->executeNone($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects metadata of a column of table.
   *
   * @param string|null $schemaName The name of the table schema. If null the current schema.
   * @param string      $tableName  The name of the table.
   * @param string      $columnName The name of the column.
   *
   * @return array
   *
   * @throws MySqlQueryErrorException
   */
  public function tableColumn(?string $schemaName, string $tableName, string $columnName): array
  {
    $sql = sprintf('
select COLUMN_NAME         as  column_name
,      COLUMN_TYPE         as  column_type
,      DATA_TYPE           as  data_type
,      NUMERIC_PRECISION   as  numeric_precision
,      NUMERIC_SCALE       as  numeric_scale
,      CHARACTER_SET_NAME  as  character_set_name
,      COLLATION_NAME      as  collation_name
from   information_schema.COLUMNS
where  TABLE_SCHEMA = ifnull(%s, database())
and    TABLE_NAME   = %s
and    COLUMN_NAME  = %s',
                   $this->dl->quoteString($schemaName),
                   $this->dl->quoteString($tableName),
                   $this->dl->quoteString($columnName));

    return $this->executeRow1($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects metadata of all columns of table.
   *
   * @param string $schemaName The name of the table schema.
   * @param string $tableName  The name of the table.
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function tableColumns(string $schemaName, string $tableName): array
  {
    $sql = sprintf('
select COLUMN_NAME         as  column_name
,      COLUMN_TYPE         as  column_type
,      IS_NULLABLE         as  is_nullable
,      CHARACTER_SET_NAME  as  character_set_name
,      COLLATION_NAME      as  collation_name
,      EXTRA               as  extra
from   information_schema.COLUMNS
where  TABLE_SCHEMA = %s
and    TABLE_NAME   = %s
order by ORDINAL_POSITION',
                   $this->dl->quoteString($schemaName),
                   $this->dl->quoteString($tableName));

    return $this->executeRows($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects the primary key from a table (if any).
   *
   * @param string $schemaName The name of the table schema.
   * @param string $tableName  The name of the table.
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function tablePrimaryKey(string $schemaName, string $tableName): array
  {
    $sql = sprintf('
show index from `%s`.`%s`
where Key_name = \'PRIMARY\'',
                   $schemaName,
                   $tableName);

    return $this->executeRows($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects all unique keys from table.
   *
   * @param string $schemaName The name of the table schema.
   * @param string $tableName  The name of the table.
   *
   * @return array[]
   *
   * @throws MySqlQueryErrorException
   */
  public function tableUniqueIndexes(string $schemaName, string $tableName): array
  {
    $sql = sprintf('
show index from `%s`.`%s`
where Non_unique = 0',
                   $schemaName,
                   $tableName);

    return $this->executeRows($sql);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Logs the query on the console.
   *
   * @param string $sql The query.
   */
  private function logQuery(string $sql): void
  {
    $sql = trim($sql);

    if (str_contains($sql, "\n"))
    {
      // Query is a multi line query.
      $this->io->logVeryVerbose('Executing query:');
      $this->io->logVeryVerbose('<sql>%s</sql>', $sql);
    }
    else
    {
      // Query is a single line query.
      $this->io->logVeryVerbose('Executing query: <sql>%s</sql>', $sql);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
