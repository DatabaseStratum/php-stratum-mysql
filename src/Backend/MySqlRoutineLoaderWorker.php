<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Backend;

use SetBased\Stratum\Backend\RoutineLoaderWorker;
use SetBased\Stratum\Common\Backend\CommonRoutineLoaderWorker;
use SetBased\Stratum\Common\Helper\CommonDataTypeHelper;
use SetBased\Stratum\Common\Loader\CommonRoutineLoader;
use SetBased\Stratum\Common\Loader\Helper\EscapeHelper;
use SetBased\Stratum\Common\Loader\Helper\LoaderContext;
use SetBased\Stratum\Middle\Helper\RowSetHelper;
use SetBased\Stratum\MySql\Loader\Helper\MySqlDataTypeHelper;
use SetBased\Stratum\MySql\Loader\Helper\MySqlEscapeHelper;
use SetBased\Stratum\MySql\Loader\Helper\SqlModeHelper;
use SetBased\Stratum\MySql\Loader\MySqlRoutineLoader;
use SetBased\Stratum\MySql\MySqlDataLayer;
use SetBased\Stratum\MySql\MySqlDefaultConnector;
use SetBased\Stratum\MySql\MySqlMetadataLayer;

/**
 * Command for loading stored routines into a MySQL instance from pseudo SQL files.
 */
class MySqlRoutineLoaderWorker extends CommonRoutineLoaderWorker implements RoutineLoaderWorker
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The maximum column size in bytes.
   */
  const MAX_COLUMN_SIZE = 65532;

  /**
   * Maximum length of a char.
   */
  const MAX_LENGTH_CHAR = 255;

  /**
   * Maximum length of a varchar.
   */
  const MAX_LENGTH_VARCHAR = 4096;

  /**
   * Maximum length of a binary.
   */
  const MAX_LENGTH_BINARY = 255;

  /**
   * Maximum length of a varbinary.
   */
  const MAX_LENGTH_VARBINARY = 4096;

  /**
   * The metadata layer.
   *
   * @var MySqlMetadataLayer|null
   */
  protected ?MySqlMetadataLayer $dl;

  /**
   * The default character set under which the stored routine will be loaded and run.
   *
   * @var string
   */
  private string $characterSetClient;

  /**
   * Details of all character sets.
   *
   * @var array[]|null
   */
  private ?array $characterSets = null;

  /**
   * The default collate under which the stored routine will be loaded and run.
   *
   * @var string
   */
  private string $collationConnection;

  /**
   * The SQL mode under which the stored routine will be loaded and run.
   *
   * @var string
   */
  private string $sqlMode;

  /**
   * The helper object for SQL modes.
   *
   * @var SqlModeHelper
   */
  private SqlModeHelper $sqlModeHelper;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Disconnects from MySQL instance.
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
   * Connects to a MySQL instance.
   */
  protected function connect(): void
  {
    $host     = $this->config->manString('database.host');
    $user     = $this->config->manString('database.user');
    $password = $this->config->manString('database.password');
    $database = $this->config->manString('database.database');
    $port     = $this->config->manInt('database.port', 3306);

    $connector = new MySqlDefaultConnector($host, $user, $password, $database, $port);
    $dataLayer = new MySqlDataLayer($connector);
    $dataLayer->connect();

    $this->dl = new MySqlMetadataLayer($dataLayer, $this->io);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function createDataTypeHelper(): CommonDataTypeHelper
  {
    return new MySqlDataTypeHelper();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @@inheritdoc
   */
  protected function createEscaper(): EscapeHelper
  {
    return new MySqlEscapeHelper($this->dl);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function createRoutineLoader(LoaderContext $context): CommonRoutineLoader
  {
    return new MySqlRoutineLoader($this->io,
                                  $this->dl,
                                  $this->sqlModeHelper,
                                  $this->characterSetClient,
                                  $this->collationConnection);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function dropStoredRoutine(array $rdbmsMetadata): void
  {
    $this->dl->dropRoutine($rdbmsMetadata['routine_type'], $rdbmsMetadata['routine_name']);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function fetchColumnTypes(): void
  {
    $columns = $this->dl->allTableColumns();

    $this->saveColumnTypesExact($columns);
    $this->saveColumnTypesMaxLength($columns);

    $this->io->text(sprintf('Selected %d column types for substitution', sizeof($columns)));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function fetchRdbmsMetadata(): array
  {
    return $this->dl->allRoutines();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function initRdbmsSpecific(): void
  {
    $this->sqlModeHelper = new SqlModeHelper($this->dl, $this->sqlMode);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function phpStratumMetadataRevision(): string
  {
    return parent::phpStratumMetadataRevision().'.1';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function readConfigFile(): void
  {
    parent::readConfigFile();

    $this->characterSetClient  = $this->config->manString('database.character_set_client', 'utf8mb4');
    $this->collationConnection = $this->config->manString('database.collation_connection', 'utf8mb4_general_ci');
    $this->sqlMode             = $this->config->manString('loader.sql_mode');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the maximum number of characters in a VARCHAR or CHAR.
   *
   * @param string $characterSetName The name of the character set of the column.
   */
  private function maxCharacters(string $characterSetName): ?int
  {
    if ($this->characterSets===null)
    {
      $this->characterSets = $this->dl->allCharacterSets();
    }

    $key = RowSetHelper::searchInRowSet($this->characterSets, 'character_set_name', $characterSetName);
    if ($key===null)
    {
      return null;
    }

    $size = $this->characterSets[$key]['maxlen'];

    return (int)floor(self::MAX_COLUMN_SIZE / $size);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Saves the exact column types as type hints.
   *
   * @param array[] $columns The details of all table columns.
   */
  private function saveColumnTypesExact(array $columns): void
  {
    foreach ($columns as $column)
    {
      $hint  = $column['table_name'].'.'.$column['column_name'];
      $value = $column['column_type'];

      if ($column['character_set_name']!==null)
      {
        $value .= ' character set '.$column['character_set_name'];
      }

      $this->addTypeHint($hint, $value);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Saves the column types with maximum length as type hints.
   *
   * @param array[] $columns The details of all table columns.
   */
  private function saveColumnTypesMaxLength(array $columns): void
  {
    foreach ($columns as $column)
    {
      $hint = $column['table_name'].'.'.$column['column_name'].'%max';

      switch ($column['data_type'])
      {
        case 'char':
        case 'varchar':
          $max = $this->maxCharacters($column['character_set_name']);
          if ($max!==null)
          {
            $value = sprintf('%s(%d) character set %s',
                             $column['data_type'],
                             $max,
                             $column['character_set_name']);
          }
          else
          {
            $value = null;
          }
          break;

        case 'binary':
          $value = sprintf('%s(%d)', $column['data_type'], self::MAX_LENGTH_BINARY);
          break;

        case 'varbinary':
          $value = sprintf('%s(%d)', $column['data_type'], self::MAX_LENGTH_VARBINARY);
          break;

        default:
          $value = null;
      }

      if ($value!==null)
      {
        $this->addTypeHint($hint, $value);
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
