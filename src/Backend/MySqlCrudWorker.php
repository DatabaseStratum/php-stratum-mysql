<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Backend;

use SetBased\Exception\FallenException;
use SetBased\Stratum\Backend\CrudWorker;
use SetBased\Stratum\MySql\Crud\Helper\DeleteRoutine;
use SetBased\Stratum\MySql\Crud\Helper\InsertRoutine;
use SetBased\Stratum\MySql\Crud\Helper\SelectRoutine;
use SetBased\Stratum\MySql\Crud\Helper\UpdateRoutine;

/**
 * Creates stored procedures for CRUD operations.
 */
class MySqlCrudWorker extends MySqlWorker implements CrudWorker
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates the code for a stored routine.
   *
   * @param string $tableName   The name of the table.
   * @param string $operation   The operation {insert|update|delete|select}.
   * @param string $routineName The name of the generated routine.
   */
  public function generateRoutine(string $tableName, string $operation, string $routineName): string
  {
    $schemaName = $this->settings->manString('database.database');

    $this->connect();

    $tableColumns  = $this->dl->tableColumns($schemaName, $tableName);
    $primaryKey    = $this->dl->tablePrimaryKey($schemaName, $tableName);
    $uniqueIndexes = $this->dl->tableUniqueIndexes($schemaName, $tableName);

    switch ($operation)
    {
      case 'update':
        $routine = new UpdateRoutine($tableName, $routineName, $tableColumns, $primaryKey, $uniqueIndexes);
        break;

      case 'delete':
        $routine = new DeleteRoutine($tableName, $routineName, $tableColumns, $primaryKey, $uniqueIndexes);
        break;

      case 'select':
        $routine = new SelectRoutine($tableName, $routineName, $tableColumns, $primaryKey, $uniqueIndexes);
        break;

      case 'insert':
        $routine = new InsertRoutine($tableName, $routineName, $tableColumns, $primaryKey, $uniqueIndexes);
        break;

      default:
        throw new FallenException('operation', $operation);
    }
    $this->disconnect();

    return $routine->getCode();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a list of all supported operations by the worker.
   */
  public function operations(): array
  {
    return ['insert', 'update', 'delete', 'select'];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a list of all tables in de database of the backend.
   */
  public function tables(): array
  {
    $this->connect();
    $schema = $this->settings->manString('database.database');
    $rows   = $this->dl->allTablesNames($schema);
    $this->disconnect();

    $tables = [];
    foreach ($rows as $row)
    {
      $tables[] = $row['table_name'];
    }

    return $tables;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
