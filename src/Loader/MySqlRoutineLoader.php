<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Loader;

use SetBased\Exception\FallenException;
use SetBased\Helper\Cast;
use SetBased\Stratum\Backend\StratumStyle;
use SetBased\Stratum\Common\Exception\RoutineLoaderException;
use SetBased\Stratum\Common\Loader\CommonRoutineLoader;
use SetBased\Stratum\Common\Loader\Helper\LoaderContext;
use SetBased\Stratum\MySql\Loader\Helper\RoutineParametersHelper;
use SetBased\Stratum\MySql\Loader\Helper\SqlModeHelper;
use SetBased\Stratum\MySql\MySqlMetadataLayer;

/**
 * Class for loading a single stored routine into a MySQL instance from pseudo SQL file.
 */
class MySqlRoutineLoader extends CommonRoutineLoader
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * MySQL's and MariaDB's SQL/PSM syntax.
   */
  const int SQL_PSM_SYNTAX = 1;

  /**
   * Oracle PL/SQL syntax.
   */
  const int PL_SQL_SYNTAX = 2;

  /**
   * The default character set under which the stored routine will be loaded and run.
   *
   * @var string
   */
  private string $characterSet;

  /**
   * The default collate under which the stored routine will be loaded and run.
   *
   * @var string
   */
  private string $collate;

  /**
   * The metadata layer.
   *
   * @var MySqlMetadataLayer
   */
  private MySqlMetadataLayer $dl;

  /**
   * The SQL mode helper object.
   *
   * @var SqlModeHelper
   */
  private SqlModeHelper $sqlModeHelper;

  /**
   * The syntax of the stored routine. Either SQL_PSM_SYNTAX or PL_SQL_SYNTAX.
   *
   * @var int
   */
  private int $syntax;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param StratumStyle       $io            The output decorator.
   * @param MySqlMetadataLayer $dl            The metadata layer.
   * @param SqlModeHelper      $sqlModeHelper
   * @param string             $characterSet  The default character set under which the stored routine will be loaded
   *                                          and run.
   * @param string             $collate       The key or index columns (depending on the designation type) of the
   *                                          stored routine.
   */
  public function __construct(StratumStyle       $io,
                              MySqlMetadataLayer $dl,
                              SqlModeHelper      $sqlModeHelper,
                              string             $characterSet,
                              string             $collate)
  {
    parent::__construct($io);

    $this->dl            = $dl;
    $this->sqlModeHelper = $sqlModeHelper;
    $this->characterSet  = $characterSet;
    $this->collate       = $collate;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extract column metadata from the rows returned by the SQL statement 'describe table'.
   *
   * @param array $description The description of the table.
   */
  private static function extractColumnsFromTableDescription(array $description): array
  {
    $ret = [];

    foreach ($description as $column)
    {
      preg_match('/^(?<data_type>\w+)(?<extra>.*)?$/', $column['Type'], $parts1);

      $tmp = ['column_name'       => $column['Field'],
              'data_type'         => $parts1['data_type'],
              'numeric_precision' => null,
              'numeric_scale'     => null,
              'dtd_identifier'    => $column['Type']];

      switch ($parts1[1])
      {
        case 'tinyint':
          preg_match('/^\((?<precision>\d+)\)/', $parts1['extra'], $parts2);
          $tmp['numeric_precision'] = Cast::toManInt($parts2['precision'] ?? 4);
          $tmp['numeric_scale']     = 0;
          break;

        case 'smallint':
          preg_match('/^\((?<precision>\d+)\)/', $parts1['extra'], $parts2);
          $tmp['numeric_precision'] = Cast::toManInt($parts2['precision'] ?? 6);
          $tmp['numeric_scale']     = 0;
          break;

        case 'mediumint':
          preg_match('/^\((?<precision>\d+)\)/', $parts1['extra'], $parts2);
          $tmp['numeric_precision'] = Cast::toManInt($parts2['precision'] ?? 9);
          $tmp['numeric_scale']     = 0;
          break;

        case 'int':
          preg_match('/^\((?<precision>\d+)\)/', $parts1['extra'], $parts2);
          $tmp['numeric_precision'] = Cast::toManInt($parts2['precision'] ?? 11);
          $tmp['numeric_scale']     = 0;
          break;

        case 'bigint':
          preg_match('/^\((?<precision>\d+)\)/', $parts1['extra'], $parts2);
          $tmp['numeric_precision'] = Cast::toManInt($parts2['precision'] ?? 20);
          $tmp['numeric_scale']     = 0;
          break;

        case 'year':
          // Nothing to do.
          break;

        case 'float':
          $tmp['numeric_precision'] = 12;
          break;

        case 'double':
          $tmp['numeric_precision'] = 22;
          break;

        case 'binary':
        case 'char':
        case 'varbinary':
        case 'varchar':
          // Nothing to do (binary) strings.
          break;

        case 'decimal':
          preg_match('/^\((?<precision>\d+),(<?scale>\d+)\)$/', $parts1['extra'], $parts2);
          $tmp['numeric_precision'] = Cast::toManInt($parts2['precision'] ?? 65);
          $tmp['numeric_scale']     = Cast::toManInt($parts2['scale'] ?? 0);
          break;

        case 'time':
        case 'timestamp':
        case 'date':
        case 'datetime':
          // Nothing to do date and time.
          break;

        case 'enum':
        case 'set':
          // Nothing to do sets.
          break;

        case 'bit':
          preg_match('/^\((?<precision>\d+)\)$/', $parts1['extra'], $parts2);
          $tmp['numeric_precision'] = Cast::toManInt($parts2['precision']);
          break;

        case 'tinytext':
        case 'text':
        case 'mediumtext':
        case 'longtext':
        case 'tinyblob':
        case 'blob':
        case 'mediumblob':
        case 'longblob':
          // Nothing to do CLOBs and BLOBs.
          break;

        default:
          throw new FallenException('data type', $parts1[1]);
      }

      $ret[] = $tmp;
    }

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Drops a stored routine.
   *
   * @param LoaderContext $context The loader context.
   */
  protected function dropStoredRoutine(LoaderContext $context): void
  {
    if (!empty($context->oldRdbmsMetadata))
    {
      $this->dl->dropRoutine($context->oldRdbmsMetadata['routine_type'],
                             $context->oldRdbmsMetadata['routine_name']);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function extractInsertMultipleTableColumns(LoaderContext $context): void
  {
    if ($context->docBlock->getDesignation()['type']!=='insert_multiple')
    {
      return;
    }

    $tableName        = $context->docBlock->getDesignation()['table_name'];
    $keys             = $context->docBlock->getDesignation()['keys'];
    $isTemporaryTable = !$this->dl->checkTableExists($tableName);

    // Create temporary table if table is a temporary table.
    if ($isTemporaryTable)
    {
      $this->dl->callProcedure($context->storedRoutine->getName());
    }

    $rdbmsColumns = $this->dl->describeTable($tableName);

    // Drop temporary table if table is temporary.
    if ($isTemporaryTable)
    {
      $this->dl->dropTemporaryTable($tableName);
    }

    // Check number of columns in the table match the number of fields given in the designation type.
    $n1 = sizeof($keys);
    $n2 = sizeof($rdbmsColumns);
    if ($n1!==$n2)
    {
      throw new RoutineLoaderException("Number of fields %d and number of columns %d don't match.", $n1, $n2);
    }

    $context->newPhpStratumMetadata['insert_multiple_table_columns'] = self::extractColumnsFromTableDescription($rdbmsColumns);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function extractName(LoaderContext $context): void
  {
    $n = preg_match('/create\s+(?<type>procedure|function)\s+(?<name>[a-zA-Z0-9_]+)/i',
                    $context->storedRoutine->getCode(),
                    $matches);
    if ($n!==1)
    {
      throw new RoutineLoaderException('Unable to find the stored routine name and type.');
    }

    $context->storedRoutine->setName($matches['name']);
    $context->storedRoutine->setType($matches['type']);
    $this->extractSyntax($context);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function extractStoredRoutineParameters(LoaderContext $context): void
  {
    $routineParametersHelper = new RoutineParametersHelper($this->dl);
    $context->storedRoutine->setParameters($routineParametersHelper->getParameters($context));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function loadRoutineFile(LoaderContext $context): void
  {
    if ($this->syntax===self::PL_SQL_SYNTAX)
    {
      $this->sqlModeHelper->addIfRequiredOracleMode();
    }
    else
    {
      $this->sqlModeHelper->removeIfRequiredOracleMode();
    }

    $this->dropStoredRoutine($context);
    $this->dl->setCharacterSet($this->characterSet, $this->collate);
    $this->dl->executeNone($this->routineSourceCode);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether the source file must be load or reloaded.
   */
  protected function mustReload(LoaderContext $context): bool
  {
    if (parent::mustReload($context))
    {
      return true;
    }

    if (!$this->sqlModeHelper->compare($context->oldRdbmsMetadata['sql_mode']))
    {
      return true;
    }

    if ($context->oldRdbmsMetadata['character_set_client']!==$this->characterSet)
    {
      return true;
    }

    if ($context->oldRdbmsMetadata['collation_connection']!==$this->collate)
    {
      return true;
    }

    return false;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function updateMetadata(LoaderContext $context): void
  {
    parent::updateMetadata($context);

    $context->newPhpStratumMetadata['character_set_client'] = $this->characterSet;
    $context->newPhpStratumMetadata['collation_connection'] = $this->collate;
    $context->newPhpStratumMetadata['sql_mode']             = $this->sqlModeHelper->getCanonicalSqlMode();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Detects the syntax of the stored procedure. Either SQL/PSM or PL/SQL.
   *
   * @param LoaderContext $context The loader context.
   */
  private function extractSyntax(LoaderContext $context): void
  {
    if ($this->sqlModeHelper->hasOracleMode())
    {
      $key1 = $this->findFirstMatchingLine($context, '/^\s*(as|is)\s*$/i');
      $key2 = $this->findFirstMatchingLine($context, '/^\s*begin\s*$/i');

      if ($key1!==null && $key2!==null && $key1<$key2)
      {
        $this->syntax = self::PL_SQL_SYNTAX;
      }
      else
      {
        $this->syntax = self::SQL_PSM_SYNTAX;
      }
    }
    else
    {
      $this->syntax = self::SQL_PSM_SYNTAX;
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the key of the source line that match a regex pattern.
   *
   * @param LoaderContext $context The loader context.
   * @param string        $pattern The regex pattern.
   *
   * @return int|null
   */
  private function findFirstMatchingLine(LoaderContext $context, string $pattern): ?int
  {
    foreach ($context->storedRoutine->getCodeLines() as $key => $line)
    {
      if (preg_match($pattern, $line)===1)
      {
        return $key;
      }
    }

    return null;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
