<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Wrapper;

use SetBased\Stratum\Common\Wrapper\Helper\WrapperContext;
use SetBased\Stratum\Middle\Exception\ResultException;
use SetBased\Stratum\MySql\Exception\MySqlQueryErrorException;

/**
 * Class for generating a wrapper method for a stored procedure that selects 0 or 1 row.
 */
class Row0Wrapper extends MysqlWrapper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateMethodBodyWithLobFetchData(WrapperContext $context): void
  {
    $context->codeStore->append('$row = [];');
    $context->codeStore->append('$this->bindAssoc($stmt, $row);');
    $context->codeStore->append('');
    $context->codeStore->append('$tmp = [];');
    $context->codeStore->append('while (($b = $stmt->fetch()))');
    $context->codeStore->append('{');
    $context->codeStore->append('$new = [];');
    $context->codeStore->append('foreach($row as $key => $value)');
    $context->codeStore->append('{');
    $context->codeStore->append('$new[$key] = $value;');
    $context->codeStore->append('}');
    $context->codeStore->append('$tmp[] = $new;');
    $context->codeStore->append('}');
    $context->codeStore->append('');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateMethodBodyWithLobReturnData(WrapperContext $context): void
  {
    $this->throws(ResultException::class);

    $context->codeStore->append('if ($b===false)');
    $context->codeStore->append('{');
    $context->codeStore->append('throw $this->dataLayerError(\'mysqli_stmt::fetch\');');
    $context->codeStore->append('}');
    $context->codeStore->append('if (sizeof($tmp)>1)');
    $context->codeStore->append('{');
    $context->codeStore->append('throw new ResultException([0, 1], sizeof($tmp), $query);');
    $context->codeStore->append('}');
    $context->codeStore->append('');
    $context->codeStore->append('return ($tmp) ? $tmp[0] : null;');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateMethodBodyWithoutLob(WrapperContext $context): void
  {
    $this->throws(MySqlQueryErrorException::class);
    $this->throws(ResultException::class);

    $context->codeStore->append(sprintf("return \$this->executeRow0('call %s(%s)');",
                                        $context->phpStratumMetadata['routine_name'],
                                        $this->getRoutineArgs($context)));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function getDocBlockReturnType(WrapperContext $context): string
  {
    return 'array|null';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function getReturnTypeDeclaration(WrapperContext $context): string
  {
    return ': ?array';
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
