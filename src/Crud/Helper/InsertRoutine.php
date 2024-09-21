<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Crud\Helper;

/**
 * Generates the code for a stored routine that inserts a row.
 */
class InsertRoutine extends BaseRoutine
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateBody(): void
  {
    $this->codeStore->append(sprintf('insert into %s(', $this->tableName));

    $offset  = mb_strlen($this->codeStore->getLastLine());
    $columns = $this->tableColumnsWithoutAutoIncrement();
    $padding = $this->maxColumnNameLength($columns);

    $first = true;
    foreach ($columns as $column)
    {
      if ($first)
      {
        $this->codeStore->appendToLastLine(sprintf(' %s', $column['column_name']));

        $first = false;
      }
      else
      {
        $format = sprintf('%%-%ds %%s', $offset);
        $this->codeStore->append(sprintf($format, ',', $column['column_name']));

        if ($column===end($this->tableColumns))
        {
          $this->codeStore->appendToLastLine(' )');
        }
      }
    }

    $this->codeStore->append('values(');
    $offset = mb_strlen($this->codeStore->getLastLine());

    $first = true;
    foreach ($columns as $column)
    {
      if ($first)
      {
        $this->codeStore->appendToLastLine(sprintf(' p_%s', $column['column_name']));

        $first = false;
      }
      else
      {
        $format = sprintf('%%-%ds p_%%-%ds', $offset, $padding);
        $this->codeStore->append(sprintf($format, ',', $column['column_name']));

        if ($column===end($this->tableColumns))
        {
          $this->codeStore->appendToLastLine(' )');
        }
      }
    }
    $this->codeStore->append(';');

    if ($this->checkAutoIncrement($this->tableColumns))
    {
      $this->codeStore->append('');
      $this->codeStore->append('select last_insert_id();');
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritDoc
   */
  protected function generateDocBlock(): void
  {
    $this->generateDocBlockAllColumnsWithoutAutoIncrement();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates the function name and parameters of the stored routine.
   */
  protected function generateRoutineDeclaration(): void
  {
    $this->generateRoutineDeclarationAllColumnsWithoutAutoIncrement();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritDoc
   */
  protected function generateSqlDataAndDesignationType(): void
  {
    $this->codeStore->append('modifies sql data');

    if ($this->checkAutoIncrement($this->tableColumns))
    {
      $this->codeStore->append('-- type:   singleton1');
      $this->codeStore->append('-- return: int');
    }
    else
    {
      $this->codeStore->append('-- type: none');
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//--------------------------------------------------------------------------------------------------------------------
