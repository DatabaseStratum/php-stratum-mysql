<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Wrapper;

use SetBased\Exception\FallenException;
use SetBased\Stratum\Common\Wrapper\CommonWrapper;
use SetBased\Stratum\Common\Wrapper\Helper\WrapperContext;
use SetBased\Stratum\Middle\Exception\ResultException;
use SetBased\Stratum\MySql\Exception\MySqlDataLayerException;
use SetBased\Stratum\MySql\Exception\MySqlQueryErrorException;
use SetBased\Stratum\MySql\Loader\Helper\MySqlDataTypeHelper;

/**
 * Abstract parent class for all wrapper generators.
 */
abstract class MysqlWrapper extends CommonWrapper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * A factory for creating the appropriate object for generating a wrapper method for a stored routine.
   *
   * @param WrapperContext $context The wrapper context.
   */
  public static function createRoutineWrapper(WrapperContext $context): MysqlWrapper
  {
    $type = $context->phpStratumMetadata['designation']['type'];
    switch ($type)
    {
      case 'bulk':
        $wrapper = new BulkWrapper();
        break;

      case 'insert_multiple':
        $wrapper = new InsertMultipleWrapper();
        break;

      case 'log':
        $wrapper = new LogWrapper();
        break;

      case 'map':
        $wrapper = new MapWrapper();
        break;

      case 'none':
        $wrapper = new NoneWrapper();
        break;

      case 'row0':
        $wrapper = new Row0Wrapper();
        break;

      case 'row1':
        $wrapper = new Row1Wrapper();
        break;

      case 'rows':
        $wrapper = new RowsWrapper();
        break;

      case 'rows_with_key':
        $wrapper = new RowsWithKeyWrapper();
        break;

      case 'rows_with_index':
        $wrapper = new RowsWithIndexWrapper();
        break;

      case 'singleton0':
        $wrapper = new Singleton0Wrapper();
        break;

      case 'singleton1':
        $wrapper = new Singleton1Wrapper();
        break;

      case 'function':
        $wrapper = new FunctionWrapper();
        break;

      case 'table':
        $wrapper = new TableWrapper();
        break;

      default:
        throw new FallenException('routine type', $type);
    }

    return $wrapper;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns true if one of the parameters is a BLOB or CLOB.
   *
   * @param WrapperContext $context The wrapper context.
   */
  public function hasBlobParameter(WrapperContext $context): bool
  {
    foreach ($context->phpStratumMetadata['parameters'] as $parameter)
    {
      if (MySqlDataTypeHelper::isBlobParameter($parameter['data_type']))
      {
        return true;
      }
    }

    return false;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates a complete wrapper method.
   *
   * @param WrapperContext $context The wrapper context.
   */
  protected function generateMethodBody(WrapperContext $context): void
  {
    if ($this->hasBlobParameter($context))
    {
      $this->generateMethodBodyWithLob($context);
    }
    else
    {
      $this->generateMethodBodyWithoutLob($context);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates a complete wrapper method.
   *
   * @param WrapperContext $context The wrapper context.
   */
  abstract protected function generateMethodBodyWithLobFetchData(WrapperContext $context): void;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates a complete wrapper method.
   *
   * @param WrapperContext $context The wrapper context.
   */
  abstract protected function generateMethodBodyWithLobReturnData(WrapperContext $context): void;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a wrapper method for a stored routine without LOB parameters.
   *
   * @param WrapperContext $context The wrapper context.
   */
  abstract protected function generateMethodBodyWithoutLob(WrapperContext $context): void;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns code for the arguments for calling the stored routine in a wrapper method.
   *
   * @param WrapperContext $context The wrapper context.
   */
  protected function getRoutineArgs(WrapperContext $context): string
  {
    $ret = '';

    foreach ($context->phpStratumMetadata['parameters'] as $parameter)
    {
      $mangledName = $context->mangler::getParameterName($parameter['parameter_name']);

      if ($ret)
      {
        $ret .= ',';
      }
      $ret .= $context->dataType->escapePhpExpression($parameter, '$'.$mangledName);
    }

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates a complete wrapper method for a stored routine with a LOB parameter.
   *
   * @param WrapperContext $context The wrapper context.
   */
  private function generateMethodBodyWithLob(WrapperContext $context): void
  {
    $this->throws(MySqlDataLayerException::class);
    $this->throws(MysqlQueryErrorException::class);
    $this->throws(ResultException::class);

    $routineArgs = $this->getRoutineArgs($context);

    $bindings = '';
    $nulls    = '';
    foreach ($context->phpStratumMetadata['parameters'] as $parameter)
    {
      $binding = MySqlDataTypeHelper::getBindVariableType($parameter);
      if ($binding=='b')
      {
        $bindings .= 'b';
        if ($nulls!=='')
        {
          $nulls .= ', ';
        }
        $nulls .= '$null';
      }
    }
    $context->codeStore->append('$query = \'call '.$context->phpStratumMetadata['routine_name'].'('.$routineArgs.')\';');
    $context->codeStore->append('$stmt  = @$this->mysqli->prepare($query);');
    $context->codeStore->append('if (!$stmt)');
    $context->codeStore->append('{');
    $context->codeStore->append("throw \$this->dataLayerError('mysqli::prepare');");
    $context->codeStore->append('}');
    $context->codeStore->append('');
    $context->codeStore->append('$null = null;');
    $context->codeStore->append('$success = @$stmt->bind_param(\''.$bindings.'\', '.$nulls.');');
    $context->codeStore->append('if (!$success)');
    $context->codeStore->append('{');
    $context->codeStore->append("throw \$this->dataLayerError('mysqli_stmt::bind_param');");
    $context->codeStore->append('}');
    $context->codeStore->append('');
    $context->codeStore->append('$this->getMaxAllowedPacket();');
    $context->codeStore->append('');

    $blobArgumentIndex = 0;
    foreach ($context->phpStratumMetadata['parameters'] as $parameter)
    {
      if (MySqlDataTypeHelper::getBindVariableType($parameter)=='b')
      {
        $mangledName = $context->mangler::getParameterName($parameter['parameter_name']);

        $context->codeStore->append('$this->sendLongData($stmt, '.$blobArgumentIndex.', $'.$mangledName.');');

        $blobArgumentIndex++;
      }
    }

    if ($blobArgumentIndex>0)
    {
      $context->codeStore->append('');
    }

    $context->codeStore->append('if ($this->logQueries)');
    $context->codeStore->append('{');
    $context->codeStore->append('$time0 = microtime(true);');
    $context->codeStore->append('}');
    $context->codeStore->append('');
    $context->codeStore->append('try');
    $context->codeStore->append('{');
    $context->codeStore->append('$success = @$stmt->execute();');
    $context->codeStore->append('}');
    $context->codeStore->append('catch (\mysqli_sql_exception)');
    $context->codeStore->append('{');
    $context->codeStore->append('$success = false;');
    $context->codeStore->append('}');
    $context->codeStore->append('if (!$success)');
    $context->codeStore->append('{');
    $context->codeStore->append("throw \$this->queryError('mysqli_stmt::execute', \$query);");
    $context->codeStore->append('}');
    $context->codeStore->append('');
    $context->codeStore->append('if ($this->logQueries)');
    $context->codeStore->append('{');
    $context->codeStore->append("\$this->queryLog[] = ['query' => \$query,");
    $context->codeStore->append("                     'time'  => microtime(true) - \$time0];", false);
    $context->codeStore->append('}');
    $context->codeStore->append('');
    $this->generateMethodBodyWithLobFetchData($context);
    $context->codeStore->append('$stmt->close();');
    $context->codeStore->append('if ($this->mysqli->more_results())');
    $context->codeStore->append('{');
    $context->codeStore->append('$this->mysqli->next_result();');
    $context->codeStore->append('}');
    $context->codeStore->append('');
    $this->generateMethodBodyWithLobReturnData($context);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
