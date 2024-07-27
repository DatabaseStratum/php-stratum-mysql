<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Helper\Crud;

use SetBased\Helper\CodeStore\MySqlCompoundSyntaxCodeStore;
use SetBased\Stratum\Middle\Helper\RowSetHelper;

/**
 * Abstract parent class for classes for generating CRUD stored routines.
 */
abstract class BaseRoutine
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The code of the generated stored routine.
   *
   * @var MySqlCompoundSyntaxCodeStore
   */
  protected MySqlCompoundSyntaxCodeStore $codeStore;

  /**
   * Metadata about the stored routine parameters.
   *
   * @var array[]
   */
  protected array $parameters;

  /**
   * The primary key of the table.
   *
   * @var array[]
   */
  protected array $primaryKey;

  /**
   * The name of the generated stored procedure.
   *
   * @var string
   */
  protected string $routineName;

  /**
   * Metadata about the columns of the table.
   *
   * @var array[]
   */
  protected array $tableColumns;

  /**
   * The name of the table for which a stored routine must be generated.
   *
   * @var string
   */
  protected string $tableName;

  /**
   * The unique indexes on the table.
   *
   * @var array[]
   */
  protected array $uniqueIndexes;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param string  $tableName     The name of the table for which a stored routine must be generated.
   * @param string  $routineName   The name of the generated stored procedure.
   * @param array[] $tableColumns  Metadata about the columns of the table.
   * @param array[] $primaryKey    The primary key of the table.
   * @param array[] $uniqueIndexes The unique indexes on the table.
   */
  public function __construct(string $tableName,
                              string $routineName,
                              array  $tableColumns,
                              array  $primaryKey,
                              array  $uniqueIndexes)
  {
    $this->tableName     = $tableName;
    $this->routineName   = $routineName;
    $this->tableColumns  = $tableColumns;
    $this->primaryKey    = $primaryKey;
    $this->uniqueIndexes = $uniqueIndexes;

    $this->codeStore = new MySqlCompoundSyntaxCodeStore();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the generated code of the stored routine.
   *
   * @return string
   */
  public function getCode(): string
  {
    $this->generateRoutine();

    return $this->codeStore->getCode();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns tre if and only if the table has an auto_increment column.
   *
   * @param array[] $columns Columns from table.
   *
   * @return bool
   */
  protected function checkAutoIncrement(array $columns): bool
  {
    foreach ($columns as $column)
    {
      if ($column['extra']=='auto_increment')
      {
        return true;
      }
    }

    return false;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates the body of the stored routine.
   */
  abstract protected function generateBody(): void;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates the doc block for the stored routine.
   */
  abstract protected function generateDocBlock(): void;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates the doc block for a stored routine that uses all columns and (preferably) a key (i.e. update).
   */
  protected function generateDocBlockAllColumnsWithKeyList(): void
  {
    $this->codeStore->append('/**');
    $this->codeStore->append(' * @todo describe routine', false);
    $this->codeStore->append(' * ', false);

    $padding = $this->maxColumnNameLength($this->tableColumns);
    $format  = sprintf(' * @param p_%%-%ds @todo describe parameter', $padding);
    foreach ($this->tableColumns as $column)
    {
      $this->codeStore->append(sprintf($format, $column['column_name']), false);
    }

    $this->generateKeyListInDocBlock();

    $this->codeStore->append(' */', false);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates the doc block for a stored routine that uses all columns except auto increment column (i.e. insert).
   */
  protected function generateDocBlockAllColumnsWithoutAutoIncrement(): void
  {
    $this->codeStore->append('/**');
    $this->codeStore->append(' * @todo describe routine', false);
    $this->codeStore->append(' * ', false);

    $columns = $this->tableColumnsWithoutAutoIncrement();
    $width   = $this->maxColumnNameLength($columns);
    $format  = sprintf(' * @param p_%%-%ds @todo describe parameter', $width);
    foreach ($this->tableColumns as $column)
    {
      $this->codeStore->append(sprintf($format, $column['column_name']), false);
    }

    $this->codeStore->append(' */', false);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates the doc block for a stored routine that (preferably) uses a key (i.e. select and delete).
   */
  protected function generateDocBlockWithKey(): void
  {
    $this->codeStore->append('/**');
    $this->codeStore->append(' * @todo describe routine', false);
    $this->codeStore->append(' * ', false);

    $columns = $this->keyColumns();
    $padding = $this->maxColumnNameLength($columns);
    $format  = sprintf(' * @param p_%%-%ds @todo describe parameter', $padding);
    foreach ($columns as $column)
    {
      $this->codeStore->append(sprintf($format, $column['column_name']), false);
    }

    $this->generateKeyListInDocBlock();

    $this->codeStore->append(' */', false);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates an overview of all keys on a table in a doc block.
   */
  protected function generateKeyListInDocBlock(): void
  {
    $keys = $this->keyList();
    if (!empty($keys))
    {
      if (sizeof($keys)>1 || !isset($keys['PRIMARY']))
      {
        $this->codeStore->append(' * ', false);
        $this->codeStore->append(' * Possible keys:', false);
        foreach ($keys as $keyName => $columns)
        {
          $this->codeStore->append(sprintf(' *   %s: %s', $keyName, $columns), false);
        }
      }
    }
    else
    {
      $this->codeStore->append(' * ', false);
      $this->codeStore->append(' * NOTE: Table does not have a key.', false);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates the function name and parameters of the stored routine.
   */
  abstract protected function generateRoutineDeclaration(): void;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates the function name and parameters of a stored routine that uses all columns except auto increment column
   * (i.e. insert).
   */
  protected function generateRoutineDeclarationAllColumnsWithoutAutoIncrement(): void
  {
    $this->codeStore->append(sprintf('create procedure %s(', $this->routineName));

    $columns = $this->tableColumnsWithoutAutoIncrement();
    $padding = $this->maxColumnNameLength($columns);
    $offset  = mb_strlen($this->codeStore->getLastLine());

    $first = true;
    foreach ($columns as $column)
    {
      if ($first)
      {
        $format = sprintf('in p_%%-%ds @%%s.%%s%%s@', $padding);
        $this->codeStore->appendToLastLine(strtolower(sprintf($format,
                                                              $column['column_name'],
                                                              $this->tableName,
                                                              $column['column_name'],
                                                              '%type')));
      }
      else
      {
        $format = sprintf('%%%ds p_%%-%ds @%%s.%%s%%s@', $offset + 2, $padding);
        $this->codeStore->append(strtolower(sprintf($format,
                                                    'in',
                                                    $column['column_name'],
                                                    $this->tableName,
                                                    $column['column_name'],
                                                    '%type')),
                                 false);
      }

      if ($column!=end($this->tableColumns))
      {
        $this->codeStore->appendToLastLine(',');
      }
      else
      {
        $this->codeStore->appendToLastLine(')');
      }

      $first = false;
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates the function name and parameters of a stored routine that (preferably) uses a key (i.e. select, update,
   * and delete).
   */
  protected function generateRoutineDeclarationWithKey(): void
  {
    $this->codeStore->append(sprintf('create procedure %s(', $this->routineName));

    $offset  = mb_strlen($this->codeStore->getLastLine());
    $columns = $this->keyColumns();
    $width   = $this->maxColumnNameLength($columns);

    $first = true;
    foreach ($columns as $column)
    {
      if ($first)
      {
        $format = sprintf('in p_%%-%ds @%%s.%%s%%s@', $width);
        $this->codeStore->appendToLastLine(strtolower(sprintf($format,
                                                              $column['column_name'],
                                                              $this->tableName,
                                                              $column['column_name'],
                                                              '%type')));
      }
      else
      {
        $format = sprintf('%%%ds p_%%-%ds @%%s.%%s%%s@', $offset + 2, $width);
        $this->codeStore->append(strtolower(sprintf($format,
                                                    'in',
                                                    $column['column_name'],
                                                    $this->tableName,
                                                    $column['column_name'],
                                                    '%type')),
                                 false);
      }

      if ($column!=end($columns))
      {
        $this->codeStore->appendToLastLine(',');
      }
      else
      {
        $this->codeStore->appendToLastLine(')');
      }

      $first = false;
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates the modifies/reads sql data and designation type comment of the stored routine.
   */
  abstract protected function generateSqlDataAndDesignationType(): void;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns all columns that are in one or more keys. If the table does not have any keys all columns are returned.
   *
   * @return array[]
   */
  protected function keyColumns(): array
  {
    $columns = [];

    if (!empty($this->uniqueIndexes))
    {
      foreach ($this->tableColumns as $column)
      {
        if (RowSetHelper::searchInRowSet($this->uniqueIndexes, 'Column_name', $column['column_name'])!==null)
        {
          $columns[] = $column;
        }
      }
    }
    else
    {
      foreach ($this->tableColumns as $column)
      {
        $columns[] = $column;
      }
    }

    return $columns;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @return array
   */
  protected function keyList(): array
  {
    $nested = $this->nestedKeys();
    $keys   = [];
    foreach ($nested as $keyName => $columnNames)
    {
      $keys[$keyName] = implode(', ', $columnNames);
    }

    return $keys;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the length the longest column name in a list of columns.
   *
   * @param array[] $columns The list of columns.
   *
   * @return int
   */
  protected function maxColumnNameLength(array $columns): int
  {
    $length = 0;
    foreach ($columns as $column)
    {
      $length = max(mb_strlen($column['column_name']), $length);
    }

    return $length;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns all keys (primary and unique indexes) on the table as nested array.
   *
   * @return array[]
   */
  protected function nestedKeys(): array
  {
    $keys = [];
    $last = '';
    foreach ($this->uniqueIndexes as $row)
    {
      if ($last!==$row['Key_name'])
      {
        $keys[$row['Key_name']] = [];
      }

      $keys[$row['Key_name']][] = $row['Column_name'];

      $last = $row['Key_name'];
    }

    return $keys;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns all columns of the table except any auto increment column.
   *
   * @return array[]
   */
  protected function tableColumnsWithoutAutoIncrement(): array
  {
    $columns = [];

    foreach ($this->tableColumns as $column)
    {
      if ($column['extra']!='auto_increment')
      {
        $columns[] = $column;
      }
    }

    return $columns;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates the code of the stored routine.
   */
  private function generateRoutine(): void
  {
    $this->generateDocBlock();
    $this->generateRoutineDeclaration();
    $this->generateSqlDataAndDesignationType();
    $this->codeStore->append('begin');
    $this->generateBody();
    $this->codeStore->append('end');
  }
}

//----------------------------------------------------------------------------------------------------------------------
