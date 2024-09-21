<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Crud\Helper;

/**
 * Generates the code for a stored routine that updates a row.
 */
class UpdateRoutine extends BaseRoutine
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateBody(): void
  {
    $this->codeStore->append(sprintf('update %s', $this->tableName));
    $this->codeStore->append('set');

    $offset  = mb_strlen($this->codeStore->getLastLine());
    $columns = $this->tableColumnsWithoutAutoIncrement();
    $width   = $this->maxColumnNameLength($columns);

    $first = true;
    foreach ($columns as $column)
    {
      if ($first)
      {
        $format = sprintf("%%%ds %%-%ds = p_%%s", $offset, $width);
        $this->codeStore->appendToLastLine(sprintf($format, '', $column['column_name'], $column['column_name']));

        $first = false;
      }
      else
      {
        $format = sprintf("%%-%ds %%-%ds = p_%%s", $offset + 3, $width);
        $this->codeStore->append(sprintf($format, ',', $column['column_name'], $column['column_name']));
      }
    }

    $this->codeStore->append('where');

    $columns = $this->keyColumns();
    $width   = $this->maxColumnNameLength($columns);

    $first = true;
    foreach ($columns as $column)
    {
      if ($first)
      {
        $format = sprintf("%%%ds %%-%ds = p_%%s", 1, $width);
        $line   = sprintf($format, '', $column['column_name'], $column['column_name']);
        $this->codeStore->appendToLastLine($line);

        $first = false;
      }
      else
      {
        $format = sprintf("and%%%ds %%-%ds = p_%%s", 3, $width);
        $this->codeStore->append(sprintf($format, '', $column['column_name'], $column['column_name']));
      }
    }

    if (empty($this->uniqueIndexes))
    {
      $this->codeStore->append('limit 0,1');
    }

    $this->codeStore->append(';');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritDoc
   */
  protected function generateDocBlock(): void
  {
    $this->generateDocBlockAllColumnsWithKeyList();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates the function name and parameters of the stored routine.
   */
  protected function generateRoutineDeclaration(): void
  {
    $this->generateRoutineDeclarationWithKey();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritDoc
   */
  protected function generateSqlDataAndDesignationType(): void
  {
    $this->codeStore->append('modifies sql data');
    $this->codeStore->append('-- type: none');
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
