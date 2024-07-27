<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Wrapper;

use SetBased\Exception\LogicException;
use SetBased\Stratum\MySql\Exception\MySqlQueryErrorException;
use SetBased\Stratum\MySql\Helper\DataTypeHelper;

/**
 * Class for generating a wrapper method for a stored procedure that prepares a table to be used with a bulk SQL
 * statement.
 */
class BulkInsertWrapper extends Wrapper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function enhancePhpDocBlockParameters(array &$parameters): void
  {
    $parameter = ['php_name'       => '$rows',
                  'description'    => ['The rows that must be inserted.'],
                  'php_type'       => 'array[]',
                  'dtd_identifier' => null];

    $parameters = array_merge([$parameter], $parameters);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function getDocBlockReturnType(): string
  {
    return 'void';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function getReturnTypeDeclaration(): string
  {
    return ': void';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function getWrapperArgs(): string
  {
    return '?array $rows';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function writeResultHandler(): void
  {
    $this->throws(MySqlQueryErrorException::class);

    // Validate number of column names and number of column types are equal.
    $n1 = sizeof($this->routine['bulk_insert_keys']);
    $n2 = sizeof($this->routine['bulk_insert_columns']);
    if ($n1!=$n2)
    {
      throw new LogicException("Number of fields %d and number of columns %d don't match.", $n1, $n2);
    }

    $routineArgs = $this->getRoutineArgs();
    $this->codeStore->append('$this->realQuery(\'call '.$this->routine['routine_name'].'('.$routineArgs.')\');');

    $columns = '';
    $fields  = '';
    foreach ($this->routine['bulk_insert_keys'] as $i => $key)
    {
      if ($key!='_')
      {
        if ($columns)
        {
          $columns .= ',';
        }
        $columns .= '`'.$this->routine['bulk_insert_columns'][$i]['column_name'].'`';

        if ($fields)
        {
          $fields .= ',';
        }
        $fields .= DataTypeHelper::escapePhpExpression($this->routine['bulk_insert_columns'][$i], '$row[\''.$key.'\']');
      }
    }

    $this->codeStore->append('if (is_array($rows) && !empty($rows))');
    $this->codeStore->append('{');
    $this->codeStore->append('$sql = "INSERT INTO `'.$this->routine['bulk_insert_table_name'].'`('.$columns.')".PHP_EOL;');
    $this->codeStore->append('$first = true;');
    $this->codeStore->append('foreach($rows as $row)');
    $this->codeStore->append('{');

    $this->codeStore->append('$sql .= (($first) ? \'values\' : \',     \').\'('.$fields.')\'.PHP_EOL;');

    $this->codeStore->append('$first = false;');
    $this->codeStore->append('}');
    $this->codeStore->append('$this->realQuery($sql);');
    $this->codeStore->append('}');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function writeRoutineFunctionLobFetchData(): void
  {
    // Nothing to do.
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function writeRoutineFunctionLobReturnData(): void
  {
    // Nothing to do.
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
