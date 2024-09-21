<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Wrapper;

use SetBased\Stratum\Common\Wrapper\Helper\WrapperContext;
use SetBased\Stratum\MySql\Exception\MySqlDataLayerException;
use SetBased\Stratum\MySql\Exception\MySqlQueryErrorException;

/**
 * Class for generating a wrapper method for a stored procedure that selects 0 or more rows. The rows are returned as
 * nested arrays.
 */
class RowsWithIndexWrapper extends MysqlWrapper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateMethodBodyWithLobFetchData(WrapperContext $context): void
  {
    $index = '';
    foreach ($context->phpStratumMetadata['designation']['columns'] as $column)
    {
      $index .= '[$new[\''.$column.'\']]';
    }

    $context->codeStore->append('$row = [];');
    $context->codeStore->append('$this->bindAssoc($stmt, $row);');
    $context->codeStore->append('');
    $context->codeStore->append('$ret = [];');
    $context->codeStore->append('while (($b = $stmt->fetch()))');
    $context->codeStore->append('{');
    $context->codeStore->append('$new = [];');
    $context->codeStore->append('foreach($row as $key => $value)');
    $context->codeStore->append('{');
    $context->codeStore->append('$new[$key] = $value;');
    $context->codeStore->append('}');
    $context->codeStore->append('$ret'.$index.'[] = $new;');
    $context->codeStore->append('}');
    $context->codeStore->append('');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateMethodBodyWithLobReturnData(WrapperContext $context): void
  {
    $this->throws(MySqlDataLayerException::class);

    $context->codeStore->append('if ($b===false)');
    $context->codeStore->append('{');
    $context->codeStore->append('throw $this->dataLayerError(\'mysqli_stmt::fetch\');');
    $context->codeStore->append('}');
    $context->codeStore->append('');
    $context->codeStore->append('return $ret;');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateMethodBodyWithoutLob(WrapperContext $context): void
  {
    $this->throws(MySqlQueryErrorException::class);

    $index = '';
    foreach ($context->phpStratumMetadata['designation']['columns'] as $column)
    {
      $index .= '[$row[\''.$column.'\']]';
    }

    $context->codeStore->append(sprintf("\$result = \$this->query('call %s(%s)');",
                                        $context->phpStratumMetadata['routine_name'],
                                        $this->getRoutineArgs($context)));
    $context->codeStore->append('$ret = [];');
    $context->codeStore->append('while (($row = $result->fetch_array(MYSQLI_ASSOC)))');
    $context->codeStore->append('{');
    $context->codeStore->append(sprintf('$ret%s[] = $row;', $index));
    $context->codeStore->append('}');
    $context->codeStore->append('$result->free();');
    $context->codeStore->append('if ($this->mysqli->more_results())');
    $context->codeStore->append('{');
    $context->codeStore->append('$this->mysqli->next_result();');
    $context->codeStore->append('}');
    $context->codeStore->append('');
    $context->codeStore->append('return $ret;');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function getDocBlockReturnType(WrapperContext $context): string
  {
    return 'array[]';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function getReturnTypeDeclaration(WrapperContext $context): string
  {
    return ': array';
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
