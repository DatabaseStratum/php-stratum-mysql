<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Wrapper;

use SetBased\Stratum\Common\Wrapper\Helper\WrapperContext;
use SetBased\Stratum\MySql\Exception\MySqlQueryErrorException;

/**
 * Class for generating a wrapper method for a stored procedure that selects 0 or more rows with 2 columns. The rows are
 * returned as an array the first column are the keys and the second column are the values.
 */
class MapWrapper extends MysqlWrapper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateMethodBodyWithLobFetchData(WrapperContext $context): void
  {
    $context->codeStore->append('$result = $stmt->get_result();');
    $context->codeStore->append('$ret = [];');
    $context->codeStore->append('while (($row = $result->fetch_array(MYSQLI_NUM)))');
    $context->codeStore->append('{');
    $context->codeStore->append('$ret[$row[0]] = $row[1];');
    $context->codeStore->append('}');
    $context->codeStore->append('$result->free();');
    $context->codeStore->append('');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateMethodBodyWithLobReturnData(WrapperContext $context): void
  {
    $context->codeStore->append('return $ret;');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateMethodBodyWithoutLob(WrapperContext $context): void
  {
    $this->throws(MySqlQueryErrorException::class);

    $context->codeStore->append(sprintf("\$result = \$this->query('call %s(%s)');",
                                        $context->phpStratumMetadata['routine_name'],
                                        $this->getRoutineArgs($context)));
    $context->codeStore->append('$ret = [];');
    $context->codeStore->append('while (($row = $result->fetch_array(MYSQLI_NUM)))');
    $context->codeStore->append('{');
    $context->codeStore->append('$ret[$row[0]] = $row[1];');
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
    return 'array';
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
