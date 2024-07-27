<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Wrapper;

use SetBased\Stratum\Middle\Exception\ResultException;
use SetBased\Stratum\MySql\Exception\MySqlDataLayerException;
use SetBased\Stratum\MySql\Helper\DataTypeHelper;

/**
 * Class for generating a wrapper method for a stored procedure that selects 1 row having a single column only.
 */
class Singleton1Wrapper extends Wrapper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function getDocBlockReturnType(): string
  {
    return $this->routine['return'];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function getReturnTypeDeclaration(): string
  {
    $type = DataTypeHelper::phpTypeHintingToPhpTypeDeclaration($this->getDocBlockReturnType());

    if ($type==='')
    {
      return '';
    }

    return ': '.$type;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function writeResultHandler(): void
  {
    $this->throws(MySqlDataLayerException::class);
    $this->throws(ResultException::class);

    $routineArgs = $this->getRoutineArgs();

    if ($this->routine['return']=='bool')
    {
      $this->codeStore->append('return !empty($this->executeSingleton1(\'call '.$this->routine['routine_name'].'('.$routineArgs.')\'));');
    }
    else
    {
      $this->codeStore->append('return $this->executeSingleton1(\'call '.$this->routine['routine_name'].'('.$routineArgs.')\');');
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function writeRoutineFunctionLobFetchData(): void
  {
    $this->codeStore->append('$row = [];');
    $this->codeStore->append('$this->bindAssoc($stmt, $row);');
    $this->codeStore->append('');
    $this->codeStore->append('$tmp = [];');
    $this->codeStore->append('while (($b = $stmt->fetch()))');
    $this->codeStore->append('{');
    $this->codeStore->append('$new = [];');
    $this->codeStore->append('foreach($row as $value)');
    $this->codeStore->append('{');
    $this->codeStore->append('$new[] = $value;');
    $this->codeStore->append('}');
    $this->codeStore->append('$tmp[] = $new;');
    $this->codeStore->append('}');
    $this->codeStore->append('');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function writeRoutineFunctionLobReturnData(): void
  {
    $this->throws(MySqlDataLayerException::class);
    $this->throws(ResultException::class);

    $this->codeStore->append('if ($b===false) throw $this->dataLayerError(\'mysqli_stmt::fetch\');');
    $this->codeStore->append('if (sizeof($tmp)!=1) throw new ResultException([1], sizeof($tmp), $query);');
    $this->codeStore->append('');

    if ($this->routine['return']=='bool')
    {
      $this->codeStore->append('return !empty($tmp[0][0]);');
    }
    else
    {
      $this->codeStore->append('return $tmp[0][0];');
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
