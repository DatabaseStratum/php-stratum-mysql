<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Backend;

use SetBased\Stratum\Common\Backend\CommonRoutineWrapperGeneratorWorker;
use SetBased\Stratum\Common\Helper\CommonDataTypeHelper;
use SetBased\Stratum\Common\Wrapper\Helper\WrapperContext;
use SetBased\Stratum\MySql\Loader\Helper\MySqlDataTypeHelper;
use SetBased\Stratum\MySql\Wrapper\MysqlWrapper;

/**
 * Command for generating a class with wrapper methods for invoking stored routines in a MySQL or MariaDB database.
 */
class MySqlRoutineWrapperGeneratorWorker extends CommonRoutineWrapperGeneratorWorker
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function buildRoutineWrapper(WrapperContext $context): void
  {
    $wrapper = MysqlWrapper::createRoutineWrapper($context);
    $wrapper->generateMethod($context);

    $this->imports = array_merge($this->imports, $wrapper->getImports());
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function createDataTypeHelper(): CommonDataTypeHelper
  {
    return new MySqlDataTypeHelper();
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
