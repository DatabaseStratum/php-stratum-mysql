<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Wrapper;

use SetBased\Stratum\Common\Wrapper\Helper\WrapperContext;
use SetBased\Stratum\Middle\Exception\ResultException;
use SetBased\Stratum\MySql\Exception\MySqlDataLayerException;
use SetBased\Stratum\MySql\Loader\Helper\MySqlDataTypeHelper;

/**
 * Class for generating a wrapper method for a stored function.
 */
class FunctionWrapper extends MysqlWrapper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateMethodBodyWithLobFetchData(WrapperContext $context): void
  {
    $context->codeStore->append('$ret = $this->mysqli->affected_rows;');
    $context->codeStore->append('');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateMethodBodyWithLobReturnData(WrapperContext $context): void
  {
    if ($context->phpStratumMetadata['return']==='bool')
    {
      $context->codeStore->append('return !empty($ret);');
    }
    else
    {
      $context->codeStore->append('return $ret;');
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function generateMethodBodyWithoutLob(WrapperContext $context): void
  {
    $this->throws(MySqlDataLayerException::class);
    $this->throws(ResultException::class);

    if ($context->phpStratumMetadata['php_doc']['return']===['bool'])
    {
      $context->codeStore->append(sprintf("return !empty(\$this->executeSingleton0('select %s(%s)'));",
                                          $context->phpStratumMetadata['routine_name'],
                                          $this->getRoutineArgs($context)));
    }
    else
    {
      $context->codeStore->append(sprintf("return \$this->executeSingleton0('select %s(%s)');",
                                          $context->phpStratumMetadata['routine_name'],
                                          $this->getRoutineArgs($context)));
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function getDocBlockReturnType(WrapperContext $context): string
  {
    return implode('|', $context->phpStratumMetadata['php_doc']['return']);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function getReturnTypeDeclaration(WrapperContext $context): string
  {
    $type = MySqlDataTypeHelper::phpTypeHintingToPhpTypeDeclaration($this->getDocBlockReturnType($context));
    if ($type==='')
    {
      return '';
    }

    return ': '.$type;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
