<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Wrapper;

use SetBased\Exception\LogicException;
use SetBased\Stratum\Common\Wrapper\Helper\WrapperContext;
use SetBased\Stratum\MySql\Exception\MySqlQueryErrorException;

/**
 * Class for generating a wrapper method for a stored procedure that prepares a table to be used with a multiple insert
 * SQL statement.
 */
class InsertMultipleWrapper extends MysqlWrapper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function enhancePhpDocBlockParameters(array &$parameters): void
  {
    $parameter = ['php_name'       => '$rows',
                  'description'    => 'The rows that must be inserted.',
                  'php_type'       => 'array[]',
                  'dtd_identifier' => null];

    $parameters = array_merge([$parameter], $parameters);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateMethodBodyWithLobFetchData(WrapperContext $context): void
  {
    // Nothing to do.
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateMethodBodyWithLobReturnData(WrapperContext $context): void
  {
    // Nothing to do.
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateMethodBodyWithoutLob(WrapperContext $context): void
  {
    $this->throws(MySqlQueryErrorException::class);

    $tableName = $context->phpStratumMetadata['designation']['table_name'];
    $keys      = $context->phpStratumMetadata['designation']['keys'];
    $columns   = $context->phpStratumMetadata['insert_multiple_table_columns'];
    $n1        = sizeof($keys);
    $n2        = sizeof($columns);
    if ($n1!==$n2)
    {
      throw new LogicException("Number of fields %d and number of columns %d don't match.", $n1, $n2);
    }

    $context->codeStore->append(sprintf("\$this->realQuery('call %s(%s)');",
                                        $context->phpStratumMetadata['routine_name'],
                                        $this->getRoutineArgs($context)));

    $columnNames = [];
    $values      = [];
    foreach ($keys as $i => $key)
    {
      if ($key!='_')
      {
        $columnNames[] = '`'.$columns[$i]['column_name'].'`';
        $values[]      = $context->dataType->escapePhpExpression($columns[$i], '$row[\''.$key.'\']');
      }
    }

    $context->codeStore->append('if (is_array($rows) && !empty($rows))');
    $context->codeStore->append('{');
    $context->codeStore->append(sprintf('$sql = "INSERT INTO `%s`(%s)".PHP_EOL;',
                                        $tableName,
                                        implode(', ', $columnNames)));
    $context->codeStore->append('$first = true;');
    $context->codeStore->append('foreach($rows as $row)');
    $context->codeStore->append('{');

    $context->codeStore->append(sprintf("\$sql .= ((\$first) ? 'values' : ',     ').'(%s)'.PHP_EOL;",
                                        implode(', ', $values)));

    $context->codeStore->append('$first = false;');
    $context->codeStore->append('}');
    $context->codeStore->append('$this->realQuery($sql);');
    $context->codeStore->append('}');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function getDocBlockReturnType(WrapperContext $context): string
  {
    return 'void';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function getReturnTypeDeclaration(WrapperContext $context): string
  {
    return ': void';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function getWrapperArgs(WrapperContext $context): string
  {
    return '?array $rows';
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
