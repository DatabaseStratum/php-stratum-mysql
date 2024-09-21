<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Wrapper;

use SetBased\Stratum\Common\Wrapper\Helper\WrapperContext;
use SetBased\Stratum\MySql\Exception\MySqlDataLayerException;
use SetBased\Stratum\MySql\Exception\MySqlQueryErrorException;

/**
 * Class for generating a wrapper method for a stored procedure that selects 0, 1, or more rows.
 */
class RowsWrapper extends MysqlWrapper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateMethodBodyWithLobFetchData(WrapperContext $context): void
  {
    $this->throws(MySqlQueryErrorException::class);

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
    $context->codeStore->append(' $tmp[] = $new;');
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
    $this->throws(MySqlQueryErrorException::class);

    $context->codeStore->append('if ($b===false) throw $this->dataLayerError(\'mysqli_stmt::fetch\');');
    $context->codeStore->append('');
    $context->codeStore->append('return $tmp;');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateMethodBodyWithoutLob(WrapperContext $context): void
  {
    $this->throws(MySqlQueryErrorException::class);

    $context->codeStore->append(sprintf("return \$this->executeRows('call %s(%s)');",
                                        $context->phpStratumMetadata['routine_name'],
                                        $this->getRoutineArgs($context)));
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
